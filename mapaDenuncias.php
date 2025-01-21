<?php
session_start(); // Asegúrate de iniciar la sesión para acceder a variables de sesión

$usuario_nombre = isset($_SESSION['nombre']) ? $_SESSION['nombre'] : null;
$usuario_id = isset($_SESSION['id']) ? $_SESSION['id'] : null;

// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "denuncias");

if ($conexion->connect_error) {
    die("Error en la conexión a la base de datos: " . $conexion->connect_error);
}

// Obtener todas las denuncias de la base de datos
$query = "SELECT id_denuncia, titulo, descripción, ubicacion, categoria, estado, evidencia FROM denuncia";
$resultado = $conexion->query($query);

$denuncias = [];
if ($resultado && $resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        $denuncias[] = $fila;
    }
}

// Cerrar la conexión
$conexion->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mapa de Denuncias</title>
    <link rel="stylesheet" href="styles-mapadenuncias.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">

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

    <div id="mapa"></div>
    <div id="detalle">
        <h3>Detalles de la Denuncia</h3>
        <p>Haz clic en una denuncia para ver más detalles aquí.</p>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        // Inicializar el mapa
        const map = L.map('mapa').setView([-17.3935, -66.157], 14);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // Lista de denuncias desde PHP
        const denuncias = <?php echo json_encode($denuncias); ?>;

        // Añadir marcadores al mapa
        denuncias.forEach(denuncia => {
            const [lat, lng] = denuncia.ubicacion.split(',').map(coord => parseFloat(coord.trim()));

            const marker = L.marker([lat, lng]).addTo(map);
            marker.on('click', () => {
                // Mostrar detalles de la denuncia en el panel lateral
                document.getElementById('detalle').innerHTML = `
                    <h3>${denuncia.titulo}</h3>
                    <p><strong>Categoría:</strong> ${denuncia.categoria}</p>
                    <p><strong>Estado:</strong> ${denuncia.estado}</p>
                    <p><strong>Descripción:</strong> ${denuncia.descripción}</p>
                    ${denuncia.evidencia ? `<img src="${denuncia.evidencia}" alt="Evidencia" style="width: 100%;">` : ''}
                `;
            });
        });
    </script>
</body>
</html>
