<?php
session_start();
include_once("conexion.php");

$conexion = new Conexion();
$db = $conexion->getConexion();

if (isset($_POST['ver']) && isset($_POST['orden_id'])) {
    $ordenId = $_POST['orden_id'];
    
    // Obtener información de la orden y del paciente
    $query = "SELECT o.*, p.nombre1, p.apellido1, p.numeroid AS identificacion, p.tel_movil, 
                     pr.codigo AS codigo_profesional, gp.nombre1 AS nombre_profesional, gp.apellido1 AS apellido_profesional
              FROM lab_m_orden o
              JOIN fac_m_tarjetero t ON o.id_historia = t.id
              JOIN gen_m_persona p ON t.id_persona = p.id
              LEFT JOIN fac_p_profesional pr ON o.id_profesional_ordena = pr.id
              LEFT JOIN gen_m_persona gp ON pr.id_persona = gp.id  -- Relación del doctor con gen_m_persona
              WHERE o.orden = :ordenId";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':ordenId', $ordenId, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $paciente = $row['nombre1'] . ' ' . $row['apellido1'];
        $identificacion = $row['identificacion'];
        $telefono = $row['tel_movil'];
        $medico = $row['nombre_profesional'] . ' ' . $row['apellido_profesional'] ?? 'No especificado';
        $servicio = 'Laboratorio'; // Asumiendo que siempre es laboratorio
        $fechaOrden = $row['fecha'];
        
        // Obtener resultados de las pruebas
            $queryResultados = "SELECT 
                g.nombre AS grupo,
                cups.codigo AS procedimiento_codigo,
                cups.nombre AS procedimiento_nombre,
                pr.codigo_prueba AS prueba_codigo,
                pr.nombre_prueba AS prueba_nombre,
                r.res_numerico, 
                r.res_opcion, 
                r.res_texto,
                pr.unidad AS unidad_medida,
                po.valor_ref_min_f, 
                po.valor_ref_max_f,
                po.valor_ref_min_m, 
                po.valor_ref_max_m
            FROM lab_m_orden_resultados r
            JOIN lab_p_pruebas pr ON r.id_prueba = pr.id
            JOIN lab_p_procedimientos p ON pr.id_procedimiento = p.id
            JOIN fac_p_cups cups ON p.id_cups = cups.id
            JOIN lab_p_grupos g ON p.id_grupo_laboratorio = g.id
            LEFT JOIN lab_p_pruebas_opciones po ON r.id_pruebaopcion = po.id
            WHERE r.id_orden = :ordenId
            ORDER BY g.nombre, cups.nombre, pr.nombre_prueba";

$stmtResultados = $db->prepare($queryResultados);
$stmtResultados->bindParam(':ordenId', $ordenId, PDO::PARAM_INT);
$stmtResultados->execute();

$grupos = array();
while ($rowResultado = $stmtResultados->fetch(PDO::FETCH_ASSOC)) {
// Agrupamos los resultados por grupo y procedimiento
$grupos[$rowResultado['grupo']][$rowResultado['procedimiento_codigo']][] = $rowResultado;
}
    } else {
        echo "Error: No se encontró la orden especificada.";
        exit;
    }
} else {
    echo "Error: No se proporcionó un ID de orden válido.";
    exit;
}
?>





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados de Laboratorio</title>
    <link rel="stylesheet" href="orden.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Merriweather+Sans:ital,wght@1,500&display=swap');
    </style>
</head>
<body>
<nav>
    <a href="home.php"><img src="img/hogar.png" alt=""> Home</a>
    <a href="perfil.php"><img src="img/perfil.png" alt="">Perfil del usuario</a>
</nav>
<div class="main-content">
    <div class="header">
        <h1>Resultados de Laboratorio</h1>
        <button class="logout-btn" onclick="window.location.href='logout.php'">Cerrar sesión</button>
    </div>
    <div class="patient-info">
        <p><strong style="color: darkblue;">Paciente:</strong> <?php echo htmlspecialchars($paciente); ?></p>
        <p><strong style="color: darkblue;">Identificación:</strong> <?php echo htmlspecialchars($identificacion); ?></p>
        <p><strong style="color: darkblue;">Teléfono:</strong> <?php echo htmlspecialchars($telefono); ?></p>
        <p><strong style="color: darkblue;">Médico:</strong> <?php echo htmlspecialchars($medico); ?></p>
        <p><strong style="color: darkblue;">Servicio:</strong> <?php echo htmlspecialchars($servicio); ?></p>
        <p><strong style="color: darkblue;">Fecha orden:</strong> <?php echo htmlspecialchars($fechaOrden); ?></p>
    </div>
    <?php foreach ($grupos as $nombreGrupo => $procedimientos): ?>
        <div class="lab-group">
            <h2><?php echo htmlspecialchars($nombreGrupo); ?></h2>
            <?php foreach ($procedimientos as $nombreProcedimiento => $pruebas): ?>
                <h3><?php echo htmlspecialchars($nombreProcedimiento); ?></h3>
                <table>
                    <thead>
                        <tr>
                            <th>Código de la prueba</th>
                            <th>Nombre de la prueba</th>
                            <th>Resultado</th>
                            <th>Valores de Referencia</th>
                            <th>Unidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pruebas as $prueba): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($prueba['prueba_codigo']); ?></td>
                                <td><?php echo htmlspecialchars($prueba['prueba_nombre']); ?></td>
                                <td><?php echo htmlspecialchars($prueba['res_numerico'] ?? $prueba['res_opcion'] ?? $prueba['res_texto'] ?? ''); ?></td>
                                <td><?php 
                                    $refMin = $prueba['valor_ref_min_f'] !== null ? $prueba['valor_ref_min_f'] : $prueba['valor_ref_min_m'];
                                    $refMax = $prueba['valor_ref_max_f'] !== null ? $prueba['valor_ref_max_f'] : $prueba['valor_ref_max_m'];
                                    echo $refMin !== null && $refMax !== null ? htmlspecialchars("$refMin - $refMax") : 'No especificado';
                                ?></td>
                                <td><?php echo htmlspecialchars($prueba['unidad_medida'] ?? ''); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
    <p style="color: rgb(1, 1, 75);">Fin del informe de resultados</p>
</div>
<script src="orden.js"></script>
</body>
</html>