@extends('layouts.app')

@section('title', 'À propos — SUNUNATT')
@section('description', 'Découvrez qui nous sommes, notre mission et notre vision pour l\'inclusion financière en Afrique.')

@section('content')

<!-- HERO -->
<section class="hero-about">
  <div class="container">
    <h1>À propos de SUNUNATT</h1>
    <p>Découvrez qui nous sommes, notre mission et notre vision pour l'inclusion financière en Afrique.</p>
  </div>
</section>

<!-- PRÉSENTATION -->
<section class="section">
  <div class="container">
    <div class="présentation">
      <div class="présentation-card">
        <i class="fas fa-bullseye" style="font-size:48px;color:var(--blue);margin-bottom:20px;"></i>
        <h3 style="margin-bottom:20px;">SUNUNATT</h3>
        <p style="padding:8px;">
          L'épargne collaborative au service de vos projets.
          SUNUNATT est une application sénégalaise de tontine numérique qui permet à chacun d'épargner intelligemment,
          en toute sécurité, pour concrétiser ses projets — petits comme grands.
          Notre slogan le résume : « Vos projets à portée de main. »
        </p>
      </div>
    </div>
  </div>
</section>

<!-- MISSION & VISION -->
<section class="section">
  <div class="container">
    <div class="mission-vision">
      <div class="mission-card">
        <i class="fas fa-bullseye"></i>
        <h3>Notre Mission</h3>
        <p>
          Démocratiser l'accès à l'épargne et à l'investissement au Sénégal et dans l'espace UEMOA,
          en réinventant la tontine — cette tradition séculaire d'épargne collaborative — avec les outils du numérique :
          sécurité bancaire, transparence totale, et discipline automatisée.
          SUNUNATT s'adresse à tous les profils : salariés, commerçants, étudiants, professions libérales,
          membres de la diaspora. Que vous épargniez pour une parcelle de terrain, un équipement, un projet
          professionnel ou simplement pour constituer un matelas de sécurité, la plateforme s'adapte à vos objectifs.
        </p>
      </div>
      <div class="vision-card">
        <i class="fas fa-eye"></i>
        <h3>Notre Vision</h3>
        <p>
          <strong>Devenir le leader de la tontine numérique en Afrique de l'Ouest d'ici 2027</strong>,
          en connectant des millions d'utilisateurs à travers une solution innovante et inclusive.
        </p>
      </div>
    </div>
  </div>
</section>

<!-- ENGAGEMENTS -->
<section class="section-alt" style="background:white;">
  <div class="container">
    <h2 class="section-title">Nos engagements</h2>
    <p class="section-sub">Ce qui guide nos actions au quotidien</p>
    <div class="values-grid" style="margin-top:16px;">
      <div class="value-card">
        <h4>Transparence</h4>
        <p>Chaque versement, chaque redistribution et chaque pénalité sont tracés et consultables à tout moment dans l'application.</p>
      </div>
      <div class="value-card">
        <h4>Sécurité</h4>
        <p>Les fonds des utilisateurs sont conservés sur des comptes dédiés, séparés de la trésorerie de l'éditeur, auprès de partenaires financiers agréés par la BCEAO.</p>
      </div>
      <div class="value-card">
        <h4>Conformité</h4>
        <p>SUNUNATT opère dans le respect de la réglementation sénégalaise et UEMOA, notamment la loi n°2008-12 sur la protection des données personnelles et les instructions de la BCEAO relatives aux services financiers numériques.</p>
      </div>
      <div class="value-card">
        <h4>Communauté</h4>
        <p>SUNUNATT s'appuie sur la confiance et la solidarité qui font la force des tontines traditionnelles, en les sécurisant par la technologie.</p>
      </div>
    </div>
  </div>
</section>

<!-- ÉQUIPE -->
<section class="section">
  <div class="container">
    <h2 class="section-title">Notre équipe</h2>
    <p class="section-sub">Des passionnés de la tech et de l'inclusion financière</p>
    <div class="team-grid" style="gap:120px;">
      <div class="team-card">
        <h4>Bruno Gomis</h4>
        <p>CEO &amp; Fondateur</p>
      </div>
      <div class="team-card">
        <h4>GIVE1PROJECT</h4>
        <p>Partenaire stratégique</p>
      </div>
    </div>
  </div>
</section>

<!-- PARTENAIRES -->
<section class="section-alt" style="background:var(--blue-sky, #E8F2FC);">
  <div class="container">
    <h2 class="section-title">Nos partenaires</h2>
    <p class="section-sub">Ils nous font confiance et nous accompagnent</p>
    <div class="partners-grid">
      <span class="partner-badge">
        <img src="https://favicon.im/wave.com" alt="Wave" loading="lazy" style="height:60px;width:60px;">
      </span>
      <span class="partner-badge">
        <img src="{{ asset('images/Orange-Money-logo.png') }}" alt="Orange Money"
             style="height:60px;width:75px;border:1px solid rgb(20,20,20);">
      </span>
    </div>
  </div>
</section>

<!-- CTA FINAL -->
<section id="cta-final">
  <div class="container">
    <h2 class="reveal">Rejoignez des milliers de Sénégalais qui font confiance à SUNUNATT</h2>
    <p class="reveal">Simple, transparent, accessible. Vos projets à portée de main.</p>
    <a href="https://play.google.com/store/apps/details?id=com.teknon.sununatt"
       target="_blank" class="btn btn-gold reveal"
       style="background:#E09000;font-size:17px;padding:16px 36px;">
      Télécharger gratuitement
    </a>
  </div>
</section>

@endsection
