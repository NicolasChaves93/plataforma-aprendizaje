<?php
    session_start();

    // Verificar si el usuario ha iniciado sesión
    if (!isset($_SESSION['usuario'])) {
        header("Location: login.php");
        exit();
    }

    $username = $_SESSION['usuario'];
    $curso = htmlspecialchars($_GET['curso']);

    // URL de Firebase Realtime Database
    $url = 'https://ingenieria-web-179ff-default-rtdb.firebaseio.com/usuarios/'.$username.'.json';

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
        foreach ($data as $userKey => $user) {
            if ($user['Usuario'] === $username) {
                $usuarioKey = $userKey;
                $usuarioData = $user;
                break;
            }
        }
    }

    // Obtener comentarios del foro
    $urlForo = 'https://ingenieria-web-179ff-default-rtdb.firebaseio.com/foros/'.$curso.'.json';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $urlForo);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $responseForo = curl_exec($ch);
    curl_close($ch);

    $comentarios = null;
    if ($responseForo !== false) {
        $comentarios = json_decode($responseForo, true);
    }

    // Datos de ejemplo de lecciones y ejercicios
    $lecciones = [
        ["titulo" => "Lección 1: Introducción", "tipo" => "Teórica"],
        ["titulo" => "Lección 2: Conceptos Básicos", "tipo" => "Teórica"],
        ["titulo" => "Ejercicio 1: Primer Ejercicio", "tipo" => "Práctico"],
        ["titulo" => "Ejercicio 2: Segundo Ejercicio", "tipo" => "Práctico"]
    ];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del Curso</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            margin-top: 20px;
        }
        .comment-section {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2>Detalle del Curso</h2>
        <!-- Botón para volver a la página principal -->
        <a href="pagina_principal.php" class="btn btn-secondary mb-4">Volver a la Página Principal</a>
        
        <div id="lecciones-container" class="mt-3">
            <?php
                echo '<h3>Curso: ' . $curso . '</h3>';

                foreach ($lecciones as $leccion) {
                    $titulo = $leccion['titulo'];
                    $progreso = 0;
                    if (isset($usuarioData['Cursos'][$curso]['lecciones'][$titulo])) {
                        $progreso = $usuarioData['Cursos'][$curso]['lecciones'][$titulo]['progreso'];
                    }
                    $completado = $progreso == 100 ? 'Completado' : 'Marcar como Completado';

                    echo '<div class="card">
                            <div class="card-body">
                                <h5 class="card-title">' . $titulo . '</h5>
                                <p class="card-text">Tipo: ' . $leccion['tipo'] . '</p>
                                <button class="btn btn-primary" onclick="guardarProgreso(\'' . $titulo . '\')" ' . ($progreso == 100 ? 'disabled' : '') . '>' . $completado . '</button>
                            </div>
                          </div>';
                }
            ?>
        </div>
        <h2 class="mt-5">Foro de Discusión</h2>
        <div id="foro-container" class="mt-3">
            <!-- Sección para mostrar y agregar preguntas/respuestas del foro -->
            <div class="comment-section">
                <?php
                    if ($comentarios) {
                        foreach ($comentarios as $comentario) {
                            echo '<div class="card mt-3">
                                    <div class="card-body">
                                        <h5 class="card-title">' . htmlspecialchars($comentario['usuario']) . '</h5>
                                        <p class="card-text">' . htmlspecialchars($comentario['mensaje']) . '</p>
                                    </div>
                                  </div>';
                        }
                    } else {
                        echo '<p>No hay comentarios para este curso.</p>';
                    }
                ?>
            </div>
            <div class="form-group mt-3">
                <label for="comentario">Agregar Comentario:</label>
                <textarea class="form-control" id="comentario" rows="3"></textarea>
                <button class="btn btn-primary mt-2" onclick="agregarComentario()">Enviar</button>
            </div>
        </div>
    </div>

    <script>
        function guardarProgreso(leccion) {
            const userId = "<?php echo $username; ?>";
            const curso = "<?php echo $curso; ?>";
            const usuarioKey = "<?php echo $usuarioKey; ?>";

            const data = {
                progreso: 100
            };

            const url = `https://ingenieria-web-179ff-default-rtdb.firebaseio.com/usuarios/${userId}/${usuarioKey}/Cursos/${curso}/lecciones/${leccion}.json`;

            fetch(url, {
                method: 'PATCH',
                body: JSON.stringify(data),
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                actualizarProgresoTotal(userId, usuarioKey, curso);
            })
            .catch(error => {
                console.error('Error al guardar el progreso:', error);
            });
        }

        function actualizarProgresoTotal(userId, usuarioKey, curso) {
            const url = `https://ingenieria-web-179ff-default-rtdb.firebaseio.com/usuarios/${userId}/${usuarioKey}/Cursos/${curso}/lecciones.json`;
            fetch(url)
            .then(response => response.json())
            .then(lecciones => {
                const leccionesGlobales = <?php echo json_encode($lecciones); ?>;
                let totalLecciones = leccionesGlobales.length;
                let leccionesCompletadas = 0;

                for (let leccion in lecciones) {
                    if (lecciones[leccion].progreso == 100) {
                        leccionesCompletadas++;
                    }
                }

                const progresoTotal = (leccionesCompletadas / totalLecciones) * 100;

                const updateUrl = `https://ingenieria-web-179ff-default-rtdb.firebaseio.com/usuarios/${userId}/${usuarioKey}/Cursos/${curso}.json`;
                const data = {
                    progreso_total: progresoTotal
                };

                return fetch(updateUrl, {
                    method: 'PATCH',
                    body: JSON.stringify(data),
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });
            })
            .then(() => {
                alert('Progreso guardado correctamente.');
                location.reload();
            })
            .catch(error => {
                console.error('Error al actualizar el progreso total:', error);
            });
        }

        function agregarComentario() {
            const userId = "<?php echo $username; ?>";
            const curso = "<?php echo $curso; ?>";
            const comentario = document.getElementById('comentario').value;

            if (comentario.trim() === '') {
                alert('El comentario no puede estar vacío.');
                return;
            }

            const data = {
                usuario: userId,
                mensaje: comentario
            };

            const url = `https://ingenieria-web-179ff-default-rtdb.firebaseio.com/foros/${curso}.json`;

            fetch(url, {
                method: 'POST',
                body: JSON.stringify(data),
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(() => {
                alert('Comentario agregado correctamente.');
                location.reload();
            })
            .catch(error => {
                console.error('Error al agregar el comentario:', error);
            });
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
</body>
</html>
