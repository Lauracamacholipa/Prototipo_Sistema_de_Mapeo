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
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="logo">
                <a href="#">
                    <img src="img/logo.png" alt="Logo" class="logo-img">
                </a>
            </div>
            <ul class="nav-links">
                <li><a href="#inicio">Inicio</a></li>
                <li><a href="mapaDenuncias.php">Servicios</a></li>
                <li><a href="#nosotros">Nosotros</a></li>
                <li><a href="#contacto">Contacto</a></li>
            </ul>
            <div class="user-actions">
                <input type="text" placeholder="Buscar..." class="search-box">
                <button type="submit" class="search-button"></button>

                <?php if ($usuario_nombre): ?>
                    <div class="welcome-message">
                        <p>Bienvenido, <?php echo $usuario_nombre; ?>!</p>
                    </div>
                    <a href="mostrarUsuario.php?id=<?php echo $usuario_id; ?>">
                        <img src="img/tuerca.png" alt="Ajustes" class="settings-icon">
                    </a>
                <?php else: ?>
                    <button onclick="window.location.href='crearCuenta.php'" class="btn-crear-cuenta">Crear usuario</button>
                    <button onclick="window.location.href='iniciarSesion.php'" class="btn-iniciar-sesion">Iniciar sesión</button>
                <?php endif; ?>


            </div>
        </nav>
    </header>

    <main>
        <section class="hero">
            <h1>Nosotros vamos de k’uchu a k’uchu</h1>
            <a href="registroDenuncia.php?id=<?php echo $usuario_id; ?>">
                <button type="submit" class="cta-button">Comienza ahora</button>
            </a>

        </section>

        <section class="map-section">
            <h2>Mapeo de basura</h2>
            <div class="map-container">
                <img src="img/mapa-img.jpeg" alt="Mapa de basura" class="map-img">
            </div>
            <div class="map-search">
                <input type="text" placeholder="Buscar ubicación..." class="map-search-box">
            </div>
        </section>
    </main>
</body>
</html>
