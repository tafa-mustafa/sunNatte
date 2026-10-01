@extends('layouts.app')

@section('title', 'Rejoindre '.$tontine->nom.' — SUNUNATT')
@section('description', 'Rejoignez la tontine '.$tontine->nom.' sur SUNUNATT avec le code '.$code.'.')
@section('og_title', 'Rejoins ma tontine '.$tontine->nom)

@section('content')
<section class="hero-about">
  <div class="container">
    <h1>{{ $tontine->nom }}</h1>
    <p>Vous avez été invité à rejoindre cette tontine sur SUNUNATT.</p>
  </div>
</section>

<section class="page-contact">
  <div class="container">
    <div class="contact-form reveal">
      <h3>Code d'accès</h3>
      <p style="font-size:2rem;font-weight:800;letter-spacing:.2em;margin:1rem 0;">{{ $code }}</p>
      @if($tontine->description)
        <p>{{ $tontine->description }}</p>
      @endif
      <p>Téléchargez l'application, inscrivez-vous, puis saisissez ce code sur l'accueil.</p>
      <p>
        <a href="{{ config('tontine.play_store') }}" class="btn btn-primary" rel="noopener noreferrer">
          Télécharger l'app
        </a>
      </p>
    </div>
  </div>
</section>
@endsection
