<?php
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

$mensaje = ""; // Variable para almacenar el mensaje

// Verifica si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mysqli_real_escape_string($enlace, $_POST['email']);
    
    // Verificar si el correo ya está registrado
    $check_email_query = $enlace->prepare("SELECT * FROM usuario WHERE email = ?");
    $check_email_query->bind_param("s", $email);
    $check_email_query->execute();
    $result = $check_email_query->get_result();
    
    if ($result->num_rows > 0) {
        $mensaje = "El correo electrónico ya está registrado.";
    } else {
        // Obtener datos del formulario
        $nombre = mysqli_real_escape_string($enlace, $_POST['nombre']);
        $contrasena = mysqli_real_escape_string($enlace, $_POST['contraseña']);
        $telefono = mysqli_real_escape_string($enlace, $_POST['telefono']);
        $rol = mysqli_real_escape_string($enlace, $_POST['rol']);
        $contrasena_admin = isset($_POST['contrasena_admin']) ? mysqli_real_escape_string($enlace, $_POST['contrasena_admin']) : '';

        // Validar la contraseña de administrador solo si se seleccionó el rol "admin"
        if ($rol === "admin" && $contrasena_admin !== "admin") {
            $mensaje = "Contraseña incorrecta para el rol de administrador.";
        } else {
            // Insertar datos en la base de datos
            $query = $enlace->prepare("INSERT INTO usuario (nombre, email, contraseña, telefono, rol) VALUES (?, ?, ?, ?, ?)");
            $query->bind_param("sssss", $nombre, $email, $contrasena, $telefono, $rol);

            if ($query->execute()) {
                $_SESSION['id'] = $enlace->insert_id; // Guardar el ID del usuario
                $_SESSION['nombre'] = $nombre;
                $_SESSION['rol'] = $rol; // Guardar el rol
                header("Location: index.php"); // Redirigir a index.php
                exit();
            } else {
                $mensaje = "Error al crear la cuenta: " . $enlace->error;
            }
        }
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
    <script>
        // Muestra el campo de contraseña de administrador si se selecciona "Administrador"
        function toggleAdminPasswordField() {
            const rol = document.getElementById("rol").value;
            const adminPasswordField = document.getElementById("admin-password-field");
            if (rol === "admin") {
                adminPasswordField.style.display = "block";
            } else {
                adminPasswordField.style.display = "none";
            }
        }
    </script>
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
        </ul>
    </nav>
</header>

<main>
    <section class="form-section">
        <h2>Crear Cuenta</h2>
        
        <!-- Mostrar mensaje de error si existe -->
        <?php if (!empty($mensaje)): ?>
            <div class="mensaje-error"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <form action="crearCuenta.php" method="POST">
            <label for="nombre">Nombre Completo:</label>
            <input type="text" id="nombre" name="nombre" required><br>

            <label for="email">Correo Electrónico:</label>
            <input type="email" id="email" name="email" required><br>

            <label for="contrasena">Contraseña:</label>
            <input type="password" id="contrasena" name="contraseña" required><br>

            <label for="telefono">Teléfono:</label>
            <input type="text" id="telefono" name="telefono" required><br>

            <label for="rol">Rol:</label>
            <select id="rol" name="rol" required onchange="toggleAdminPasswordField()">
                <option value="usuario">Usuario</option>
                <option value="admin">Administrador</option>
            </select><br>

            <div id="admin-password-field">
                <label for="contrasena_admin">Contraseña Administrador:</label>
                <input type="password" id="contrasena_admin" name="contrasena_admin"><br>
            </div>

            <button type="submit">Crear Cuenta</button>
        </form>
    </section>
</main>
</body>
</html>