@extends('layouts.app')

@section('title', 'Rejoindre '.$tontine->nom.' — SUNUNATT')
@section('description', 'Rejoignez la tontine '.$tontine->nom.' sur SUNUNATT avec le code '.$code.'.')
@section('og_title', 'Rejoins ma tontine '.$tontine->nom)

@section('content')
<section class="page-hero">
  <div class="container">
    <div class="eyebrow">Invitation</div>
    <h1>{{ $tontine->nom }}</h1>
    <p>Vous avez été invité à rejoindre cette tontine sur SUNUNATT.</p>
  </div>
</section>

<section>
  <div class="container">
    <div class="join-card reveal">
      <h3>Code d'accès</h3>
      <div class="join-code" id="joinCode">{{ $code }}</div>
      <button type="button" class="btn btn-text join-copy" data-copy="{{ $code }}">Copier le code</button>
      @if($tontine->description)
        <p class="desc">{{ $tontine->description }}</p>
      @endif
      <div class="join-steps">
        <div><b>1</b> Téléchargez l'application SUNUNATT</div>
        <div><b>2</b> Créez votre compte avec votre numéro</div>
        <div><b>3</b> Saisissez ce code sur l'accueil, le créateur valide votre demande</div>
      </div>
      <div class="join-actions">
        <a href="{{ $share['intent'] ?? config('tontine.play_store') }}" class="btn btn-primary btn-lg" rel="noopener noreferrer">
          Ouvrir dans l'app
        </a>
        <a href="{{ config('tontine.play_store') }}" class="btn btn-outline btn-lg" rel="noopener noreferrer">
          Télécharger l'app
        </a>
      </div>
    </div>
  </div>
</section>
@endsection
