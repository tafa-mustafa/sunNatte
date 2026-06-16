@extends('layouts.app')

@section('title', 'Contact — SUNUNATT')
@section('description', 'Contactez l\'équipe SUNUNATT. Nous répondons à toutes vos questions.')

@section('content')

<section style="background:#F0F7FF;padding:60px 0;">
  <div class="container">

    {{-- Messages flash --}}
    @if (session('success'))
      <div class="notification success" style="position:relative;top:auto;right:auto;margin-bottom:24px;display:block;">
        ✅ {{ session('success') }}
      </div>
    @endif
    @if (session('error'))
      <div class="notification error" style="position:relative;top:auto;right:auto;margin-bottom:24px;display:block;">
        ❌ {{ session('error') }}
      </div>
    @endif

    <div class="contact-grid">
      <!-- Formulaire -->
      <div class="contact-form">
        <form id="contactForm" action="{{ route('contact.send') }}" method="POST">
          @csrf
          <h3 style="font-size:24px;margin-bottom:24px;">Envoyez-nous un message</h3>

          <div class="form-group">
            <label class="form-label">Nom complet *</label>
            <input type="text" name="name" id="name" class="form-input"
                   placeholder="Votre nom" value="{{ old('name') }}">
            @error('name')<span style="color:red;font-size:13px;">{{ $message }}</span>@enderror
          </div>

          <div class="form-group">
            <label class="form-label">Email *</label>
            <input type="email" name="email" id="email" class="form-input"
                   placeholder="email@exemple.com" value="{{ old('email') }}">
            @error('email')<span style="color:red;font-size:13px;">{{ $message }}</span>@enderror
          </div>

          <div class="form-group">
            <label class="form-label">Téléphone</label>
            <input type="tel" name="phone" id="phone" class="form-input"
                   placeholder="+221 7X XXX XX XX" value="{{ old('phone') }}">
          </div>

          <div class="form-group">
            <label class="form-label">Sujet *</label>
            <select name="subject" id="subject" class="form-input">
              <option value="">-- Choisissez un sujet --</option>
              <option {{ old('subject') == 'Question générale' ? 'selected' : '' }}>Question générale</option>
              <option {{ old('subject') == 'Partenariat' ? 'selected' : '' }}>Partenariat</option>
              <option {{ old('subject') == 'Presse' ? 'selected' : '' }}>Presse</option>
              <option {{ old('subject') == 'Investissement' ? 'selected' : '' }}>Investissement</option>
              <option {{ old('subject') == 'Support technique' ? 'selected' : '' }}>Support technique</option>
            </select>
            @error('subject')<span style="color:red;font-size:13px;">{{ $message }}</span>@enderror
          </div>

          <div class="form-group">
            <label class="form-label">Message *</label>
            <textarea name="message" id="message" class="form-input"
                      placeholder="Votre message...">{{ old('message') }}</textarea>
            @error('message')<span style="color:red;font-size:13px;">{{ $message }}</span>@enderror
          </div>

          <button type="submit" class="btn" style="width:100%;justify-content:center;">
            Envoyer le message →
          </button>
        </form>
      </div>

      <!-- Informations de contact -->
      <div class="contact-info">
        <h3>Retrouvez-nous</h3>
        <p>Que vous soyez un utilisateur, un partenaire potentiel, un journaliste ou un investisseur, nous sommes là pour vous.</p>
        <div class="contact-detail">
          <span>Contact générale :</span>
          <span>info@sununatt.com</span>
        </div>
        <div class="contact-detail">
          <span>Direction :</span>
          <span>b.gomis@sununatt.com</span>
        </div>
        <div class="contact-detail">
          <span>Téléphone :</span>
          <span>+221 77 337 71 02</span>
        </div>
        <div class="contact-detail">
          <span>Adresse :</span>
          <span>Diamalaye 2, villa 199E, Dakar, Sénégal</span>
        </div>
      </div>
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
