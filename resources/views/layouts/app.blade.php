<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#ffffff">
<meta name="description" content="@yield('description', 'SUNUNATT, la tontine numérique de l\'Afrique de l\'Ouest. Gérez vos tontines, tirelires et mutuelles en toute sécurité avec Wave.')">
<meta property="og:title" content="@yield('og_title', 'SUNUNATT, la tontine numérique de l\'Afrique')">
<meta property="og:description" content="@yield('og_description', 'Gérez vos tontines en toute sécurité. Simple, transparent, accessible.')">
<meta property="og:image" content="https://sununatt.sn/og-image.jpg">
<link rel="icon" type="image/x-icon" href="{{ asset('images/logo_icon.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Roboto+Mono:wght@500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@foreach (['style', 'home', 'pages'] as $sheet)
<link rel="stylesheet" href="{{ asset('css/'.$sheet.'.css') }}?v={{ @filemtime(public_path('css/'.$sheet.'.css')) ?: '1' }}">
@endforeach
<title>@yield('title', 'SUNUNATT, la tontine numérique de l\'Afrique de l\'Ouest')</title>
</head>
<body>

<a href="#main-content" class="skip-link">Aller au contenu</a>

<nav aria-label="Navigation principale">
  <div class="logo">
    <a href="{{ route('home') }}">
      <img src="{{ asset('images/logo.jpeg') }}" alt="Logo SUNUNATT">
      <strong>SUNUNATT</strong>
    </a>
  </div>

  <ul class="nav-links" id="navLinks">
    <li><a href="{{ route('home') }}" data-nav="home">Accueil</a></li>
    <li><a href="{{ route('home') }}#comment_marche" data-nav="how">Comment ça marche</a></li>
    <li><a href="{{ route('home') }}#features" data-nav="features">Fonctionnalités</a></li>
    <li><a href="{{ route('a-propos') }}" data-nav="about">À propos</a></li>
    <li><a href="{{ route('contact') }}" data-nav="contact">Contact</a></li>
    <li>
      <a href="{{ config('tontine.play_store') }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary nav-cta">
        Télécharger l'app
      </a>
    </li>
  </ul>
  <button class="hamburger" id="hamburgerBtn" onclick="toggleMobile()" aria-label="Ouvrir le menu" aria-expanded="false" aria-controls="mobileMenu">
    <span></span><span></span><span></span>
  </button>
</nav>

<div class="mobile-overlay" id="mobileOverlay" onclick="closeMobile()"></div>

<div class="mobile-menu" id="mobileMenu" aria-hidden="true">
  <a href="{{ route('home') }}" data-nav="home" onclick="closeMobile()">Accueil</a>
  <a href="{{ route('home') }}#comment_marche" data-nav="how" onclick="closeMobile()">Comment ça marche</a>
  <a href="{{ route('home') }}#features" data-nav="features" onclick="closeMobile()">Fonctionnalités</a>
  <a href="{{ route('a-propos') }}" data-nav="about" onclick="closeMobile()">À propos</a>
  <a href="{{ route('contact') }}" data-nav="contact" onclick="closeMobile()">Contact</a>
  <a href="{{ route('mentions-legales') }}" data-nav="legal" onclick="closeMobile()">Mentions légales</a>
  <a href="{{ config('tontine.play_store') }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary" style="margin-top:12px;">
    Télécharger l'app
  </a>
</div>

<main id="main-content">
@yield('content')
</main>

<footer>
  <div class="footer-inner">
    <div class="footer-brand">
      <div class="footer-logo">
        <img src="{{ asset('images/logo_icon.png') }}" alt="SUNUNATT">
        <span class="footer-logo-text">SUNUNATT</span>
      </div>
      <p>La tontine numérique de l'Afrique de l'Ouest. Tontines, tirelires et mutuelles, simples, sécurisées, accessibles à tous.</p>
      <p class="footer-meta">TEKNON x GIVE1PROJECT, 2026</p>
    </div>
    <div class="footer-col">
      <h4>Produit</h4>
      <a href="{{ route('home') }}#comment_marche">Comment ça marche</a>
      <a href="{{ route('home') }}#features">Fonctionnalités</a>
      <a href="{{ config('tontine.play_store') }}" target="_blank" rel="noopener noreferrer">Application Android</a>
    </div>
    <div class="footer-col">
      <h4>Entreprise</h4>
      <a href="{{ route('a-propos') }}">À propos</a>
      <a href="{{ route('contact') }}">Contact</a>
      <a href="{{ route('mentions-legales') }}">Mentions légales</a>
    </div>
    <div class="footer-col">
      <h4>Support</h4>
      <a href="mailto:info@sununatt.com">info@sununatt.com</a>
      <a href="tel:+221773377102">+221 77 337 71 02</a>
      <a href="{{ route('contact') }}">Centre d'aide</a>
    </div>
  </div>
  <div class="footer-bottom">
    <span>© 2026 SUNUNATT, TEKNON. Tous droits réservés.</span>
    <a href="{{ route('mentions-legales') }}">Confidentialité</a>
    <a href="{{ route('mentions-legales') }}#donnees">Données personnelles</a>
  </div>
</footer>

<script src="{{ asset('js/script.js') }}?v={{ @filemtime(public_path('js/script.js')) ?: '1' }}"></script>
</body>
</html>
