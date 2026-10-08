@extends('admin.layout')
@section('title', 'Adhésions')
@section('content')
<h1>Demandes en attente</h1>
<p class="hint">Endpoint : <code>GET /api/admin/adhesions/pending</code> — review <code>POST /api/admin/adhesions/{id}/review</code></p>
<table>
  <thead>
    <tr><th>Membre</th><th>Tontine</th><th>Visibilité</th><th></th></tr>
  </thead>
  <tbody>
    @forelse($adhesions as $a)
      <tr>
        <td>
          <a href="{{ route('admin.users.show', $a['user']['id'] ?? 0) }}">{{ $a['user']['prenom'] ?? '' }} {{ $a['user']['nom'] ?? '' }}</a>
          <br><small>{{ $a['user']['phone'] ?? '' }}</small>
        </td>
        <td>
          <a href="{{ route('admin.tontines.show', $a['tontine']['id'] ?? 0) }}">{{ $a['tontine']['nom'] ?? '' }}</a>
        </td>
        <td>{{ $a['tontine']['visibilite'] ?? '' }}</td>
        <td>
          <form class="row" method="post" action="{{ route('admin.adhesions.review', $a['id']) }}">
            @csrf
            <input type="hidden" name="action" value="approve">
            <button class="btn" type="submit">Approuver</button>
          </form>
          <form class="row" method="post" action="{{ route('admin.adhesions.review', $a['id']) }}" style="margin-top:.3rem;">
            @csrf
            <input type="hidden" name="action" value="reject">
            <input name="motif" placeholder="motif de refus" required>
            <button class="btn err" type="submit">Refuser</button>
          </form>
        </td>
      </tr>
    @empty
      <tr><td colspan="4">Aucune demande.</td></tr>
    @endforelse
  </tbody>
</table>
@endsection
