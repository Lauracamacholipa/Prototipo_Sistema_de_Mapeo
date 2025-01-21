<?php
    // Iniciar la sesión
    session_start();
    // Verificar si el usuario está logueado
    $usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : null;
    $usuario_nombre = isset($_SESSION['nombre']) ? $_SESSION['nombre'] : null;

?>
<?php
// Configuración de la base de datos
$host = 'localhost';
$dbname = 'denuncias';
$username = 'root';
$password = '';

// Conexión a la base de datos
$enlace = mysqli_connect($host, $username, $password, $dbname);

// Verifica si la conexión fue exitosa
if (!$enlace) {
    die("Conexión fallida: " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos del formulario
    $nombre = mysqli_real_escape_string($enlace, $_POST['nombre']);
    $email = mysqli_real_escape_string($enlace, $_POST['email']);
    $contrasena = mysqli_real_escape_string($enlace, $_POST['contraseña']); // Asegúrate de que coincida con el campo del formulario
    $telefono = mysqli_real_escape_string($enlace, $_POST['telefono']);

    // Consulta preparada
    $query = $enlace->prepare("INSERT INTO usuario (nombre, email, contraseña, telefono) VALUES (?, ?, ?, ?)");
    $query->bind_param("ssss", $nombre, $email, $contrasena, $telefono);
    if ($query->execute()) {
        // Redirigir a la página de inicio después de una inserción exitosa
        session_start();
        $_SESSION['id'] = $enlace->insert_id; // Guardar el ID del usuario creado
        $_SESSION['nombre'] = $nombre;
        header("Location: index.php");
        exit();
    } else {
        echo "Error al crear la cuenta: " . $enlace->error;
    }
}

// Cerrar la conexión
mysqli_close($enlace);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta</title>
    <link rel="stylesheet" href="styles-crearcuenta.css">
</head>
<body>
<header>
        <nav class="navbar">
            <div class="logo">
                <a href="#"><img src="img/logo.png" alt="Logo" class="logo-img"></a>
            </div>
            <ul class="nav-links">
                <li><a href="index.php">Inicio</a></li>
                <li><a href="mapaDenuncias.php">Mapa de denuncias</a></li>
                <li><a href="contacto.php">Contacto</a></li>
                <li><a href="#Comentarios">Comentarios</a></li>
            </ul>
            <div class="user-actions">
                <div class="nav-buttons">
                    <?php if ($usuario_nombre): ?>
                        <p>Bienvenido, <?php echo $usuario_nombre; ?>!</p>
                        <a href="mostrarUsuario.php?id=<?php echo $usuario_id; ?>">
                            <img src="img/tuerca.png" alt="Ajustes" class="settings-icon">
                        </a>
                    <?php else: ?>
                        <button onclick="window.location.href='crearCuenta.php'" class="btn-crear-cuenta">Crear usuario</button>
                        <button onclick="window.location.href='iniciarSesion.php'" class="btn-iniciar-sesion">Iniciar sesión</button>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

    <main>
        <section class="form-section">
            <h2>Crear Cuenta</h2>
            <form action="crearCuenta.php" method="POST">
                <label for="nombre">Nombre Completo:</label>
                <input type="text" id="nombre" name="nombre" required><br>

                <label for="email">Correo Electrónico:</label>
                <input type="email" id="email" name="email" required><br>

                <label for="contrasena">Contraseña:</label>
                <input type="password" id="contrasena" name="contraseña" required><br>


                <label for="telefono">Teléfono:</label>
                <input type="text" id="telefono" name="telefono" required><br>

                <button type="submit">Crear Cuenta</button>
            </form>
        </section>
    </main>
</body>
</html>
