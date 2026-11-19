@extends('admin.layout')
@section('title', 'Tontines')
@section('content')
<h1>Tontines</h1>
<p class="hint">Endpoints : <code>GET /api/admin/tontines/all</code> · <code>POST /api/admin/tontines/create</code></p>
<form class="stack" method="post" action="{{ route('admin.tontines.store') }}">
  @csrf
  <strong>Nouvelle tontine catalogue</strong>
  <input name="nom" placeholder="Nom" required>
  <input name="nombre_personne" type="number" min="2" placeholder="Places" required>
  <input name="montant" type="number" placeholder="Montant FCFA">
  <input name="duree" type="number" placeholder="Durée (mois)">
  <input name="type" placeholder="Type (argent / materiel)" value="argent">
  <select name="visibilite">
    <option value="catalogue">Catalogue</option>
    <option value="privee">Privée</option>
  </select>
  <button class="btn" type="submit">Créer</button>
</form>
<table>
  <thead>
    <tr><th>Nom</th><th>Visibilité</th><th>Places</th><th>Pending</th><th>Statut</th><th></th></tr>
  </thead>
  <tbody>
    @foreach($tontines as $t)
      <tr>
        <td>
          <a href="{{ route('admin.tontines.show', $t['id']) }}">{{ $t['nom'] }}</a>
          <br><small>{{ $t['code_adhesion'] ?? '' }}</small>
        </td>
        <td>{{ $t['visibilite'] ?? '' }}</td>
        <td>{{ $t['approved_count'] ?? 0 }}/{{ $t['nombre_personne'] ?? 0 }}</td>
        <td>{{ $t['pending_count'] ?? 0 }}</td>
        <td>{{ !empty($t['statut']) ? 'ouverte' : 'fermée' }}</td>
        <td>
          <form method="post" action="{{ route('admin.tontines.toggle', $t['id']) }}">
            @csrf
            <button class="btn gold" type="submit">{{ !empty($t['statut']) ? 'Fermer' : 'Ouvrir' }}</button>
          </form>
        </td>
      </tr>
    @endforeach
  </tbody>
</table>
@endsection
