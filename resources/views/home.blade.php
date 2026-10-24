@extends('layouts.app')

@section('title', 'SUNUNATT, la tontine numérique de l\'Afrique de l\'Ouest')
@section('description', 'SUNUNATT digitalise vos tontines, tirelires et mutuelles avec Wave. Tontines privées sur invitation ou catalogue public, reçus PDF, alertes SMS.')

@section('content')

<section id="hero">
  <div class="hero-inner container">
    <div class="hero-content">
      <div class="hero-badge"><span class="tag">Nouveau</span> Mutuelle solidaire et exports PDF / Excel</div>
      <h1>Épargnez ensemble,<br><em>en toute confiance.</em></h1>
      <p>SUNUNATT digitalise la tontine : créez votre groupe, invitez par code, cotisez avec Wave et suivez chaque franc depuis votre téléphone.</p>

      <div class="hero-ctas">
        <a href="{{ config('tontine.play_store') }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-lg btn-store">
          <svg viewBox="0 0 24 24" aria-hidden="true" fill="currentColor"><path d="M4 2.6v18.8c0 .5.5.8.9.6l10.4-9.4L4.9 2.1c-.4-.3-.9 0-.9.5z" opacity=".95"/><path d="M16.4 11.3 6.3 2.2l9.1 5.2 1 3.9zM16.4 12.7l-1 3.9-9.1 5.2 10.1-9.1z" opacity=".7"/><path d="m16.4 11.3 3.2 1.8c.5.3.5 1 0 1.3l-3.2 1.8-1.3-2.5 1.3-2.4z"/></svg>
          <span class="btn-store-text"><span class="small">Disponible sur</span><span class="big">Google Play</span></span>
        </a>
        <a href="#comment_marche" class="btn btn-outline btn-lg">Voir comment ça marche</a>
      </div>

      <div class="hero-partners">
        <span>Paiement</span>
        <span class="chip"><span class="dot"></span>Wave</span>
        <span class="chip soon"><span class="dot"></span>Orange Money bientôt</span>
        <span class="chip"><span class="dot"></span>iOS à venir</span>
      </div>
    </div>

    <div class="hero-visual">
      <span class="blob-green" aria-hidden="true"></span>
      <div class="phone-toast" aria-hidden="true">
        <span class="ic">✓</span>
        <div><strong>Cotisation reçue</strong><small>Moussa · 25 000 FCFA · Wave</small></div>
      </div>
      <div class="phone-mockup" aria-hidden="true">
        <div class="phone-screen">
          <div class="phone-top-bar">
            <span class="phone-greeting"><small>Bonjour</small>Aminata</span>
            <span class="phone-avatar">AD</span>
          </div>
          <div class="phone-balance-card">
            <div class="phone-balance-label">Solde tontine</div>
            <div class="phone-balance-amount">245 000 FCFA</div>
            <span class="phone-balance-sub">● Prochain tour dans 5 jours</span>
          </div>
          <div class="phone-quick-actions">
            <div class="phone-action"><div class="phone-action-icon">+</div>Cotiser</div>
            <div class="phone-action"><div class="phone-action-icon">#</div>Code</div>
            <div class="phone-action"><div class="phone-action-icon">◎</div>Tirelire</div>
            <div class="phone-action"><div class="phone-action-icon">♥</div>Mutuelle</div>
          </div>
          <div class="phone-section-title">Mes tontines</div>
          <div class="phone-tontine-item">
            <div class="tontine-avatar">GF</div>
            <div class="tontine-info">
              <div class="tontine-name">Groupement Femmes Dakar</div>
              <div class="tontine-sub">Code AB3K7P · 8 membres · mensuelle</div>
            </div>
            <div class="tontine-amount">50k</div>
          </div>
          <div class="phone-tontine-item">
            <div class="tontine-avatar y">FT</div>
            <div class="tontine-info">
              <div class="tontine-name">Famille Thiès</div>
              <div class="tontine-sub">Privée · 12 membres</div>
            </div>
            <div class="tontine-amount">20k</div>
          </div>
          <div class="phone-tontine-item">
            <div class="tontine-avatar g">TS</div>
            <div class="tontine-info">
              <div class="tontine-name">Tirelire scolaire</div>
              <div class="tontine-sub">Objectif 300 000 · 62 %</div>
            </div>
            <div class="tontine-amount">186k</div>
          </div>
          <div class="phone-nav"><span class="on">Accueil</span><span>Tontines</span><span>Suivi</span><span>Profil</span></div>
        </div>
      </div>
    </div>
  </div>
</section>

<div class="stats-bar">
  <div class="stats-bar-inner">
    <div class="stat-bar-item reveal">
      <div class="stat-bar-icon">📱</div>
      <div><div class="stat-bar-value">Wave</div><div class="stat-bar-label">Paiement mobile intégré, Orange Money à venir</div></div>
    </div>
    <div class="stat-bar-item reveal">
      <div class="stat-bar-icon">🧾</div>
      <div><div class="stat-bar-value">Reçu PDF</div><div class="stat-bar-label">À chaque cotisation, suivi en temps réel</div></div>
    </div>
    <div class="stat-bar-item reveal">
      <div class="stat-bar-icon">🔒</div>
      <div><div class="stat-bar-value">KYC + OTP</div><div class="stat-bar-label">Identité vérifiée, membres validés par le créateur</div></div>
    </div>
    <div class="stat-bar-item reveal">
      <div class="stat-bar-icon">✓</div>
      <div><div class="stat-bar-value">Gratuit</div><div class="stat-bar-label">Téléchargement et inscription sans frais</div></div>
    </div>
  </div>
</div>

<section id="comment_marche">
  <div class="container">
    <div class="section-header reveal">
      <div class="section-label">Comment ça marche</div>
      <h2 class="section-title">Trois étapes, zéro paperasse</h2>
      <p class="section-sub">De la création du groupe au premier versement, tout se passe dans l'application.</p>
    </div>
    <div class="steps-grid">
      <div class="step-card reveal">
        <div class="step-num">1</div>
        <div class="step-title">Créez votre tontine</div>
        <p class="step-desc">Montant, nombre de membres, durée. Privée, elle reste entre vous ; publiée au catalogue, elle est vérifiée par notre équipe sous 24 à 48 h.</p>
      </div>
      <div class="step-card reveal">
        <div class="step-num">2</div>
        <div class="step-title">Invitez vos membres</div>
        <p class="step-desc">Partagez votre code à 6 caractères ou le lien sununatt.sn/join/CODE par WhatsApp ou SMS. Vous validez chaque demande avant l'entrée.</p>
      </div>
      <div class="step-card reveal">
        <div class="step-num">3</div>
        <div class="step-title">Cotisez et recevez</div>
        <p class="step-desc">Payez avec Wave, recevez votre reçu PDF immédiatement. Au tour convenu, les fonds arrivent sur votre mobile et chacun est notifié.</p>
      </div>
    </div>
  </div>
</section>

<section id="features">
  <div class="container">
    <div class="section-intro reveal">
      <div class="section-label">Fonctionnalités</div>
      <h2 class="section-title">Tout pour épargner ensemble</h2>
      <p class="section-sub">Tontine, tirelire, mutuelle : les outils essentiels, pensés pour le quotidien.</p>
    </div>
    <div class="cards-grid">
      <div class="card reveal">
        <div class="card-icon-wrap"><i class="fas fa-users card-icon"></i></div>
        <h4>Tontines privées ou catalogue</h4>
        <p>Privée : accès uniquement par code d'invitation, le créateur valide les membres. Catalogue : ouverte à tous après vérification par notre équipe.</p>
      </div>
      <div class="card reveal">
        <div class="card-icon-wrap"><i class="fas fa-mobile-alt card-icon"></i></div>
        <h4>Paiements Wave et reçus</h4>
        <p>Cotisez en quelques secondes avec Wave. Chaque paiement génère un reçu PDF. Orange Money arrive bientôt.</p>
      </div>
      <div class="card reveal">
        <div class="card-icon-wrap"><i class="fas fa-piggy-bank card-icon"></i></div>
        <h4>Tirelire digitale</h4>
        <p>Épargnez avec un objectif et une échéance. Retrait anticipé possible avec une pénalité de 10 %, affichée avant confirmation.</p>
      </div>
      <div class="card reveal">
        <div class="card-icon-wrap"><i class="fas fa-hand-holding-heart card-icon"></i></div>
        <h4>Mutuelle solidaire</h4>
        <p>Créez une cagnotte pour jusqu'à 50 bénéficiaires, fixez un taux, suivez les remboursements jusqu'à la clôture.</p>
      </div>
      <div class="card reveal">
        <div class="card-icon-wrap"><i class="fas fa-file-export card-icon"></i></div>
        <h4>Suivi et exports</h4>
        <p>Tableau de suivi, exports PDF et Excel, procès-verbal d'assemblée prêt à partager avec le groupe.</p>
      </div>
      <div class="card reveal">
        <div class="card-icon-wrap"><i class="fas fa-bell card-icon"></i></div>
        <h4>Sécurité et alertes</h4>
        <p>Vérification d'identité, code OTP, notifications push, SMS et e-mail pour chaque adhésion, cotisation et échéance.</p>
      </div>
    </div>
  </div>
</section>

<section id="temoignages">
  <div class="container">
    <div class="section-header reveal">
      <div class="section-label">Témoignages</div>
      <h2 class="section-title">Ils nous font confiance</h2>
    </div>
    <div class="testimonials-grid">
      <div class="testimonial-card reveal">
        <div class="quote-mark">“</div>
        <p class="testimonial-text">Avant, on perdait du temps à se retrouver pour compter l'argent. Maintenant tout est automatique. Plus de conflits dans notre GIE.</p>
        <div class="testimonial-author">
          <div class="author-avatar">AM</div>
          <div><div class="author-name">Aïssatou Mbaye</div><div class="author-role">Présidente GIE, Dakar</div></div>
        </div>
      </div>
      <div class="testimonial-card reveal">
        <div class="quote-mark">“</div>
        <p class="testimonial-text">Je suis en France mais je participe à la tontine familiale à Thiès sans difficulté. C'est exactement ce qu'il nous fallait.</p>
        <div class="testimonial-author">
          <div class="author-avatar">PD</div>
          <div><div class="author-name">Papa Diallo</div><div class="author-role">Diaspora sénégalaise, Lyon</div></div>
        </div>
      </div>
      <div class="testimonial-card reveal">
        <div class="quote-mark">“</div>
        <p class="testimonial-text">Notre association compte 30 membres. SUNUNATT gère les cotisations, les tours et les rappels. Le suivi est clair pour tout le monde.</p>
        <div class="testimonial-author">
          <div class="author-avatar">SK</div>
          <div><div class="author-name">Seydou Konaté</div><div class="author-role">Gérant d'association, Ziguinchor</div></div>
        </div>
      </div>
    </div>
  </div>
</section>

<section id="download">
  <div class="container">
    <div class="download-inner">
      <div class="reveal">
        <div class="section-label">Disponible sur Android</div>
        <h2 class="section-title">Téléchargez SUNUNATT <span class="text-gold">gratuitement</span></h2>
        <p class="section-sub">Version iOS à venir. Vous avez reçu un code d'invitation ? Installez l'app, inscrivez-vous et saisissez-le sur l'accueil.</p>
        <div class="store-buttons">
          <a href="{{ config('tontine.play_store') }}" target="_blank" rel="noopener noreferrer" class="store-btn">
            <span class="store-icon">▶</span>
            <span class="store-text"><span class="small">Disponible sur</span><span class="big">Google Play</span></span>
          </a>
          <div class="store-btn disabled" aria-disabled="true">
            <span class="store-icon"></span>
            <span class="store-text"><span class="small">Bientôt sur</span><span class="big">App Store</span></span>
          </div>
        </div>
      </div>
      <div class="download-promo-wrap reveal">
        <div class="download-promo-card">
          <h3>Rejoindre une tontine</h3>
          <p>Un code à 6 caractères suffit. Le créateur valide votre demande, vous êtes notifié, vous cotisez.</p>
          <div class="code-demo"><code>AB3K7P</code><span>Exemple de code d'invitation</span></div>
          <div class="promo-tags">
            <span class="promo-tag">Gratuit</span>
            <span class="promo-tag">Sécurisé</span>
            <span class="promo-tag">Sans paperasse</span>
          </div>
        </div>
      </div>
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
