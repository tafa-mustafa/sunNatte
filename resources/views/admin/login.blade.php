<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Connexion admin — Sunu Natt</title>
  <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body style="display:block;">
  <form class="login" method="post" action="{{ route('admin.login.submit') }}">
    @csrf
    <h1>Back-office Sunu Natt</h1>
    <p>E-mail ou téléphone administrateur</p>
    @if($errors->any())
      <p class="flash err">{{ $errors->first() }}</p>
    @endif
    <div class="stack" style="box-shadow:none;padding:0;max-width:none;">
      <input name="login" value="{{ old('login') }}" placeholder="e-mail ou téléphone" required>
      <input type="password" name="password" placeholder="mot de passe" required>
    </div>
    <button class="btn" type="submit">Connexion</button>
  </form>
</body>
</html>
