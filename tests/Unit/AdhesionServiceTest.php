<?php

namespace Tests\Unit;

use App\Models\Adhesion;
use App\Models\Tontine;
use App\Models\User;
use App\Services\AdhesionService;
use InvalidArgumentException;
use Tests\TestCase;

class AdhesionServiceTest extends TestCase
{
    private function service(): AdhesionService
    {
        return new AdhesionService;
    }

    public function test_new_or_rejected_request_can_be_submitted(): void
    {
        $rejected = new Adhesion(['statut' => AdhesionService::REJECTED]);
        $pending = new Adhesion(['statut' => AdhesionService::PENDING]);
        $approved = new Adhesion(['statut' => AdhesionService::APPROVED]);

        $this->assertTrue($this->service()->canSubmit(null));
        $this->assertTrue($this->service()->canSubmit($rejected));
        $this->assertFalse($this->service()->canSubmit($pending));
        $this->assertFalse($this->service()->canSubmit($approved));
    }

    public function test_pending_members_do_not_consume_places(): void
    {
        $tontine = new Tontine(['nombre_personne' => 10]);

        $this->assertSame(10, $this->service()->remainingPlaces($tontine, 0));
        $this->assertSame(7, $this->service()->remainingPlaces($tontine, 3));
        $this->assertTrue($this->service()->hasCapacity($tontine, 9));
        $this->assertFalse($this->service()->hasCapacity($tontine, 10));
    }

    public function test_approve_clears_rejection_and_sets_member_badge(): void
    {
        $adhesion = new Adhesion([
            'statut' => AdhesionService::PENDING,
            'badge' => 'demande',
            'motif_rejet' => 'CNI illisible',
        ]);

        $this->service()->approve($adhesion, 42);

        $this->assertSame(AdhesionService::APPROVED, $adhesion->statut);
        $this->assertSame('membre', $adhesion->badge);
        $this->assertNull($adhesion->motif_rejet);
        $this->assertSame(42, $adhesion->reviewed_by);
        $this->assertNotNull($adhesion->reviewed_at);
    }

    public function test_reject_requires_a_reason(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->service()->reject(new Adhesion(['statut' => AdhesionService::PENDING]), 1, '  ');
    }

    public function test_rejected_request_can_be_reopened(): void
    {
        $adhesion = new Adhesion([
            'statut' => AdhesionService::REJECTED,
            'motif_rejet' => 'Photo floue',
            'reviewed_by' => 1,
        ]);

        $this->service()->reopen($adhesion);

        $this->assertSame(AdhesionService::PENDING, $adhesion->statut);
        $this->assertNull($adhesion->motif_rejet);
        $this->assertSame('demande', $adhesion->badge);
    }

    public function test_payload_exposes_sla_only_when_pending(): void
    {
        $pending = new Adhesion(['statut' => AdhesionService::PENDING]);
        $approved = new Adhesion(['statut' => AdhesionService::APPROVED]);

        $this->assertSame('24-48h', $this->service()->payload($pending)['delai_estime']);
        $this->assertNull($this->service()->payload($approved)['delai_estime']);
    }

    public function test_owner_reviews_private_tontine_not_catalogue(): void
    {
        $owner = new User;
        $owner->role_id = User::ROLE_USER;
        $admin = new User;
        $admin->role_id = User::ROLE_ADMIN;
        $privee = new Tontine(['visibilite' => Tontine::VISIBILITE_PRIVEE]);
        $catalogue = new Tontine(['visibilite' => Tontine::VISIBILITE_CATALOGUE]);

        $this->assertTrue($this->service()->canReview($owner, $privee, true));
        $this->assertFalse($this->service()->canReview($owner, $privee, false));
        $this->assertFalse($this->service()->canReview($owner, $catalogue, true));
        $this->assertTrue($this->service()->canReview($admin, $catalogue, false));
        $this->assertTrue($this->service()->canReview($admin, $privee, false));
    }

    public function test_private_pending_payload_points_to_creator(): void
    {
        $adhesion = new Adhesion(['statut' => AdhesionService::PENDING]);
        $adhesion->setRelation('tontine', new Tontine(['visibilite' => Tontine::VISIBILITE_PRIVEE]));

        $this->assertSame(AdhesionService::DELAY_OWNER, $this->service()->payload($adhesion)['delai_estime']);
    }
}
