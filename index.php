<?php
    session_start();
    $usuario_id = isset($_SESSION['id']) ? $_SESSION['id'] : null;
    $usuario_nombre = isset($_SESSION['nombre']) ? $_SESSION['nombre'] : null;
    $usuario_rol = isset($_SESSION['rol']) ? $_SESSION['rol'] : null;
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
                <a href="#"><img src="img/logo.png" alt="Logo" class="logo-img"></a>
            </div>
            <ul class="nav-links">
                <li><a href="#inicio">Inicio</a></li>
                <li><a href="mapaDenuncias.php?rol=<?php echo urlencode($usuario_rol); ?>">Mapa de Denuncias</a></li>
                <li><a href="contacto.php">Contacto</a></li>
            </ul>
            <div class="user-actions">
                <div class="nav-buttons">
                    <?php if ($usuario_nombre): ?>
                        <p>Bienvenido, <?php echo $usuario_nombre; ?>!</p>
                        <a href="mostrarUsuario.php?id=<?php echo $usuario_id; ?>">
                            <img src="img/tuerca.png" alt="Ajustes" class="settings-icon">
                        </a>
                    <?php else: ?>
                        <button type="button" onclick="window.location.href='crearCuenta.php'" class="btn-crear-cuenta">Crear usuario</button>
                        <button onclick="window.location.href='iniciarSesion.php'" class="btn-iniciar-sesion">Iniciar sesión</button>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

    <main>
        <section class="hero">
            <div class="hero-overlay">
                <div class="hero-text">
                    <h1>Nosotros vamos de k'uchu a k'uchu</h1>
                    <p class="value-proposition">Tu ciudad más limpia, tu vida más feliz</p>
                    <?php if ($usuario_id): ?>
                        <a href="registroDenuncia.php?id=<?php echo $usuario_id; ?>" class="cta-button">Comienza ahora</a>
                    <?php else: ?>
                        <a href="iniciarSesion.php" class="cta-button">Comienza ahora</a>
                    <?php endif; ?>
                </div>
            </div>
        </section>


        <section class="services-section">
            <h2>¿EN QUÉ PODEMOS AYUDAR AL USUARIO?</h2>
            <div class="services-grid">
                <div class="service-card">
                    <h3>Mapa interactivo</h3>
                    <img src="img/map.gif" alt="">
                    <p>Accede a un mapa interactivo donde puedes ver en tiempo real las denuncias y problemas reportados en tu ciudad. Así podrás conocer las áreas que necesitan atención urgente.</p>
                </div>
                <div class="service-card">
                    <h3>Reportes</h3>
                    <img src="img/den.gif" alt="">
                    <p>Realiza reportes de problemas o situaciones que necesiten ser atendidas por las autoridades. Puedes registrar incidencias directamente desde el sitio para que sean gestionadas.</p>
                </div>
                <div class="service-card">
                    <h3>Contacto</h3>
                    <img src="img/llam.gif" alt="">
                    <p>Si necesitas ayuda directa o deseas obtener más información, puedes ponerte en contacto con nosotros a través de diversos medios. Estamos aquí para ayudarte.</p>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <img src="img/Footer.png" alt="Imagen del footer">
    </footer>
</body>
</html>
