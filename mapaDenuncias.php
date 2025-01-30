<?php
session_start();

// Verificar si el usuario está logueado
$usuario_nombre = $_SESSION['nombre'] ?? null;
$usuario_id = $_SESSION['id'] ?? null;
$usuario_rol = $_SESSION['rol'] ?? null;

// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "denuncias");

if ($conexion->connect_error) {
    die("Error en la conexión a la base de datos: " . $conexion->connect_error);
}

$estado_filtrado = $_GET['estado'] ?? null;
$usuario_filtrado = $_GET['mis_denuncias'] ?? null;

// Obtener todas las denuncias de la base de datos
$query = "SELECT d.id_denuncia, d.titulo, d.descripción, d.ubicacion, d.categoria, d.estado, d.evidencia, d.fecha_denuncia, 
          u.id AS usuario_id, u.nombre AS usuario_nombre, u.email AS usuario_email, u.telefono AS usuario_telefono 
          FROM denuncia d 
          JOIN usuario u ON d.id_usuario = u.id";

// Filtrar por estado
if ($estado_filtrado && in_array($estado_filtrado, ['pendiente', 'en_proceso', 'resuelta', 'rechazada'])) {
    $query .= " WHERE d.estado = '$estado_filtrado'";
}

// Filtrar por denuncias del usuario logueado
if ($usuario_filtrado) {
    if ($estado_filtrado) {
        $query .= " AND d.id_usuario = $usuario_id";
    } else {
        $query .= " WHERE d.id_usuario = $usuario_id";
    }
}

$resultado = $conexion->query($query);

$denuncias = [];
if ($resultado && $resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        // Asegúrate de que el campo de evidencia tenga la ruta correcta
        if ($fila['evidencia']) {
            $fila['evidencia'] = 'uploads/' . $fila['evidencia'];
        }

        // Agregar la denuncia al arreglo
        $denuncias[] = $fila;
    }
}

// Definir los estados válidos
$estados_validos = ['pendiente', 'en_proceso', 'resuelta', 'rechazada'];

// Actualizar el estado de una denuncia (solo para admin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_denuncia'], $_POST['estado']) && $usuario_rol === 'admin') {
    $id_denuncia = intval($_POST['id_denuncia']);
    $nuevo_estado = trim($_POST['estado']); // Elimina espacios adicionales

    // Validar estado
    if (in_array($nuevo_estado, $estados_validos)) {
        $stmt = $conexion->prepare("UPDATE denuncia SET estado = ? WHERE id_denuncia = ?");
        $stmt->bind_param("si", $nuevo_estado, $id_denuncia);

        if ($stmt->execute()) {
            echo "<script>alert('Estado actualizado correctamente'); window.location.href='mapaDenuncias.php';</script>";
        } else {
            echo "<script>alert('Error al actualizar el estado');</script>";
        }

        $stmt->close();
    } else {
        echo "<script>alert('Estado inválido.');</script>";
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
                <li><a href="#mapaDenuncias">Mapa de Denuncias</a></li>
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

<div class="contenedor-principal">
    <div class="contenedor-mapa">
        <div id="mapa">Mapa aquí</div>
    </div>
    <div class="contenedor-detalle">
        <div class="filters">
            <form method="GET" action="mapaDenuncias.php">
                <div>
                    <label for="estado">Filtrar por estado:</label>
                    <select name="estado" id="estado">
                        <option value="">Todas</option>
                        <option value="pendiente" <?php echo $estado_filtrado === 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                        <option value="en_proceso" <?php echo $estado_filtrado === 'en_proceso' ? 'selected' : ''; ?>>En Proceso</option>
                        <option value="resuelta" <?php echo $estado_filtrado === 'resuelta' ? 'selected' : ''; ?>>Resuelta</option>
                        <option value="rechazada" <?php echo $estado_filtrado === 'rechazada' ? 'selected' : ''; ?>>Rechazada</option>
                    </select>
                </div>

                <?php if ($usuario_id): ?>
                    <div>
                        <label for="mis_denuncias">Mis Denuncias:</label>
                        <input type="checkbox" name="mis_denuncias" value="1" <?php echo $usuario_filtrado ? 'checked' : ''; ?>>
                    </div>
                <?php endif; ?>

                <button type="submit">Filtrar</button>
            </form>
        </div>

        <div id="detalle">Detalle de la denuncia aquí</div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
// Inicializar el mapa
const map = L.map('mapa').setView([-17.3935, -66.157], 14);
L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);

// Cargar el archivo GeoJSON y agregarlo al mapa
fetch('export.geojson')
    .then(response => response.json())
    .then(data => {
        // Agregar la capa GeoJSON con estilo personalizado
        L.geoJSON(data, {
            style: function () {
                return {
                    color: '#ff7800', // Color de los límites
                    weight: 2, // Grosor de la línea
                    fillColor: '#fffcf5', // Color de relleno
                    fillOpacity: 0.4 // Opacidad del relleno
                };
            }
        }).addTo(map);
    })
    .catch(error => {
        console.error('Error al cargar el archivo GeoJSON:', error);
    });

// Cargar denuncias desde PHP
const denuncias = <?php echo json_encode($denuncias); ?>;
const usuarioActual = <?php echo json_encode($usuario_id); ?>;
const isAdmin = <?php echo $usuario_rol === 'admin' ? 'true' : 'false'; ?>;

// Definir colores para cada estado
const estadoColores = {
    pendiente: 'orange',
    en_proceso: 'blue',
    resuelta: 'green',
    rechazada: 'red'
};

// Crear marcadores en el mapa con colores específicos
denuncias.forEach(denuncia => {
    const ubicacionValida = denuncia.ubicacion && denuncia.ubicacion.includes(',');

    if (ubicacionValida) {
        const [lat, lng] = denuncia.ubicacion.split(',').map(coord => parseFloat(coord.trim()));

        // Obtener el color basado en el estado
        const color = estadoColores[denuncia.estado] || 'blue'; // Por defecto, azul si no coincide

        // Crear un ícono personalizado para el marcador
        const iconoPersonalizado = L.divIcon({
            className: 'custom-marker',
            html: `<div style="background-color:${color}; width: 20px; height: 20px; border-radius: 50%; border: 2px solid white;"></div>`,
            iconSize: [20, 20],
            iconAnchor: [10, 10]
        });

        // Crear y agregar el marcador al mapa
        const marker = L.marker([lat, lng], { icon: iconoPersonalizado }).addTo(map);

        marker.on('click', () => {

            let botonEditar = '';
            console.log("Usuario Actual:", usuarioActual);
            console.log("ID Usuario Denuncia:", denuncia.usuario_id);


            if (denuncia.usuario_id == usuarioActual) {
                botonEditar = `<button class="boton-editar" onclick="window.location.href='editarDenuncia.php?id=${denuncia.id_denuncia}'">Editar</button>`;
                console.log("Botón de editar generado:", botonEditar);
            }

            document.getElementById('detalle').innerHTML = `
                <div class="detalle-container">
                    <h3>${denuncia.titulo}</h3>
                    <p><strong>Descripción:</strong> ${denuncia.descripción}</p>
                    <p><strong>Categoría:</strong> ${denuncia.categoria}</p>
                    <p><strong>Fecha:</strong> ${denuncia.fecha_denuncia}</p>
                    <p><strong>Estado:</strong> <span class="estado-${denuncia.estado}">${denuncia.estado}</span></p>
                    ${denuncia.evidencia ? `
                        <div class="evidencia-container">
                            <h4>Evidencia:</h4>
                            <img src="${denuncia.evidencia}" alt="Evidencia de denuncia">
                        </div>
                    ` : '<p><strong>Evidencia:</strong> No disponible.</p>'}
                    ${isAdmin ? `
                        <div class="usuario-info">
                            <p><strong>Usuario:</strong> ${denuncia.usuario_nombre}</p>
                            <p><strong>Email:</strong> ${denuncia.usuario_email}</p>
                            <p><strong>Teléfono:</strong> ${denuncia.usuario_telefono}</p>
                        </div>
                        <form class="estado-form" action="mapaDenuncias.php" method="POST">
                            <input type="hidden" name="id_denuncia" value="${denuncia.id_denuncia}">
                            <label for="estado">Actualizar Estado:</label>
                            <select name="estado" id="estado-select" required>
                                <option value="pendiente" ${denuncia.estado === 'pendiente' ? 'selected' : ''}>Pendiente</option>
                                <option value="en_proceso" ${denuncia.estado === 'en_proceso' ? 'selected' : ''}>En Proceso</option>
                                <option value="resuelta" ${denuncia.estado === 'resuelta' ? 'selected' : ''}>Resuelta</option>
                                <option value="rechazada" ${denuncia.estado === 'rechazada' ? 'selected' : ''}>Rechazada</option>
                            </select>
                            <button type="submit">Guardar</button>
                        </form>
                    ` : `
                        <p><strong>Usuario:</strong> ${denuncia.usuario_nombre}</p>
                    `}
                    ${botonEditar ? botonEditar : ''}
                </div>
            `;
        });
    }
});
</script>
</body>
</html>
