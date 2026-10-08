@extends('layouts.app')

@section('title', 'À propos de SUNUNATT')
@section('description', 'Découvrez SUNUNATT, la tontine numérique sénégalaise pour l\'épargne collective sécurisée.')

@section('content')

<section class="page-hero">
  <div class="container">
    <div class="eyebrow">À propos</div>
    <h1>La tontine, réinventée<br>pour l'Afrique de l'Ouest</h1>
    <p>Notre mission : rendre l'épargne collective simple, transparente et accessible, du quartier à la diaspora.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="présentation">
      <div class="présentation-card reveal">
        <i class="fas fa-seedling" aria-hidden="true"></i>
        <h3>SUNUNATT</h3>
        <p>
          L'épargne collaborative au service de vos projets. SUNUNATT est une application sénégalaise de tontine numérique
          qui permet d'épargner en toute sécurité pour concrétiser vos projets, petits comme grands.
          Notre slogan : « Vos projets à portée de main. »
        </p>
      </div>
    </div>
  </div>
</section>

<section class="section section-alt">
  <div class="container">
    <div class="mission-vision">
      <div class="mission-card reveal">
        <i class="fas fa-bullseye" aria-hidden="true"></i>
        <h3>Notre mission</h3>
        <p>
          Démocratiser l'accès à l'épargne au Sénégal et dans l'espace UEMOA, en digitalisant la tontine avec des outils
          sécurisés, transparents et accessibles. SUNUNATT s'adresse aux salariés, commerçants, étudiants,
          professions libérales et à la diaspora.
        </p>
      </div>
      <div class="vision-card reveal">
        <i class="fas fa-eye" aria-hidden="true"></i>
        <h3>Notre vision</h3>
        <p>
          <strong>Devenir le leader de la tontine numérique en Afrique de l'Ouest d'ici 2027</strong>,
          en connectant des utilisateurs à travers une solution fiable et inclusive.
        </p>
      </div>
    </div>
  </div>
</section>

<section class="section section-white">
  <div class="container">
    <div class="section-page-header reveal">
      <div class="section-label">Nos engagements</div>
      <h2 class="section-title">Ce qui guide nos actions</h2>
    </div>
    <div class="values-grid">
      <div class="value-card reveal">
        <div class="value-icon"><i class="fas fa-eye" aria-hidden="true"></i></div>
        <h4>Transparence</h4>
        <p>Chaque versement, redistribution et pénalité est tracé, exportable en PDF ou Excel, consultable à tout moment.</p>
      </div>
      <div class="value-card reveal">
        <div class="value-icon"><i class="fas fa-shield-alt" aria-hidden="true"></i></div>
        <h4>Sécurité</h4>
        <p>Les fonds sont conservés sur des comptes dédiés, auprès de partenaires agréés par la BCEAO.</p>
      </div>
      <div class="value-card reveal">
        <div class="value-icon"><i class="fas fa-balance-scale" aria-hidden="true"></i></div>
        <h4>Conformité</h4>
        <p>SUNUNATT respecte la réglementation sénégalaise et UEMOA, notamment la loi n°2008-12.</p>
      </div>
      <div class="value-card reveal">
        <div class="value-icon"><i class="fas fa-users" aria-hidden="true"></i></div>
        <h4>Communauté</h4>
        <p>Nous nous appuyons sur la solidarité des tontines traditionnelles, renforcée par la technologie.</p>
      </div>
    </div>
  </div>
</section>

<section class="section section-alt">
  <div class="container">
    <div class="section-page-header reveal">
      <div class="section-label">Notre équipe</div>
      <h2 class="section-title">Une équipe engagée pour l'inclusion financière</h2>
    </div>
    <div class="team-grid">
      <div class="team-card reveal">
        <div class="team-avatar">BG</div>
        <h4>Bruno Gomis</h4>
        <p>CEO &amp; Fondateur</p>
      </div>
      <div class="team-card reveal">
        <div class="team-avatar">G1</div>
        <h4>GIVE1PROJECT</h4>
        <p>Partenaire stratégique</p>
      </div>
    </div>
  </div>
</section>

<section class="section section-sky">
  <div class="container">
    <div class="section-page-header reveal">
      <div class="section-label">Nos partenaires</div>
      <h2 class="section-title">Ils nous accompagnent</h2>
    </div>
    <div class="partners-grid reveal">
      <span class="partner-badge">
        <img src="https://favicon.im/wave.com" alt="Wave" loading="lazy" class="partner-logo">
        Wave
      </span>
      <span class="partner-badge" title="Intégration en cours">
        <img src="{{ asset('images/Orange-Money-logo.png') }}" alt="Orange Money" loading="lazy" class="partner-logo partner-logo--orange">
        Orange Money · bientôt
      </span>
    </div>
  </div>
</section>

<section id="cta-final">
  <div class="container">
    <h2 class="reveal">Rejoignez des milliers de Sénégalais qui font confiance à SUNUNATT</h2>
    <p class="reveal">Simple, transparent, accessible. Vos projets à portée de main.</p>
    <a href="{{ config('tontine.play_store') }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-lg reveal">
      Télécharger gratuitement
    </a>
  </div>
</section>

@endsection
