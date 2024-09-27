<?php 
session_start(); 
$userId = $_SESSION['user_id']; // Asegúrate de que la sesión contiene 'user_id'

include_once("conexion.php");
$conexion = new Conexion();
$db = $conexion->getConexion();

try {
    // Preparar la consulta SQL para obtener los datos del paciente, incluyendo el tipo de documento
    $query = "SELECT p.id_tipoid, p.numeroid, p.apellido1, p.apellido2, p.nombre1, p.nombre2, 
                     p.fechanac, p.id_sexobiologico, p.direccion, p.tel_movil, p.email,
                     l.nombre AS tipo_documento
               FROM gen_m_persona p
               LEFT JOIN gen_p_listaopcion l ON p.id_tipoid = l.valor
               WHERE p.id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    
    // Obtener los datos del paciente
    $paciente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($paciente) {
        // Asignar los datos del paciente a variables
        $tipo_documento = $paciente['tipo_documento'];
        $numeroid = $paciente['numeroid'];
        $apellido1 = $paciente['apellido1'];
        $apellido2 = $paciente['apellido2'];
        $nombre1 = $paciente['nombre1'];
        $nombre2 = $paciente['nombre2'];
        $fechanac = $paciente['fechanac'];
        $id_sexobiologico = $paciente['id_sexobiologico'];
        $direccion = $paciente['direccion'];
        $tel_movil = $paciente['tel_movil'];
        $email = $paciente['email'];

        // Determinar el sexo biológico
        if ($id_sexobiologico == 29) {
            $sexo_biologico = "Hombre";
        } elseif ($id_sexobiologico == 30) {
            $sexo_biologico = "Mujer";
        } else {
            $sexo_biologico = "No especificado";
        }

        // Crear una cadena con el tipo de documento y número de identificación
        $identificacion = $tipo_documento . ": " . $numeroid;

    } else {
        echo "No se encontró al paciente.";
    }
} catch (PDOException $e) {
    echo "Error al obtener los datos del paciente: " . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil del Paciente</title>
    <link rel="stylesheet" href="perfil.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Merriweather+Sans:ital,wght@1,500&display=swap');
    </style>
</head>
<body>
<div class="container">

<!-- Navegación -->
<nav>
    <a href="home.php"> <img src="img/hogar.png" alt="">Home</a>
    <a href="perfil.php" class="perfil"><img src="img/perfil.png" alt="">Perfil de Usuario</a>
</nav>

<!-- Encabezado con logo, nombre del paciente y botón de cerrar sesión -->
<div class="header">
    <img src="img/R-FAST-PNG-05.png" alt="Logo">
    <button onclick="redirigir()">Cerrar Sesión</button>
</div>

<!-- Contenido principal -->
<div class="content">
    <div class="usu" style="display: flex; align-items: center;">
        <img src="img/paciente.jpg" alt="Foto del Paciente" style="width: 150px; height: 150px;">
        <div>
            <h2 class="saludo text">¡Hola, <?php echo $apellido1 . ' ' . $apellido2 . ' ' . $nombre1 . ' ' . $nombre2; ?>!</h2>
            <p class="bienvenida text">Bienvenido al portal</p>
        </div>
    </div>
    <div class="datosu">
      <div class="dato">
        <div class="texto">
            <h2>Tipo de Documento</h2>
            <p><?php echo $tipo_documento; ?></p>
        </div>
        <div class="imagen">
            <img src="img/documento.jpg" alt="Documento">
        </div>
      </div>
      <div class="dato">
        <div class="texto">
            <h2>Número de Identificación</h2>
            <p><?php echo $numeroid; ?></p>
        </div>
        <div class="imagen">
            <img src="img/numero-identificacion.jpg" alt="Número de Identificación">
        </div>
      </div>
      <div class="dato">
        <div class="texto">
            <h2>Nombre Completo</h2>
            <?php echo $apellido1 . ' ' . $apellido2 . ' ' . $nombre1 . ' ' . $nombre2; ?>!</h2>
        </div>
        <div class="imagen">
            <img src="img/nombre.jpg" alt="Nombre">
        </div>
      </div>
      <div class="dato">
        <div class="texto">
            <h2>Fecha de nacimiento</h2>
            <p><?php echo $fechanac; ?></p>
        </div>
        <div class="imagen">
           <img src="img/fecha_nac.jpg" alt="Fecha de Nacimiento"> 
        </div>
      </div>
      <div class="dato">
        <div class="texto">
            <h2>Sexo Biológico</h2>
            <p><?php echo $sexo_biologico; ?></p>
        </div>
        <div class="imagen">
            <img src="img/sexo.biologico.jpg" alt="Sexo Biológico">
        </div>
      </div>
      <div class="dato">
        <div class="texto">
            <h2>Dirección de Residencia</h2>
            <p><?php echo $direccion; ?></p>
        </div>
        <div class="imagen">
            <img src="img/direccion.jpg" alt="Dirección">
        </div>
      </div>
      <div class="dato">
        <div class="texto">
            <h2>Número de Teléfono</h2>
            <p><?php echo $tel_movil; ?></p>
        </div>
        <div class="imagen">
            <img src="img/telefono.jpg" alt="Teléfono">
        </div>
      </div>
      <div class="dato">
        <div class="texto">
            <h2>Correo Electrónico</h2>
            <p><?php echo $email; ?></p>
        </div>
        <div class="imagen">
            <img src="img/email.jpg" alt="Correo Electrónico">
        </div>
      </div>
    </div>
    
</div>

</div>
    <script src="perfil.js"></script>
</body>
</html>
