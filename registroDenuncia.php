<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: iniciarSesion.php");
    exit();
}

$usuario_id = $_SESSION['id'];
$usuario_nombre = isset($_SESSION['usuario_nombre']) ? $_SESSION['usuario_nombre'] : null;

function isInsideArea($lat, $lng) {
    $geojson = file_get_contents('export.geojson'); 
    $data = json_decode($geojson, true);

    foreach ($data['features'] as $feature) {
        $polygon = $feature['geometry']['coordinates'][0]; 
        if (pointInPolygon($lat, $lng, $polygon)) {
            return true;
        }
    }
    return false;
}

function pointInPolygon($lat, $lng, $polygon) {
    $inside = false;
    $x = $lng;
    $y = $lat;

    $n = count($polygon);
    for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
        $xi = $polygon[$i][0];
        $yi = $polygon[$i][1];
        $xj = $polygon[$j][0];
        $yj = $polygon[$j][1];

        $intersect = (($yi > $y) != ($yj > $y)) && ($x < ($xj - $xi) * ($y - $yi) / ($yj - $yi) + $xi);
        if ($intersect) $inside = !$inside;
    }
    return $inside;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conexion = new mysqli("localhost", "root", "", "denuncias"); 

    if ($conexion->connect_error) {
        die("Error en la conexión a la base de datos: " . $conexion->connect_error);
    }

    $titulo = $conexion->real_escape_string($_POST['titulo']);
    $descripcion = $conexion->real_escape_string($_POST['descripcion']);
    $ubicacion = $conexion->real_escape_string($_POST['ubicacion']);
    $categoria = $conexion->real_escape_string($_POST['categoria']);
    $estado = 'pendiente';
    $fecha_denuncia = date("Y-m-d");

    list($lat, $lng) = explode(',', $ubicacion);
    $lat = (float)trim($lat);
    $lng = (float)trim($lng);

    if (!isInsideArea($lat, $lng)) {
        echo "La ubicación seleccionada no está dentro de los límites del municipio de Cochabamba.";
        exit();
    }

    $evidencia = null;
    if (!empty($_FILES['imagen']['name'])) {
        $target_dir = "uploads/"; 
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true); 
        }

        $filename = basename($_FILES['imagen']['name']);
        $target_file = $target_dir . $filename;
        
        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $target_file)) {
            $evidencia = $conexion->real_escape_string($filename);
        } else {
            echo "Error al subir la imagen.";
            exit();
        }
    }

    $query = "INSERT INTO denuncia (id_usuario, titulo, descripción, fecha_denuncia, estado, categoria, ubicacion, evidencia)
              VALUES ('$usuario_id', '$titulo', '$descripcion', '$fecha_denuncia', '$estado', '$categoria', '$ubicacion', '$evidencia')";

    if ($conexion->query($query)) {
        header("Location: mapaDenuncias.php?id_usuario=$usuario_id");
        exit();
    } else {
        echo "Error al registrar la denuncia: " . $conexion->error;
    }

    $conexion->close();
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Denuncia</title>
    <link rel="stylesheet" href="stylesRegistrodenuncias.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">

</head>
<body>
    <main>
        <section>
            <h2>Registrar Denuncia</h2>
            <form action="" method="POST" id="form-denuncia" enctype="multipart/form-data">

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
        const map = L.map('mi_mapa').setView([-17.3935, -66.157], 14);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        let limitesCochabamba;

        fetch('export.geojson') 
            .then(response => {
                if (!response.ok) {
                    throw new Error("No se pudo cargar el archivo GeoJSON.");
                }
                return response.json();
            })
            .then(data => {
                limitesCochabamba = L.geoJSON(data, {
                    style: {
                        color: '#ff7800', 
                        weight: 2, 
                        fillColor: '#fffcf5', 
                        fillOpacity: 0.4 
                    }
                }).addTo(map);

                map.fitBounds(limitesCochabamba.getBounds()); 
            })
            .catch(error => console.error("Error al cargar el GeoJSON:", error));

        let selectedMarker; 

        function isInsideArea(lat, lng) {
            if (!limitesCochabamba) return false;
            const point = L.latLng(lat, lng);
            return limitesCochabamba.getLayers().some(layer => layer.getBounds().contains(point));
        }

        map.on('click', (event) => {
            const { lat, lng } = event.latlng;

            if (isInsideArea(lat, lng)) {
                if (selectedMarker) {
                    map.removeLayer(selectedMarker);
                }

                selectedMarker = L.marker([lat, lng], { draggable: true }).addTo(map)
                    .bindPopup('Ubicación seleccionada dentro de Cochabamba').openPopup();

                document.getElementById('ubicacion').value = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;

                selectedMarker.on('dragend', (e) => {
                    const { lat: newLat, lng: newLng } = e.target.getLatLng();
                    if (isInsideArea(newLat, newLng)) {
                        document.getElementById('ubicacion').value = `${newLat.toFixed(6)}, ${newLng.toFixed(6)}`;
                    }
                });
            } else {
                alert("La ubicación seleccionada no está dentro de los límites del municipio de Cochabamba.");
            }
        });
    </script>
</body>
</html>
