@extends('admin.layout')
@section('title', 'Mutuelles')
@section('content')
<h1>Mutuelles</h1>
<p class="hint">Endpoint : <code>GET /api/admin/mutuelles</code></p>
<table>
  <thead>
    <tr><th>Nom</th><th>Créateur</th><th>Statut</th><th>Fonds</th><th>Taux</th></tr>
  </thead>
  <tbody>
    @forelse($mutuelles as $m)
      <tr>
        <td>{{ $m['nom'] ?? '' }}</td>
        <td>
          @if(!empty($m['createur']['id']))
            <a href="{{ route('admin.users.show', $m['createur']['id']) }}">{{ $m['createur']['prenom'] ?? '' }} {{ $m['createur']['nom'] ?? '' }}</a>
          @endif
        </td>
        <td>{{ $m['statut'] ?? '' }}</td>
        <td>{{ number_format((float) ($m['fonds_credite'] ?? 0), 0, ',', ' ') }}</td>
        <td>{{ $m['taux_interet'] ?? 0 }}%</td>
      </tr>
    @empty
      <tr><td colspan="5">Aucune mutuelle.</td></tr>
    @endforelse
  </tbody>
</table>
@endsection
