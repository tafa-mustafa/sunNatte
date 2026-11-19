@extends('admin.layout')
@section('title', 'Tarifs')
@section('content')
<h1>Tarifs</h1>
<p class="hint">Endpoints : <code>GET /api/admin/tarifs</code> · POST / PUT / DELETE (admin)</p>
<form class="stack" method="post" action="{{ route('admin.tarifs.store') }}">
  @csrf
  <select name="type">
    <option value="cotisation">cotisation</option>
    <option value="retrait">retrait</option>
  </select>
  <input name="min" type="number" placeholder="Min">
  <input name="max" type="number" placeholder="Max">
  <input name="pourcentage" type="number" step="0.01" placeholder="% " required>
  <button class="btn" type="submit">Ajouter</button>
</form>
<table>
  <thead><tr><th>Type</th><th>Min</th><th>Max</th><th>%</th><th></th></tr></thead>
  <tbody>
    @foreach($tarifs as $tarif)
      <tr>
        <td>{{ $tarif['type'] ?? $tarif->type }}</td>
        <td>{{ $tarif['min'] ?? $tarif->min ?? '' }}</td>
        <td>{{ $tarif['max'] ?? $tarif->max ?? '' }}</td>
        <td>{{ $tarif['pourcentage'] ?? $tarif->pourcentage }}</td>
        <td>
          <form method="post" action="{{ route('admin.tarifs.destroy', $tarif['id'] ?? $tarif->id) }}">
            @csrf @method('DELETE')
            <button class="btn err" type="submit">Supprimer</button>
          </form>
        </td>
      </tr>
    @endforeach
  </tbody>
</table>
@endsection
