<?php
    session_start();  

    if (!isset($_SESSION['id'])) {
        header("Location: iniciarSesion.php");
        exit();
    }

    $id_usuario = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int)$_GET['id'] : (int)$_SESSION['id'];

    $host = 'localhost';
    $dbname = 'denuncias';
    $username = 'root';
    $password = '';

    $enlace = mysqli_connect($host, $username, $password, $dbname);

    if (!$enlace) {
        die("Conexión fallida: " . mysqli_connect_error());
    }

    $stmt = $enlace->prepare("SELECT nombre, email, telefono FROM usuario WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $nombre = htmlspecialchars($row['nombre']);  
            $email = htmlspecialchars($row['email']);
            $telefono = htmlspecialchars($row['telefono']);
        } else {
            echo "Usuario no encontrado.";
            exit();
        }

        $stmt->close();
    } else {
        echo "Error en la preparación de la consulta.";
        exit();
    }

    mysqli_close($enlace);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Usuario</title>
    <link rel="stylesheet" href="stylesMostrarUsuario.css">
</head>
<body>
    <div class="container">
        <h1>Perfil de Usuario</h1>
        <h2>Información Personal</h2>

        <div class="info">
            <p><strong>Nombre:</strong> <?php echo $nombre; ?></p>
            <p><strong>Email:</strong> <?php echo $email; ?></p>
            <p><strong>Teléfono:</strong> <?php echo $telefono; ?></p>
        </div>

        <div class="buttons-container">
            <a href="index.php" class="cta-button">Volver al Inicio</a>
            <form action="" method="POST">
                <button type="submit" name="cerrar_sesion" class="cta-button cta-button-logout">Cerrar sesión</button>
            </form>
        </div>

        <?php
            if (isset($_POST['cerrar_sesion'])) {
                session_unset();
                session_destroy();

                header("Location: index.php");
                exit();
            }
        ?>
    </div>
</body>
</html>