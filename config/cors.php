

<?php

return [

/*
|--------------------------------------------------------------------------
| Laravel CORS Options
|-------------------------------------------------------------------------- 
|
| Here you may configure your settings for cross-origin resource sharing.
| You can configure a single origin or allow a list of origins.
|
*/

'paths' => ['api/*', 'broadcasting/auth'], // Especificamos las rutas a las que se debe aplicar CORS

'allowed_methods' => ['*'], // Permite todos los métodos HTTP

'allowed_origins' => ['*'], // Cambia '*' por el origen que necesites como 'http://127.0.0.1:8000'

'allowed_origins_patterns' => [],

'allowed_headers' => ['*'], // Permite todos los encabezados

'exposed_headers' => [],

'max_age' => 0,

'supports_credentials' => true, // Habilitar si necesitas permitir credenciales (como cookies o autenticación de sesión)

];
