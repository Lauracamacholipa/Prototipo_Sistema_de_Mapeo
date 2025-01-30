<?php
    session_start();
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

    <div class="contact-info-container">
        <div class="contact-info-box">
            <h3>EMSA</h3>
            <p><strong>Cochabamba - Bolivia</strong></p>
        </div>
        <div class="contact-info-box">
            <h3>Teléfono de reclamo:</h3>
            <p><strong>4-492059, Interno: 18</strong></p>
        </div>
        <div class="contact-info-box">
            <h3>Teléfono directo de atención:</h3>
            <p><strong>4298241, FAX: 4298078</strong></p>
        </div>
    </div>

    <footer>
        <img src="img/Footer.png" alt="Imagen del footer">
    </footer>
</body>
</html>