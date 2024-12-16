<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro Completado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f4f9;
            font-family: Arial, sans-serif;
        }
        .container {
            margin-top: 80px;
        }
        .btn-primary {
            background-color: #0069d9;
            border-color: #0069d9;
        }
        .alert {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card mx-auto shadow" style="max-width: 500px;">
            <div class="card-body">
                <?php
                    function sanitize_input($data) {
                        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
                    }

                    // Obtener datos
                    $nombre = sanitize_input($_POST['nombre']);
                    $apellido = sanitize_input($_POST['apellido']);
                    $edad = filter_input(INPUT_POST, 'edad', FILTER_SANITIZE_NUMBER_INT);
                    $ciudad = sanitize_input($_POST['ciudad']);
                    $celular = sanitize_input($_POST['celular']);
                    $usuario = sanitize_input($_POST['usuario']);
                    $password = sanitize_input($_POST['password']);
                    $cursos = isset($_POST['cursos']) ? $_POST['cursos'] : [];

                    // Crear vector de almacenamiento en Firebase
                    $data_usuario = [
                        "Usuario" => $usuario,
                        "Password" => $password,
                        "Nombre" => $nombre,
                        "Apellido" => $apellido,
                        "Celular" => $celular,
                        "Edad" => $edad,
                        "Ciudad" => $ciudad,
                        "Cursos" => []
                    ];

                    foreach ($cursos as $curso) {
                        $data_usuario["Cursos"][$curso] = [
                            "lecciones" => [],
                            "progreso_total" => 0
                        ];
                    }

                    function send_to_firebase($url, $data) {
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                        $response = curl_exec($ch);

                        if (curl_errno($ch)) {
                            echo '<div class="alert alert-danger text-center" role="alert">
                                    Error al enviar los datos: ' . curl_error($ch) . '
                                  </div>';
                        } else {
                            echo '<div class="alert alert-success text-center" role="alert">
                                    ¡Datos insertados exitosamente!
                                  </div>';
                        }

                        curl_close($ch);
                        return $response;
                    }

                    $url_usuario = 'https://ingenieria-web-179ff-default-rtdb.firebaseio.com/usuarios/'.$usuario.'.json';
                    send_to_firebase($url_usuario, $data_usuario);
                ?>
                <div class="d-flex justify-content-center mt-4">
                    <a href="index.html" class="btn btn-primary btn-lg">Volver a la página principal</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>