<!DOCTYPE html>
<html>
<head>
    <title>Restablecer contraseña</title>
</head>
<body>
    <h1>Restablecer su contraseña</h1>
    <p>Haga clic en el siguiente enlace para restablecer su contraseña:</p>
    <a href="{{ url('password/reset', $token) }}">Restablecer contraseña</a>
</body>
</html>
