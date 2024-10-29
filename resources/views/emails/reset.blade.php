<!DOCTYPE html>
<html>
<head>
    <title>Restablecer Contraseña</title>
</head>
<body>
    <h1>Hola {{ $user->name }},</h1>

    <p>Has solicitado restablecer tu contraseña. Para continuar, haz clic en el siguiente enlace:</p>

    <p>
        <a href="{{ url('password/reset', $token) }}">Restablecer Contraseña</a>
    </p>

    <p>Si no solicitaste este cambio, ignora este correo.</p>

    <p>Saludos,<br>
    Tu Equipo de Soporte</p>
</body>
</html>
