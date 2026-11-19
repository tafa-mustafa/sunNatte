@extends('admin.layout')
@section('title', 'Tableau de bord')
@section('content')
<h1>Tableau de bord</h1>
<div class="cards">
  @foreach($cards as $card)
    <a class="card" href="{{ $card['url'] }}">
      <span>{{ $card['label'] }}</span>
      <strong>{{ $card['value'] }}</strong>
    </a>
  @endforeach
</div>
@endsection
