<?php
    session_start(); // Iniciar la sesión

    // Destruir todas las variables de sesión
    session_unset();

    // Destruir la sesión
    session_destroy();

    // Redirigir a la página de inicio de sesión o a otra página
    header("Location: index.html");
    exit();
?>