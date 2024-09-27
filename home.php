<?php
session_start();
$userId = $_SESSION['user_id']; // Asegúrate de que la sesión tiene 'user_id'

include_once("conexion.php");
$conexion = new Conexion();
$db = $conexion->getConexion();

// Inicializar variables
$numeroOrden = isset($_GET['numeroOrden']) ? $_GET['numeroOrden'] : '';
$fechaInicio = isset($_GET['fechaInicio']) ? $_GET['fechaInicio'] : '';
$fechaFin = isset($_GET['fechaFin']) ? $_GET['fechaFin'] : '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$itemsPerPage = 10;

// Obtener el nombre del paciente desde la tabla gen_m_persona usando fac_m_tarjetero
$patientQuery = "SELECT p.nombre1, p.apellido1 
                 FROM gen_m_persona p
                 INNER JOIN fac_m_tarjetero t ON t.id_persona = p.id 
                 WHERE p.id = :userId";
$patientStmt = $db->prepare($patientQuery);
$patientStmt->execute([':userId' => $userId]);
$patientResult = $patientStmt->fetch(PDO::FETCH_ASSOC);

// Verificar si el paciente fue encontrado
if ($patientResult) {
    $patientName = $patientResult['nombre1'] . ' ' . $patientResult['apellido1'];
} else {
    echo "No se encontró al paciente.";
    exit;
}

// Construir la consulta base para las órdenes de laboratorio relacionadas con el paciente
$query = "SELECT fecha, id_documento, orden 
          FROM lab_m_orden 
          WHERE id_historia IN (
              SELECT t.id 
              FROM fac_m_tarjetero t
              WHERE t.id_persona = :userId
          )";
$params = [':userId' => $userId];

$countQuery = "SELECT COUNT(*) 
               FROM lab_m_orden 
               WHERE id_historia IN (
                   SELECT t.id 
                   FROM fac_m_tarjetero t
                   WHERE t.id_persona = :userId
               )";
$countParams = [':userId' => $userId];

// Agregar filtros si se proporcionan
if (!empty($numeroOrden)) {
    $query .= " AND orden = :numeroOrden";
    $countQuery .= " AND orden = :numeroOrden";
    $params[':numeroOrden'] = $numeroOrden;
    $countParams[':numeroOrden'] = $numeroOrden;
}

if (!empty($fechaInicio) && !empty($fechaFin)) {
    $query .= " AND fecha BETWEEN :fechaInicio AND :fechaFin";
    $countQuery .= " AND fecha BETWEEN :fechaInicio AND :fechaFin";
    $params[':fechaInicio'] = $fechaInicio;
    $params[':fechaFin'] = $fechaFin;
    $countParams[':fechaInicio'] = $fechaInicio;
    $countParams[':fechaFin'] = $fechaFin;
}

// Agregar ordenamiento y paginación
$offset = ($itemsPerPage * ($page - 1));
$query .= " ORDER BY fecha DESC LIMIT :itemsPerPage OFFSET :offset";
$params[':itemsPerPage'] = $itemsPerPage;
$params[':offset'] = $offset;

// Ejecutar las consultas
$stmt = $db->prepare($query);
$stmt->bindValue(':itemsPerPage', $itemsPerPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute($params);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

$countStmt = $db->prepare($countQuery);
$countStmt->execute($countParams);
$totalItems = $countStmt->fetchColumn();
$totalPages = ceil($totalItems / $itemsPerPage);

if (!$result) {
    echo "No se encontraron órdenes de laboratorio.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Órdenes de Laboratorio</title>
    <link rel="stylesheet" href="home.css">
</head>
<body>
<div class="container">
    <!-- Navegación -->
    <nav>
        <a href="home.php"> <img src="img/hogar.png" alt="">Home</a>
        <a href="perfil.php"><img src="img/perfil.png" alt="">Perfil de Usuario</a>
    </nav>

    <!-- Encabezado con logo, nombre del paciente y botón de cerrar sesión -->
    <div class="header">
        <img src="img/R-FAST-PNG-05.png" alt="Logo">
        <h1><?php echo htmlspecialchars($patientName); ?></h1>
        <button onclick="window.location.href='logout.php'">Cerrar Sesión</button>
    </div>

    <!-- Contenido principal -->
    <div class="content">
        <h2>Órdenes de Laboratorio</h2>
        <form method="GET" action="">
            <div class="filtro">
                <div class="numero">
                    <label for="numeroOrden">Número de orden</label>
                    <input type="text" name="numeroOrden" id="numeroOrden" value="<?php echo htmlspecialchars($numeroOrden); ?>">
                </div>
                <div class="fecha">
                    <label for="fechaInicio">Rango de Fecha</label>
                    <input type="date" name="fechaInicio" id="fechaInicio" value="<?php echo htmlspecialchars($fechaInicio); ?>">
                    <input type="date" name="fechaFin" id="fechaFin" value="<?php echo htmlspecialchars($fechaFin); ?>">
                </div>
                <button type="submit">Filtrar</button>
            </div>
        </form>
        <div class="orden">
            <table>
                <thead>
                    <tr>
                        <th>Fecha de la orden</th>
                        <th>Documento de la orden</th>
                        <th>Número de la orden</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($result as $row) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['fecha']); ?></td>
                            <td><?php echo htmlspecialchars($row['id_documento']); ?></td>
                            <td><?php echo htmlspecialchars($row['orden']); ?></td>
                            <td class="actions">
                                <form action="orden.php" method="POST">
                                    <input type="hidden" name="orden_id" value="<?php echo htmlspecialchars($row['orden']); ?>">
                                    <button type="submit" name="ver">Ver</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Paginación -->
            <div class="pagination">
                <button id="prevPage" <?php if ($page <= 1) echo 'disabled'; ?> onclick="changePage(<?php echo $page - 1; ?>)">&lt;</button>
                <input type="text" id="numeroPagina" pattern="[0-9]+" value="<?php echo $page; ?>" min="1" max="<?php echo $totalPages; ?>" onchange="changePage(this.value)">
                <span> de <?php echo $totalPages; ?></span>
                <button id="nextPage" <?php if ($page >= $totalPages) echo 'disabled'; ?> onclick="changePage(<?php echo $page + 1; ?>)">&gt;</button>
            </div>
        </div>
    </div>
</div>

<script>
function changePage(newPage) {
    let url = new URL(window.location.href);
    url.searchParams.set('page', newPage);
    window.location.href = url.toString();
}
</script>

</body>
</html>