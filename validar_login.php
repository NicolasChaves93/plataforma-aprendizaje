<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body {
            background-color: #f5f9fc;
            font-family: Arial, sans-serif;
        }
        .container {
            margin-top: 100px;
            max-width: 500px;
        }
        .card {
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card mx-auto">
            <h3 class="text-center mb-4">Iniciar Sesión</h3>
            <?php
                session_start();

                // Verificar si se enviaron los datos del formulario
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    // Recuperar datos del formulario
                    $usuario = htmlspecialchars($_POST['usuario'], ENT_QUOTES, 'UTF-8');
                    $password = htmlspecialchars($_POST['password'], ENT_QUOTES, 'UTF-8');

                    // URL de Firebase Realtime Database
                    $url = 'https://ingenieria-web-179ff-default-rtdb.firebaseio.com/usuarios/'.$usuario.'.json';

                    // Realizar solicitud a Firebase para obtener los datos
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

                    $response = curl_exec($ch);

                    if (curl_errno($ch)) {
                        die('Error al conectar con Firebase: ' . curl_error($ch));
                    }

                    curl_close($ch);

                    $data = json_decode($response, true); // Decodificar respuesta JSON

                    // Validar usuario y contraseña
                    $usuarioValido = false;
                    foreach ($data as $key => $user) {
                        if ($user['Usuario'] === $usuario && $user['Password'] === $password) {
                            $usuarioValido = true;
                            $_SESSION['usuario'] = $usuario; // Guardar el usuario en sesión
                            header("Location: pagina_principal.php"); // Redirigir a la página principal
                            exit();
                        }
                    }

                    // Si las credenciales no son válidas
                    if (!$usuarioValido) {
                        $_SESSION['error'] = 'Usuario o contraseña incorrectos.';
                        header("Location: login.php"); // Redirigir al formulario de inicio
                        exit();
                    }
                } else {
                    header("Location: login.php"); // Redirigir si se accede directamente
                    exit();
                }
                ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>