<?php
    session_start();

    // Verificar si el usuario ha iniciado sesión
    if (!isset($_SESSION['usuario'])) {
        header("Location: login.php");
        exit();
    }

    $username = $_SESSION['usuario'];

    // URL de Firebase Realtime Database
    $url = 'https://ingenieria-web-179ff-default-rtdb.firebaseio.com/usuarios/' . $username . '.json';

    // Iniciar comunicación cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Ejecutar la solicitud
    $response = curl_exec($ch);
    curl_close($ch);

    $usuarioData = null;
    if ($response !== false) {
        $data = json_decode($response, true);
        // Obtener datos específicos del usuario
        foreach ($data as $user) {
            if ($user['Usuario'] === $username) {
                $usuarioData = $user;
                break;
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página Principal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        .navbar-brand {
            font-weight: bold;
        }
        .container h1 {
            color: #343a40;
        }
        .card-title {
            font-size: 1.25rem;
        }
        .card-text {
            margin-bottom: 0.5rem;
        }
        .progress {
            height: 1.5rem;
        }
        .logout-btn {
            margin-left: auto;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg bg-primary navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"><?php echo htmlspecialchars($username); ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="pagina_principal.php">Inicio</a>
                    </li>
                </ul>
                <form class="d-flex" role="search">
                    <input class="form-control me-2" type="search" placeholder="Buscar cursos" aria-label="Buscar">
                    <button class="btn btn-outline-success" type="submit">Buscar</button>
                </form>
                <a href="cerrar_sesion.php" class="btn btn-outline-danger logout-btn">Cerrar Sesión</a>
            </div>
        </div>
    </nav>
    <div class="container mt-5">
        <h1 class="text-center">Plataforma de Aprendizaje</h1>
        <h2 class="mt-4">Progreso de Aprendizaje</h2>
        <div id="cursos-container" class="mt-3">
            <?php
                if ($usuarioData && isset($usuarioData['Cursos'])) {
                    foreach ($usuarioData['Cursos'] as $nombre_curso => $curso) {
                        $progreso_total = $curso['progreso_total'];
                        echo '<div class="card mt-3">
                                <div class="card-body">
                                    <h5 class="card-title">Curso: ' . htmlspecialchars($nombre_curso) . '</h5>
                                    <p class="card-text">Progreso: ' . htmlspecialchars($progreso_total) . '%</p>
                                    <div class="progress mb-3">
                                        <div class="progress-bar" role="progressbar" style="width: ' . htmlspecialchars($progreso_total) . '%;" aria-valuenow="' . htmlspecialchars($progreso_total) . '" aria-valuemin="0" aria-valuemax="100">' . htmlspecialchars($progreso_total) . '%</div>
                                    </div>
                                    <a href="curso_detalle.php?curso=' . urlencode($nombre_curso) . '" class="btn btn-primary">Ver Curso</a>
                                </div>
                              </div>';
                    }
                } else {
                    echo '<div class="alert alert-warning" role="alert">
                            No hay cursos registrados para este usuario.
                          </div>';
                }
            ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
</body>
</html>
