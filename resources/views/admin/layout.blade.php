<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'Admin') — Sunu Natt</title>
  <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body>
  <aside class="side">
    <a class="brand" href="{{ route('admin.dashboard') }}">Sunu Natt</a>
    <nav>
      <a href="{{ route('admin.dashboard') }}">Tableau de bord</a>
      <a href="{{ route('admin.adhesions') }}">Adhésions</a>
      <a href="{{ route('admin.tontines') }}">Tontines</a>
      <a href="{{ route('admin.users') }}">Utilisateurs</a>
      <a href="{{ route('admin.mutuelles') }}">Mutuelles</a>
      <a href="{{ route('admin.tarifs') }}">Tarifs</a>
    </nav>
    <form method="post" action="{{ route('admin.logout') }}">
      @csrf
      <button type="submit" class="linkish">Déconnexion</button>
    </form>
  </aside>
  <main class="main">
    @if(session('ok'))
      <p class="flash">{{ session('ok') }}</p>
    @endif
    @if($errors->any())
      <p class="flash err">{{ $errors->first() }}</p>
    @endif
    @yield('content')
  </main>
</body>
</html>
