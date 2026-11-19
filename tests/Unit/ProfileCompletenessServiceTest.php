<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\ProfileCompletenessService;
use Tests\TestCase;

class ProfileCompletenessServiceTest extends TestCase
{
    private function service(): ProfileCompletenessService
    {
        return new ProfileCompletenessService;
    }

    private function completeUser(): User
    {
        return new User([
            'nom' => 'Diop',
            'prenom' => 'Awa',
            'phone' => '771234567',
            'adresse' => 'Dakar',
            'profession' => 'Commerçante',
            'num_cni' => '1234567890',
        ]);
    }

    public function test_new_user_is_not_kyc_ready(): void
    {
        $user = new User([
            'nom' => 'Diop',
            'prenom' => 'Awa',
            'phone' => '771234567',
        ]);

        $result = $this->service()->inspect($user, 0);

        $this->assertFalse($result['kyc_ready']);
        $this->assertFalse($result['can_create_tontine']);
        $this->assertContains('adresse', array_column($result['missing'], 'key'));
        $this->assertContains('profession', array_column($result['missing'], 'key'));
        $this->assertContains('identite', array_column($result['missing'], 'key'));
        $this->assertContains('documents', array_column($result['missing'], 'key'));
    }

    public function test_complete_profile_with_document_is_ready(): void
    {
        $result = $this->service()->inspect($this->completeUser(), 1);

        $this->assertTrue($result['kyc_ready']);
        $this->assertTrue($result['can_create_tontine']);
        $this->assertSame(100, $result['percent']);
        $this->assertSame([], $result['missing']);
    }

    public function test_passport_counts_as_identity(): void
    {
        $user = $this->completeUser();
        $user->num_cni = null;
        $user->num_passport = 'A1234567';

        $this->assertTrue($this->service()->inspect($user, 1)['kyc_ready']);
    }

    public function test_incomplete_response_explains_why_creation_is_blocked(): void
    {
        $user = new User(['nom' => 'Diop', 'prenom' => 'Awa', 'phone' => '77']);

        $response = $this->service()->incompleteResponse($user, 0, 'create_tontine');

        $this->assertSame('PROFILE_INCOMPLETE', $response['code']);
        $this->assertStringContainsString('tontine', $response['message']);
        $this->assertNull($this->service()->incompleteResponse($this->completeUser(), 1));
    }
}
