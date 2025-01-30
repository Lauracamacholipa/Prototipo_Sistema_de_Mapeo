<?php
session_start(); // Asegúrate de que este archivo conecta a tu BD

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['id'])) {
    header("Location: iniciarSesion.php");
    exit();
}

$servername = "localhost"; // O el servidor que estés usando
$username = "root";        // Tu usuario de la base de datos
$password = "";            // Tu contraseña de la base de datos (si tienes)
$dbname = "denuncias";     // El nombre de tu base de datos

// Crear la conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Comprobar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$id_usuario_sesion = $_SESSION['id'];
$rol_usuario = $_SESSION['rol']; // Se asume que se guarda el rol en la sesión

// Verificar si se ha recibido el ID de la denuncia por GET
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: mapaDenuncias.php");
    exit();
}

$id_denuncia = $_GET['id'];

// Obtener los datos de la denuncia
$query = "SELECT id_usuario, titulo, descripción, fecha_denuncia, categoria, ubicacion, evidencia FROM denuncia WHERE id_denuncia = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_denuncia);
$stmt->execute();
$result = $stmt->get_result();

// Verificar si existe la denuncia
if ($result->num_rows === 0) {
    echo "Denuncia no encontrada.";
    exit();
}

$denuncia = $result->fetch_assoc();

// Verificar que el usuario tiene permisos para editar (dueño o admin)
if ($denuncia['id_usuario'] != $id_usuario_sesion && $rol_usuario !== 'admin') {
    echo "No tienes permisos para editar esta denuncia.";
    exit();
}

// Si se envió el formulario de edición
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = $_POST['titulo'];
    $descripcion = $_POST['descripcion'];
    $categoria = $_POST['categoria'];
    $ubicacion = $_POST['ubicacion'];
    $evidencia = ($_FILES['evidencia']['name'] != '') ? $_FILES['evidencia']['name'] : $denuncia['evidencia'];


    if ($_FILES['evidencia']['name'] != '') {
        $evidencia = $_FILES['evidencia']['name'];
        // Mover el archivo a la carpeta de uploads
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $target_file = $target_dir . basename($_FILES['evidencia']['name']);
        move_uploaded_file($_FILES['evidencia']['tmp_name'], $target_file);
    } else {
        $evidencia = $denuncia['evidencia'];
    }

    $updateQuery = "UPDATE denuncia SET titulo=?, descripción=?, categoria=?, ubicacion=?, evidencia=? WHERE id_denuncia=?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("sssssi", $titulo, $descripcion, $categoria, $ubicacion, $evidencia, $id_denuncia);

    if ($stmt->execute()) {
        header("Location: mapaDenuncias.php");
        exit();
    } else {
        echo "Error al actualizar la denuncia.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Denuncia</title>
    <link rel="stylesheet" href="stylesRegistrodenuncias.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
</head>
<body>
    <main>
        <section>
            <h2>Editar Denuncia</h2>
            <form action="" method="POST" id="form-denuncia" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="titulo">Título de la denuncia:</label>
                    <input type="text" id="titulo" name="titulo" value="<?= htmlspecialchars($denuncia['titulo']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción del problema:</label>
                    <textarea id="descripcion" name="descripcion" rows="4" required><?= htmlspecialchars($denuncia['descripción']) ?></textarea>
                </div>

                <div class="form-group">
                    <label for="ubicacion">Ubicación:</label>
                    <input type="text" id="ubicacion" name="ubicacion" value="<?= htmlspecialchars($denuncia['ubicacion']) ?>" required>
                    <div id="mi_mapa"></div>
                </div>

                <div class="form-group">
                    <label for="categoria">Categoría del problema:</label>
                    <select id="categoria" name="categoria" required>
                        <option value="basura-desbordada" <?= $denuncia['categoria'] == 'basura-desbordada' ? 'selected' : '' ?>>Contenedor desbordado</option>
                        <option value="residuos-peligrosos" <?= $denuncia['categoria'] == 'residuos-peligrosos' ? 'selected' : '' ?>>Residuos peligrosos</option>
                        <option value="vertido-ilegal" <?= $denuncia['categoria'] == 'vertido-ilegal' ? 'selected' : '' ?>>Vertido ilegal</option>
                        <option value="otros" <?= $denuncia['categoria'] == 'otros' ? 'selected' : '' ?>>Otros</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="evidencia">Evidencia (opcional):</label>
                    <?php if ($denuncia['evidencia']): ?>
                        <img src="uploads/<?= htmlspecialchars($denuncia['evidencia']) ?>" alt="Evidencia" class="evidencia-img">
                    <?php endif; ?>
                    <input type="file" id="evidencia" name="evidencia" accept="image/*">
                    <input type="hidden" name="evidencia" value="<?= htmlspecialchars($denuncia['evidencia']) ?>">
                </div>

                <div class="form-actions">
                    <button type="submit" class="cta-button">Guardar Cambios</button>
                    <a href="mapaDenuncias.php">
                        <button type="button" class="cta-button secondary">Volver</button>
                    </a>
                </div>
            </form>
        </section>
    </main>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
    // Crear el mapa
    const map = L.map('mi_mapa').setView([<?= $denuncia['ubicacion'] ?>], 14);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    let selectedMarker; // Marcador seleccionado

    // Colocar un marcador en la ubicación
    const ubicacion = '<?= $denuncia['ubicacion'] ?>'.split(', ');
    selectedMarker = L.marker([parseFloat(ubicacion[0]), parseFloat(ubicacion[1])]).addTo(map)
        .bindPopup('Ubicación seleccionada').openPopup();

    // Evento click en el mapa
    map.on('click', (event) => {
        const { lat, lng } = event.latlng;

        selectedMarker.setLatLng([lat, lng]);
        document.getElementById('ubicacion').value = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
    });
    </script>
</body>
</html>
