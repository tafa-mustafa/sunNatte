@extends('admin.layout')
@section('title', ($user['prenom'] ?? '').' '.($user['nom'] ?? ''))
@section('content')
<p><a href="{{ route('admin.users') }}">← Utilisateurs</a></p>
<h1>{{ $user['prenom'] ?? '' }} {{ $user['nom'] ?? '' }}</h1>
<p class="hint">Endpoints : <code>GET /api/admin/users/{id}</code> · documents · update · activate/deactivate</p>
<p>KYC {{ $completeness['percent'] ?? 0 }}% — {{ !empty($completeness['kyc_ready']) ? 'prêt' : 'incomplet' }}</p>
@if(!empty($completeness['missing']))
  <ul>
    @foreach($completeness['missing'] as $miss)
      <li>{{ is_array($miss) ? ($miss['label'] ?? $miss['key']) : $miss }}</li>
    @endforeach
  </ul>
@endif

<form class="stack" method="post" action="{{ route('admin.users.update', $user['id']) }}" enctype="multipart/form-data">
  @csrf
  <input name="prenom" value="{{ $user['prenom'] ?? '' }}" placeholder="Prénom">
  <input name="nom" value="{{ $user['nom'] ?? '' }}" placeholder="Nom">
  <input name="phone" value="{{ $user['phone'] ?? '' }}" placeholder="Téléphone">
  <input name="email" value="{{ $user['email'] ?? '' }}" placeholder="E-mail">
  <input name="adresse" value="{{ $user['adresse'] ?? '' }}" placeholder="Adresse">
  <input name="profession" value="{{ $user['profession'] ?? '' }}" placeholder="Profession">
  <input name="num_cni" value="{{ $user['num_cni'] ?? '' }}" placeholder="CNI">
  <input name="num_passport" value="{{ $user['num_passport'] ?? '' }}" placeholder="Passeport">
  <button class="btn" type="submit">Enregistrer le profil</button>
</form>
<form method="post" action="{{ route('admin.users.toggle', $user['id']) }}">
  @csrf
  <button class="btn gold" type="submit">{{ !empty($user['statut']) ? 'Désactiver' : 'Activer' }}</button>
</form>

<h2>Documents</h2>
<table>
  <thead><tr><th>Nom</th><th>Fichier</th></tr></thead>
  <tbody>
    @forelse($documents as $doc)
      <tr>
        <td>{{ $doc['nom'] ?? '' }}</td>
        <td>
          @if(!empty($doc['document']))
            <a href="{{ asset('documents/'.$doc['document']) }}" target="_blank">{{ $doc['document'] }}</a>
          @endif
        </td>
      </tr>
    @empty
      <tr><td colspan="2">Aucun document.</td></tr>
    @endforelse
  </tbody>
</table>
@endsection
