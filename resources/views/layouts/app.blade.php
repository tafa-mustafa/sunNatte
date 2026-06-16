<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="@yield('description', 'SUNUNATT — La tontine numérique de l\'Afrique de l\'Ouest. Gérez vos tontines en toute sécurité avec Wave, Orange Money et Free Money.')">
<meta property="og:title" content="@yield('og_title', 'SUNUNATT — La tontine numérique de l\'Afrique')">
<meta property="og:description" content="@yield('og_description', 'Gérez vos tontines en toute sécurité. Simple, transparent, accessible.')">
<meta property="og:image" content="https://sununatt.sn/og-image.jpg">
<link rel="icon" type="image/x-icon" href="{{ asset('images/logo_icon.png') }}">
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<title>@yield('title', 'SUNUNATT — La tontine numérique de l\'Afrique de l\'Ouest')</title>
</head>
<body>

<!-- NAV -->
<nav>
  <div class="logo">
    <a href="{{ route('home') }}" style="cursor:pointer;text-decoration:none;">
      <img src="{{ asset('images/logo.jpeg') }}" alt="SUNUNATT_logo">
      <strong style="color:#3273B7;">SUNUNATT</strong>
    </a>
  </div>

  <ul class="nav-links" id="navLinks">
    <li><a href="{{ route('home') }}">Accueil</a></li>
    <li><a href="{{ route('home') }}#comment_marche">Comment ça marche</a></li>
    <li><a href="{{ route('a-propos') }}">À propos</a></li>
    <li><a href="{{ route('contact') }}">Contact</a></li>
    <li><a href="{{ route('mentions-legales') }}">Mentions légales</a></li>
    <li>
      <a href="https://play.google.com/store/apps/details?id=com.teknon.sununatt"
         target="_blank" class="btn btn-primary" style="padding:10px 20px;font-size:14px;">
        Télécharger
      </a>
    </li>
  </ul>
  <button class="hamburger" onclick="toggleMobile()" aria-label="Menu">
    <span></span><span></span><span></span>
  </button>
</nav>

<!-- MENU MOBILE -->
<div class="mobile-menu" id="mobileMenu">
  <a href="{{ route('home') }}" onclick="closeMobile()">Accueil</a>
  <a href="{{ route('home') }}#comment_marche" onclick="closeMobile()">Comment ça marche</a>
  <a href="{{ route('a-propos') }}" onclick="closeMobile()">À propos</a>
  <a href="{{ route('contact') }}" onclick="closeMobile()">Contact</a>
  <a href="{{ route('mentions-legales') }}" onclick="closeMobile()">Mentions légales</a>
  <a href="https://play.google.com/store/apps/details?id=com.teknon.sununatt"
     target="_blank" class="btn btn-primary" style="margin-top:1rem;">
    Télécharger l'app
  </a>
</div>

@yield('content')

<!-- PIED DE PAGE -->
<footer>
  <div class="footer-inner">
    <div class="footer-brand">
      <div class="footer-logo">
        <img src="{{ asset('images/logo_icon.png') }}" alt="footer-logo-icon" style="width:50px;">
        <span class="footer-logo-text">SUNUNATT</span>
      </div>
      <p>La tontine numérique de l'Afrique de l'Ouest. Simple, sécurisé, accessible à tous.</p>
      <p style="font-size:12px;color:rgba(255,255,255,0.4);">TEKNON x GIVE1PROJECT · 2025</p>
    </div>
    <div class="footer-col">
      <h4>Produit</h4>
      <a href="{{ route('home') }}#comment_marche">Comment ça marche</a>
      <a href="https://play.google.com/store/apps/details?id=com.teknon.sununatt" target="_blank">Télécharger l'app</a>
    </div>
    <div class="footer-col">
      <h4>Entreprise</h4>
      <a href="{{ route('a-propos') }}">À propos</a>
      <a href="{{ route('contact') }}">Contact</a>
      <a href="{{ route('mentions-legales') }}">Mentions légales</a>
    </div>
    <div class="footer-col"></div>
  </div>
  <div class="footer-bottom">
    <span>© 2025 SUNUNATT · TEKNON. Tous droits réservés.</span>
  </div>
</footer>

<script src="{{ asset('js/script.js') }}"></script>
</body>
</html>
