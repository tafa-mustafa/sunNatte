@extends('admin.layout')
@section('title', 'Utilisateurs')
@section('content')
<h1>Utilisateurs</h1>
<p class="hint">Endpoint : <code>GET /api/admin/users</code></p>
<table>
  <thead>
    <tr><th>Nom</th><th>Téléphone</th><th>E-mail</th><th>Statut</th><th></th></tr>
  </thead>
  <tbody>
    @foreach($users as $u)
      <tr>
        <td><a href="{{ route('admin.users.show', $u['id']) }}">{{ $u['prenom'] ?? '' }} {{ $u['nom'] ?? '' }}</a></td>
        <td>{{ $u['phone'] ?? '' }}</td>
        <td>{{ $u['email'] ?? '' }}</td>
        <td>{{ !empty($u['statut']) ? 'actif' : 'inactif' }}</td>
        <td>
          <form method="post" action="{{ route('admin.users.toggle', $u['id']) }}">
            @csrf
            <button class="btn" type="submit">{{ !empty($u['statut']) ? 'Désactiver' : 'Activer' }}</button>
          </form>
        </td>
      </tr>
    @endforeach
  </tbody>
</table>
@endsection
