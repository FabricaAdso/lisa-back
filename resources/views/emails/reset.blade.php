<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7f9;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .email-container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background-color: #4CAF50;
            color: #fff;
            padding: 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 20px;
        }
        .content p {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 16px;
        }
        .ii a[href] {
            color: rgb(255, 255, 255);
        }
        .button-container {
            text-align: center;
        }
        .button {
            background-color: #4CAF50;
            color: #fff;
            font-size: 18px;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin-top: 20px;
            transition: background-color 0.3s ease;
        }
        .button:hover {
            background-color: #45a049;
        }
        .footer {
            text-align: center;
            font-size: 14px;
            color: #777;
            margin-top: 20px;
            padding: 10px;
        }
        .footer p {
            margin: 0;
        }
        .footer a {
            color: #4CAF50;
            text-decoration: none;
        }
        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="email-container">
        <div class="header">
            <h1>Restablecer Contraseña</h1>
        </div>

        <div class="content">
            <p>Hola,</p>
            <p>Hemos recibido una solicitud para restablecer tu contraseña. Si fuiste tú, por favor haz clic en el siguiente enlace para crear una nueva contraseña:</p>
            <div class="button-container">
                <a href="http://localhost:4200/password/reset/{{ $token }}" class="button">Restablecer Contraseña</a>
            </div>
            <p>Si no solicitaste este cambio, puedes ignorar este correo de forma segura.</p>
        </div>

        <div class="footer">
            <p>Saludos,<br>El Equipo de Soporte</p>
            <p>Si necesitas ayuda adicional, por favor <a href="mailto:pruebasfabricaccys@gmail.com">contáctanos</a>.</p>
        </div>
    </div>

</body>
</html>
