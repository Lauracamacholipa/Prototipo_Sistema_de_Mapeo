<?php
    // Iniciar la sesión
    session_start();
    // Verificar si el usuario está logueado
    $usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : null;
    $usuario_nombre = isset($_SESSION['nombre']) ? $_SESSION['nombre'] : null;

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Llajta limpia</title>
    <link rel="stylesheet" href="styles-contacto.css">
</head>
<body>
<header>
    <nav class="navbar">
        <div class="logo">
            <a href="index.php"><img src="img/logo.png" alt="Logo" class="logo-img"></a>
        </div>
        <ul class="nav-links">
            <li><a href="index.php" class="activo">Inicio</a></li>
            <li><a href="mapaDenuncias.php">Mapa de Denuncias</a></li>
            <li><a href="contacto.php">Contacto</a></li>
        </ul>
        <div class="user-actions">
            <?php if ($usuario_nombre): ?>
                <div class="user-info">
                    <p>Bienvenido, <?php echo $usuario_nombre; ?>!</p>
                    <a href="mostrarUsuario.php?id=<?php echo $usuario_id; ?>">
                        <img src="img/tuerca.png" alt="Ajustes" class="settings-icon">
                    </a>
                </div>
            <?php else: ?>
                <button onclick="window.location.href='crearCuenta.php'" class="btn-crear-cuenta">Crear Usuario</button>
                <button onclick="window.location.href='iniciarSesion.php'" class="btn-iniciar-sesion">Iniciar Sesión</button>
            <?php endif; ?>
        </div>
    </nav>
</header>

<div class="container">
    <h1>Contáctanos</h1>
    <p>Por favor, completa el formulario y nos pondremos en contacto contigo lo antes posible.</p>

    <form action="procesar-contacto.php" method="POST" class="formulario-contacto">
        <label for="nombre">Nombre:</label>
        <input type="text" id="nombre" name="nombre" placeholder="Tu nombre completo" required>

        <label for="email">Correo Electrónico:</label>
        <input type="email" id="email" name="email" placeholder="Tu correo electrónico" required>

        <label for="mensaje">Mensaje:</label>
        <textarea id="mensaje" name="mensaje" placeholder="Escribe tu mensaje aquí..." rows="6" required></textarea>

        <button type="submit" class="btn-enviar">Enviar</button>
    </form>
</body>
</html>