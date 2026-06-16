@extends('layouts.app')

@section('title', 'SUNUNATT — La tontine numérique de l\'Afrique de l\'Ouest')

@section('content')

<!-- ══════════ HERO ══════════ -->
<section id="hero">
  <div class="hero-inner container">
    <div class="hero-content">
      <div class="hero-badge">🌍 La tontine numérique de l'Afrique</div>
      <h1>Gérez vos tontines<br>en toute sécurité</h1>
      <p>SUNUNATT digitalise vos tontines avec Wave, Orange Money et Free Money. Simple, transparent, accessible à tous.</p>

      <div class="hero-ctas">
        <a href="https://play.google.com/store/apps/details?id=com.teknon.sununatt"
           target="_blank" rel="noopener noreferrer" class="btn-store">
          <div class="btn-store-icon">
            <img src="https://play.google.com/favicon.ico" alt="Google Play">
          </div>
          <div class="btn-store-text">
            <div class="small">Télécharger sur</div>
            <div class="big">Google Play</div>
          </div>
        </a>
        <div class="btn-store disabled">
          <div class="btn-store-icon">
            <img src="https://favicon.im/appstore.com" alt="App Store">
          </div>
          <div class="btn-store-text">
            <div class="small">Bientôt sur</div>
            <div class="big">App Store</div>
          </div>
        </div>
      </div>

      <div class="hero-partners">
        <span>Compatible avec</span>
        <span class="partner-badge">Wave</span>
        <span class="partner-badge">Orange Money</span>
        <span class="partner-badge">Free Money</span>
      </div>
    </div>

    <div class="hero-visual">
      <div class="phone-mockup">
        <div class="phone-screen">
          <div class="phone-top-bar">
            <div class="phone-logo-small">
              <svg viewBox="0 0 14 14" fill="none">
                <path d="M2 7C2 4.5 4.5 2 7 2C9.5 2 12 4.5 12 7" stroke="white" stroke-width="2" stroke-linecap="round"/>
                <path d="M12 7C12 9.5 9.5 12 7 12C4.5 12 2 9.5 2 7" stroke="white" stroke-width="2" stroke-linecap="round" stroke-dasharray="2 1.5"/>
                <circle cx="7" cy="7" r="1.5" fill="white"/>
              </svg>
            </div>
            <span class="phone-greeting">Bonjour, Aminata 👋</span>
          </div>
          <div class="phone-balance-card">
            <div class="phone-balance-label">Solde tontine</div>
            <div class="phone-balance-amount">245 000 FCFA</div>
            <div class="phone-balance-sub">Prochain tour dans 5 jours</div>
          </div>
          <div class="phone-quick-actions">
            <div class="phone-action"><div class="phone-action-icon">➕</div>Cotiser</div>
            <div class="phone-action"><div class="phone-action-icon">📋</div>Historique</div>
            <div class="phone-action"><div class="phone-action-icon">👥</div>Membres</div>
            <div class="phone-action"><div class="phone-action-icon">🐖</div>Tirelire</div>
          </div>
          <div class="phone-tontine-item">
            <div class="tontine-avatar">GF</div>
            <div class="tontine-info">
              <div class="tontine-name">Groupement Femmes Dakar</div>
              <div class="tontine-sub">8 membres · Mensuelle</div>
            </div>
            <div class="tontine-amount">50k/mois</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══════════ STATS BAR ══════════ -->
<div class="stats-bar">
  <div class="stats-bar-inner">
    <div class="stat-bar-item">
      <div class="stat-bar-icon">📱</div>
      <div>
        <div class="stat-bar-value">3</div>
        <div class="stat-bar-label">Opérateurs — Wave · Orange · Free</div>
      </div>
    </div>
    <div class="stat-bar-item">
      <div class="stat-bar-icon">⚡</div>
      <div>
        <div class="stat-bar-value">24/7</div>
        <div class="stat-bar-label">Accès permanent à vos fonds</div>
      </div>
    </div>
    <div class="stat-bar-item">
      <div class="stat-bar-icon">🔒</div>
      <div>
        <div class="stat-bar-value">100%</div>
        <div class="stat-bar-label">Sécurisé — KYC &amp; double authentification</div>
      </div>
    </div>
    <div class="stat-bar-item">
      <div class="stat-bar-icon">✅</div>
      <div>
        <div class="stat-bar-value">Gratuit</div>
        <div class="stat-bar-label">Téléchargement &amp; inscription sans frais</div>
      </div>
    </div>
  </div>
</div>

<!-- ══════════ COMMENT ÇA MARCHE ══════════ -->
<section id="comment_marche">
  <div class="container">
    <div class="section-header reveal">
      <div class="section-label">Comment ça marche</div>
      <div class="section-title">Simple comme <strong style="color:var(--gold-dark);">bonjour</strong></div>
      <p class="section-sub">Trois étapes et vous êtes dans votre tontine digitale.</p>
    </div>
    <div class="steps-grid">
      <div class="step-card reveal">
        <div class="step-num">1</div>
        <div class="step-title">Créez votre tontine</div>
        <p class="step-desc">Définissez le montant, la fréquence et les règles. Votre tontine est en ligne en quelques minutes.</p>
      </div>
      <div class="step-card reveal">
        <div class="step-num">2</div>
        <div class="step-title">Invitez vos membres</div>
        <p class="step-desc">Envoyez des invitations par SMS ou lien. Chaque membre rejoint facilement depuis son téléphone.</p>
      </div>
      <div class="step-card reveal">
        <div class="step-num">3</div>
        <div class="step-title">Cotisez et recevez</div>
        <p class="step-desc">Payez avec Wave, Orange Money ou Free Money. Le tour venu, vous recevez directement sur votre mobile.</p>
      </div>
    </div>
  </div>
</section>

<!-- ══════════ FONCTIONNALITÉS ══════════ -->
<section id="section">
  <div class="container">
    <div class="section-header">
      <div class="section-label">Fonctionnalités</div>
      <div class="section-title">Tout ce dont vous avez <strong style="color:var(--gold-dark);">besoin</strong></div>
    </div>
    <div class="cards-grid">
      <div class="card reveal">
        <div class="card-icon-wrap"><i class="fas fa-users card-icon"></i></div>
        <h4>Tontines en ligne</h4>
        <p>Publiques ou privées, créez ou rejoignez des groupes facilement.</p>
      </div>
      <div class="card reveal">
        <div class="card-icon-wrap"><i class="fas fa-mobile-alt card-icon"></i></div>
        <h4>Paiements mobiles</h4>
        <p>Cotisez via Wave, Orange Money ou Free Money en quelques secondes.</p>
      </div>
      <div class="card reveal">
        <div class="card-icon-wrap"><i class="fas fa-piggy-bank card-icon"></i></div>
        <h4>Tirelire digitale</h4>
        <p>Épargnez avec discipline. Pénalité 10% en cas de retrait anticipé.</p>
      </div>
      <div class="card reveal">
        <div class="card-icon-wrap"><i class="fas fa-shield-alt card-icon"></i></div>
        <h4>Sécurité KYC</h4>
        <p>Double authentification. Fonds sur compte de cantonnement agréé.</p>
      </div>
      <div class="card reveal">
        <div class="card-icon-wrap"><i class="fas fa-chart-line card-icon"></i></div>
        <h4>Transparence totale</h4>
        <p>Historique complet de toutes les transactions, consultable à tout moment.</p>
      </div>
      <div class="card reveal">
        <div class="card-icon-wrap"><i class="fas fa-bell card-icon"></i></div>
        <h4>Alertes intelligentes</h4>
        <p>SMS et notifications push pour ne jamais manquer une échéance.</p>
      </div>
    </div>
  </div>
</section>

<!-- ══════════ TÉMOIGNAGES ══════════ -->
<section id="temoignages">
  <div class="container">
    <div class="section-header reveal">
      <div class="section-label">Témoignages</div>
      <div class="section-title">Ils nous font <strong style="color:var(--gold-dark);">confiance</strong></div>
    </div>
    <div class="testimonials-grid">
      <div class="testimonial-card reveal">
        <div class="quote-mark">"</div>
        <p class="testimonial-text">Avant, on perdait du temps à se retrouver pour compter l'argent. Maintenant tout est automatique. Plus de conflits dans notre GIE !</p>
        <div class="testimonial-author">
          <div class="author-avatar">AM</div>
          <div>
            <div class="author-name">Aïssatou Mbaye</div>
            <div class="author-role">Présidente GIE, Dakar</div>
          </div>
        </div>
      </div>
      <div class="testimonial-card reveal">
        <div class="quote-mark">"</div>
        <p class="testimonial-text">Je suis en France mais je participe à la tontine familiale à Thiès sans problème. C'est exactement ce qu'il nous fallait !</p>
        <div class="testimonial-author">
          <div class="author-avatar">PD</div>
          <div>
            <div class="author-name">Papa Diallo</div>
            <div class="author-role">Diaspora sénégalaise, Lyon</div>
          </div>
        </div>
      </div>
      <div class="testimonial-card reveal">
        <div class="quote-mark">"</div>
        <p class="testimonial-text">Notre association compte 30 membres. SUNUNATT gère tout : les cotisations, les tours, les rappels. C'est vraiment professionnel.</p>
        <div class="testimonial-author">
          <div class="author-avatar">SK</div>
          <div>
            <div class="author-name">Seydou Konaté</div>
            <div class="author-role">Gérant d'association, Ziguinchor</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══════════ TÉLÉCHARGER ══════════ -->
<section id="download">
  <div class="container">
    <div class="download-inner">
      <div class="reveal">
        <div class="section-label">Disponible sur</div>
        <div class="section-title">Téléchargez SUNUNATT <strong style="color:var(--gold-dark);">gratuitement</strong></div>
        <p class="section-sub">Disponible sur Android. iOS bientôt disponible.</p>
        <div class="store-buttons">
          <a href="https://play.google.com/store/apps/details?id=com.teknon.sununatt" target="_blank" class="store-btn">
            <span class="store-icon">▶</span>
            <div class="store-text">
              <div class="small">Disponible sur</div>
              <div class="big">Google Play</div>
            </div>
          </a>
          <div class="store-btn disabled">
            <span class="store-icon"></span>
            <div class="store-text">
              <div class="small">Bientôt sur</div>
              <div class="big">App Store</div>
            </div>
          </div>
        </div>
      </div>
      <div class="reveal" style="display:flex;justify-content:center;">
        <div style="background:linear-gradient(135deg,var(--primary),var(--primary-navy));border-radius:28px;padding:3rem;max-width:400px;width:100%;color:white;text-align:center;box-shadow:var(--shadow-blue);">
          <div style="font-size:52px;margin-bottom:1.25rem;">📲</div>
          <h3 style="font-size:22px;font-weight:800;margin-bottom:0.875rem;letter-spacing:-0.5px;">Votre argent, votre contrôle</h3>
          <p style="font-size:15px;opacity:0.82;line-height:1.78;margin-bottom:1.75rem;">La tontine que vous connaissez, digitalisée. Ensemble, on va plus loin.</p>
          <div style="display:flex;gap:0.75rem;justify-content:center;flex-wrap:wrap;">
            <span style="background:rgba(255,255,255,0.18);padding:7px 14px;border-radius:var(--r-full);font-size:12px;font-weight:700;">✅ Gratuit</span>
            <span style="background:rgba(255,255,255,0.18);padding:7px 14px;border-radius:var(--r-full);font-size:12px;font-weight:700;">🔒 Sécurisé</span>
            <span style="background:rgba(255,255,255,0.18);padding:7px 14px;border-radius:var(--r-full);font-size:12px;font-weight:700;">⚡ Instantané</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══════════ CTA FINAL ══════════ -->
<section id="cta-final">
  <div class="container">
    <h2 class="reveal">Rejoignez des milliers de Sénégalais qui font confiance à SUNUNATT</h2>
    <p class="reveal">Simple, transparent, accessible. Vos projets à portée de main.</p>
    <a href="https://play.google.com/store/apps/details?id=com.teknon.sununatt"
       target="_blank" class="btn btn-gold reveal">
      Télécharger gratuitement
    </a>
  </div>
</section>

@endsection
