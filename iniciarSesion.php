<?php
// Iniciar la sesión solo si no está ya iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario ya tiene sesión activa
if (isset($_SESSION['id'])) {
    $usuario_nombre = $_SESSION['nombre']; // Definir la variable si ya está logueado
    $usuario_id = $_SESSION['id'];
    $usuario_rol = $_SESSION['rol'];
} else {
    // Si no está logueado, la variable debe ser nula o no se define
    $usuario_nombre = null;
    $usuario_id = null;
    $usuario_rol = null;
}
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

    // Variable para almacenar el mensaje de error
    $mensaje_error = "";

    // Verifica si se ha enviado el formulario
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $email = $_POST['email'];
        $contrasena = $_POST['contrasena'];

        // Consulta para verificar el usuario en la base de datos
        $consulta = $enlace->prepare("SELECT id, nombre, rol FROM usuario WHERE email = ? AND contraseña = ?");
        $consulta->bind_param("ss", $email, $contrasena);
        $consulta->execute();
        $resultado = $consulta->get_result();

        if ($resultado->num_rows > 0) {
            // Inicio de sesión exitoso
            $usuario = $resultado->fetch_assoc();
            $_SESSION['id'] = $usuario['id'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['rol'] = $usuario['rol']; // Guarda el rol del usuario en la sesión
            header('Location: index.php');
            exit();
        } else {
            // Usuario o contraseña incorrecta
            $mensaje_error = "Correo electrónico o contraseña incorrectos.";
        }
    }

    mysqli_close($enlace);
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión</title>
    <link rel="stylesheet" href="styles-iniciarsesion.css">
</head>
<body>

    <main>
        <section class="login-form">
            <h1>Iniciar sesión</h1>

             <!-- Mostrar el mensaje de error si existe -->
             <?php if (!empty($mensaje_error)): ?>
                <div class="mensaje-error"><?php echo $mensaje_error; ?></div>
            <?php endif; ?>

            <form action="iniciarSesion.php" method="POST">
                <label for="email">Correo electrónico:</label>
                <input type="email" id="email" name="email" required>

                <label for="contrasena">Contraseña:</label>
                <input type="password" id="contrasena" name="contrasena" required>

                <!--<button type="submit" class="btn-iniciar-sesion">Iniciar sesión</button>-->
                <div class="button-group">
                    <button type="submit" class="btn-iniciar-sesion">Iniciar sesión</button>
                    <button type="button" class="btn-volver" onclick="window.location.href='index.php'">Volver</button>
                </div>
            </form>
        </section>
    </main>
</body>
</html>