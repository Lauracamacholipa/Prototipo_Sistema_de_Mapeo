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
                <li><a href="index.php">Inicio</a></li>
                <li><a href="formulario.html">Servicios</a></li>
                <li><a href="#nosotros">Nosotros</a></li>
                <li><a href="#contacto">Contacto</a></li>
            </ul>
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
