<?php
// Iniciar la sesión
session_start();

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

// Verifica si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $contrasena = $_POST['contrasena'];

    // Consulta para verificar el usuario en la base de datos
    $consulta = $enlace->prepare("SELECT id, nombre FROM usuario WHERE email = ? AND contraseña = ?");
    $consulta->bind_param("ss", $email, $contrasena);
    $consulta->execute();
    $resultado = $consulta->get_result();

    if ($resultado->num_rows > 0) {
        // Inicio de sesión exitoso
        $usuario = $resultado->fetch_assoc();
        $_SESSION['id'] = $usuario['id'];
        $_SESSION['nombre'] = $usuario['nombre'];
        header('Location: index.php');
        exit();
    } else {
        // Usuario o contraseña incorrecta
        echo "<p>Correo electrónico o contraseña incorrectos.</p>";
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
    <title>Iniciar sesión</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="logo">
                <a href="index.php">
                    <img src="img/logo.png" alt="Logo" class="logo-img">
                </a>
            </div>
        </nav>
    </header>

    <main>
        <section class="login-form">
            <h1>Iniciar sesión</h1>
            <form action="iniciarSesion.php" method="POST">
                <label for="email">Correo electrónico:</label>
                <input type="email" id="email" name="email" required>

                <label for="contrasena">Contraseña:</label>
                <input type="password" id="contrasena" name="contrasena" required>

                <button type="submit" class="btn-iniciar-sesion">Iniciar sesión</button>
            </form>
        </section>
    </main>
</body>
</html>

