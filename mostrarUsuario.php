<?php
session_start();  // Iniciar sesión para verificar si el usuario está autenticado

// Verificar si el usuario está logueado
if (!isset($_SESSION['id'])) {
    // Redirigir si no está logueado
    header("Location: iniciarSesion.php");
    exit();
}

// Obtener el ID desde la URL
$id_usuario = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int)$_GET['id'] : (int)$_SESSION['id'];

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

// Consulta para obtener la información del usuario
$stmt = $enlace->prepare("SELECT nombre, email, telefono FROM usuario WHERE id = ?");
if ($stmt) {
    // Vincular el parámetro de la consulta (el id del usuario)
    $stmt->bind_param("i", $id_usuario);
    // Ejecutar la consulta
    $stmt->execute();
    // Obtener el resultado de la consulta
    $result = $stmt->get_result();

    // Verificar si el usuario existe
    if ($row = $result->fetch_assoc()) {
        $nombre = htmlspecialchars($row['nombre']);  // Escapar salida para evitar XSS
        $email = htmlspecialchars($row['email']);
        $telefono = htmlspecialchars($row['telefono']);
    } else {
        echo "Usuario no encontrado.";
        exit();
    }

    // Cerrar la declaración
    $stmt->close();
} else {
    echo "Error en la preparación de la consulta.";
    exit();
}

// Cerrar la conexión a la base de datos
mysqli_close($enlace);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Usuario</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Perfil de Usuario</h1>
    </header>
    <main>
        <section>
            <h2>Información Personal</h2>
            <p><strong>Nombre:</strong> <?php echo $nombre; ?></p>
            <p><strong>Email:</strong> <?php echo $email; ?></p>
            <p><strong>Teléfono:</strong> <?php echo $telefono; ?></p>
        </section>
        <a href="index.php">Volver al Inicio</a>
        <!-- Botón de Cerrar Sesión -->
        <form action="" method="POST">
            <button type="submit" name="cerrar_sesion">Cerrar sesión</button>
        </form>

        <?php
        // Verificar si se presionó el botón de cerrar sesión
        if (isset($_POST['cerrar_sesion'])) {
            // Destruir todas las variables de sesión
            session_unset();
            session_destroy();

            // Redirigir al inicio (index.php)
            header("Location: index.php");
            exit();
        }
        ?>
    </main>
</body>
</html>