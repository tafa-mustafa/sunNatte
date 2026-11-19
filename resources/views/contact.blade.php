@extends('layouts.app')

@section('title', 'Contact SUNUNATT')
@section('description', 'Contactez l\'équipe SUNUNATT pour toute question ou demande d\'assistance.')

@section('content')

<section class="page-hero">
  <div class="container">
    <div class="eyebrow">Contact</div>
    <h1>Parlons de votre projet</h1>
    <p>Une question, un partenariat ou une demande d'assistance ? Écrivez-nous, nous vous répondons sous 48 h ouvrées.</p>
  </div>
</section>

<section class="page-contact">
  <div class="container">

    @if (session('success'))
      <div class="alert alert-success" role="alert">{{ session('success') }}</div>
    @endif
    @if (session('error'))
      <div class="alert alert-error" role="alert">{{ session('error') }}</div>
    @endif

    <div class="contact-grid">
      <div class="contact-form reveal">
        <form id="contactForm" action="{{ route('contact.send') }}" method="POST" novalidate>
          @csrf
          <h3>Envoyez-nous un message</h3>
          <p style="color:var(--g-600);font-size:15px;">Les champs marqués * sont obligatoires.</p>

          <div class="form-grid">
            <div class="form-group">
              <label class="form-label" for="name">Nom complet *</label>
              <input type="text" name="name" id="name" class="form-input" placeholder="Votre nom" value="{{ old('name') }}" required autocomplete="name">
              @error('name')<span class="form-error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
              <label class="form-label" for="email">Email *</label>
              <input type="email" name="email" id="email" class="form-input" placeholder="email@exemple.com" value="{{ old('email') }}" required autocomplete="email">
              @error('email')<span class="form-error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
              <label class="form-label" for="phone">Téléphone</label>
              <input type="tel" name="phone" id="phone" class="form-input" placeholder="+221 7X XXX XX XX" value="{{ old('phone') }}" autocomplete="tel">
            </div>

            <div class="form-group">
              <label class="form-label" for="subject">Sujet *</label>
              <select name="subject" id="subject" class="form-input" required>
                <option value="">Choisissez un sujet</option>
                @foreach (['Question générale', 'Partenariat', 'Presse', 'Investissement', 'Support technique'] as $option)
                  <option value="{{ $option }}" {{ old('subject') == $option ? 'selected' : '' }}>{{ $option }}</option>
                @endforeach
              </select>
              @error('subject')<span class="form-error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group full">
              <label class="form-label" for="message">Message *</label>
              <textarea name="message" id="message" class="form-input" placeholder="Votre message" required>{{ old('message') }}</textarea>
              @error('message')<span class="form-error">{{ $message }}</span>@enderror
            </div>
          </div>

          <button type="submit" class="btn btn-primary btn-lg btn-submit">
            Envoyer le message
          </button>
        </form>
      </div>

      <aside class="contact-info reveal">
        <h3>Coordonnées</h3>
        <p>Utilisateur, partenaire, presse ou investisseur : contactez-nous par le canal de votre choix.</p>
        <div class="contact-detail">
          <i class="fas fa-envelope" aria-hidden="true"></i>
          <div><span>Contact général</span><span><a href="mailto:info@sununatt.com">info@sununatt.com</a></span></div>
        </div>
        <div class="contact-detail">
          <i class="fas fa-user-tie" aria-hidden="true"></i>
          <div><span>Direction</span><span><a href="mailto:b.gomis@sununatt.com">b.gomis@sununatt.com</a></span></div>
        </div>
        <div class="contact-detail">
          <i class="fas fa-phone" aria-hidden="true"></i>
          <div><span>Téléphone</span><span><a href="tel:+221773377102">+221 77 337 71 02</a></span></div>
        </div>
        <div class="contact-detail">
          <i class="fas fa-location-dot" aria-hidden="true"></i>
          <div><span>Adresse</span><span>Diamalaye 2, villa 199E, Dakar, Sénégal</span></div>
        </div>
      </aside>
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
