<!DOCTYPE html>
<html>
<head>
    <title>Restablecer Contraseña</title>
</head>
<body>
    <h1>Restablecer su contraseña</h1>
    <form action="{{ route('password.update') }}" method="POST">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <label for="password">Nueva Contraseña:</label>
        <input type="password" name="password" required>

        <label for="password_confirmation">Confirmar Nueva Contraseña:</label>
        <input type="password" name="password_confirmation" required>

        <button type="submit">Restablecer Contraseña</button>
    </form>
</body>
</html>
