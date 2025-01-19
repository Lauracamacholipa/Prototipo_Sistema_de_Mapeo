<?php
// Iniciar la sesión
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['id'])) {
    // Si el usuario no está logueado, redirigir al login
    header("Location: iniciarSesion.php");
    exit();
}

// Obtener el usuario_id desde la sesión
$usuario_id = $_SESSION['id'];

// Verificar si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Conexión a la base de datos
    $conexion = new mysqli("localhost", "root", "", "denuncias"); // Ajusta los datos de conexión

    if ($conexion->connect_error) {
        die("Error en la conexión a la base de datos: " . $conexion->connect_error);
    }

    // Obtener datos del formulario
    $titulo = $conexion->real_escape_string($_POST['titulo']);
    $descripcion = $conexion->real_escape_string($_POST['descripcion']);
    $ubicacion = $conexion->real_escape_string($_POST['ubicacion']);
    $categoria = $conexion->real_escape_string($_POST['categoria']);
    $estado = 'pendiente';
    $fecha_denuncia = date("Y-m-d"); // Fecha actual

    // Manejo de la evidencia (imagen)
    $evidencia = null;
    if (!empty($_FILES['imagen']['name'])) {
        $target_dir = "uploads/"; // Carpeta donde se guardarán las imágenes
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true); // Crear carpeta si no existe
        }
        $target_file = $target_dir . basename($_FILES['imagen']['name']);
        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $target_file)) {
            $evidencia = $conexion->real_escape_string($target_file);
        } else {
            echo "Error al subir la imagen.";
            exit();
        }
    }

    // Insertar datos en la base de datos
    $query = "INSERT INTO denuncia (id_usuario, titulo, descripción, fecha_denuncia, estado, categoria, ubicacion, evidencia)
              VALUES ('$usuario_id', '$titulo', '$descripcion', '$fecha_denuncia', '$estado', '$categoria', '$ubicacion', '$evidencia')";

    if ($conexion->query($query)) {
        // Redirigir a la página mapaDenuncias.php con los datos de la denuncia
        header("Location: mapaDenuncias.php?id_usuario=$usuario_id");
        exit();
    } else {
        echo "Error al registrar la denuncia: " . $conexion->error;
    }

    // Cerrar la conexión
    $conexion->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Denuncia</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    <style>
        #mi_mapa {
            height: 400px;
            width: 100%;
            margin-top: 10px;
        }
        .readonly {
            background-color: #f5f5f5;
            border: none;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <main>
        <section>
            <h2>Registrar Denuncia</h2>
            <form action="" method="POST" id="form-denuncia" enctype="multipart/form-data">
                <!-- Campo oculto para el usuario_id -->
                <input type="hidden" name="usuario_id" value="<?php echo $usuario_id; ?>">

                <div class="form-group">
                    <label for="titulo">Título de la denuncia:</label>
                    <input type="text" id="titulo" name="titulo" placeholder="Título de la denuncia" required>
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción del problema:</label>
                    <textarea id="descripcion" name="descripcion" rows="4" placeholder="Describe el problema..." required></textarea>
                </div>

                <div class="form-group">
                    <label for="ubicacion">Seleccionar ubicación en el mapa:</label>
                    <div id="mi_mapa"></div>
                    <input type="text" id="ubicacion" name="ubicacion" class="readonly" readonly placeholder="Latitud, Longitud" required>
                </div>

                <div class="form-group">
                    <label for="categoria">Categoría del problema:</label>
                    <select id="categoria" name="categoria" required>
                        <option value="">Selecciona una categoría</option>
                        <option value="basura-desbordada">Contenedor desbordado</option>
                        <option value="residuos-peligrosos">Residuos peligrosos</option>
                        <option value="vertido-ilegal">Vertido ilegal</option>
                        <option value="otros">Otros</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="imagen">Subir Imagen (opcional):</label>
                    <input type="file" id="imagen" name="imagen" accept="image/*">
                </div>

                <div class="form-actions">
                    <button type="submit" class="cta-button">Aceptar</button>
                    <a href="index.php">
                        <button type="button" class="cta-button secondary">Volver</button>
                    </a>
                </div>
            </form>
        </section>
    </main>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        // Crear el mapa
        const map = L.map('mi_mapa').setView([-17.3935, -66.157], 14);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        let selectedMarker; // Marcador seleccionado

        // Agregar marcador en el clic del mapa
        map.on('click', (event) => {
            const { lat, lng } = event.latlng;

            // Remover marcador anterior
            if (selectedMarker) {
                map.removeLayer(selectedMarker);
            }

            // Agregar nuevo marcador
            selectedMarker = L.marker([lat, lng]).addTo(map)
                .bindPopup('Ubicación seleccionada').openPopup();

            // Actualizar campo de texto con las coordenadas
            document.getElementById('ubicacion').value = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
        });
    </script>
</body>
</html>
