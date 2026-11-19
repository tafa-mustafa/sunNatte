@extends('admin.layout')
@section('title', $tontine['nom'] ?? 'Tontine')
@section('content')
<p><a href="{{ route('admin.tontines') }}">← Tontines</a></p>
<h1>{{ $tontine['nom'] ?? '' }}</h1>
<p class="hint">Endpoints : <code>GET /api/admin/tontines/show/{id}</code> · update · open/close · adhere_user</p>
<p>Code {{ $tontine['code_adhesion'] ?? '' }} · {{ $tontine['visibilite'] ?? '' }} · {{ !empty($tontine['statut']) ? 'ouverte' : 'fermée' }}</p>

<form class="stack" method="post" action="{{ route('admin.tontines.update', $tontine['id']) }}">
  @csrf
  <strong>Modifier</strong>
  <input name="nom" value="{{ $tontine['nom'] ?? '' }}" required>
  <input name="nombre_personne" type="number" value="{{ $tontine['nombre_personne'] ?? '' }}">
  <input name="montant" type="number" value="{{ $tontine['montant'] ?? '' }}">
  <input name="duree" type="number" value="{{ $tontine['duree'] ?? '' }}">
  <input name="type" value="{{ $tontine['type'] ?? '' }}">
  <select name="visibilite">
    <option value="catalogue" @selected(($tontine['visibilite'] ?? '') === 'catalogue')>Catalogue</option>
    <option value="privee" @selected(($tontine['visibilite'] ?? '') === 'privee')>Privée</option>
  </select>
  <textarea name="description" rows="3">{{ $tontine['description'] ?? '' }}</textarea>
  <button class="btn" type="submit">Enregistrer</button>
</form>

<form method="post" action="{{ route('admin.tontines.toggle', $tontine['id']) }}">
  @csrf
  <button class="btn gold" type="submit">{{ !empty($tontine['statut']) ? 'Fermer' : 'Ouvrir' }}</button>
</form>

<h2>Membres</h2>
<table>
  <thead><tr><th>Nom</th><th>Badge</th><th>Statut pivot</th></tr></thead>
  <tbody>
    @forelse($tontine['users'] ?? [] as $m)
      <tr>
        <td><a href="{{ route('admin.users.show', $m['id']) }}">{{ $m['prenom'] ?? '' }} {{ $m['nom'] ?? '' }}</a></td>
        <td>{{ $m['pivot']['badge'] ?? '' }}</td>
        <td>{{ $m['pivot']['statut'] ?? '' }}</td>
      </tr>
    @empty
      <tr><td colspan="3">Aucun membre chargé (places approved).</td></tr>
    @endforelse
  </tbody>
</table>

<h2>Valider un user_id pending</h2>
<form class="row" method="post" action="{{ route('admin.tontines.adhere', $tontine['id']) }}">
  @csrf
  <input name="user_id" type="number" placeholder="user_id" required>
  <button class="btn" type="submit">POST adhere_user</button>
</form>
@endsection
