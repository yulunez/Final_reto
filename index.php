<?php


session_start();
include_once("conexion.php");

$conexion = new Conexion();
$db = $conexion->getConexion();

if (isset($_GET['btn'])) {
    $tipoDocumento = $_GET['tipoDocumento'];
    $numeroDocumento = $_GET['numeroDocumento'];
    $fechaNacimiento = $_GET['fechaNacimiento'];

    // Validar entradas
    $errors = [];

    // Verificar que el tipo de documento sea un valor válido 
    if (empty($tipoDocumento)) {
        $errors[] = "Por favor, seleccione un tipo de documento.";
    }

    // Verifica que el número de documento contenga solo dígitos
    if (!preg_match('/^\d+$/', $numeroDocumento)) {
        $errors[] = "Número de documento debe ser solo dígitos.";
    }

    // Verifica que la fecha de nacimiento tenga un formato válido (YYYY-MM-DD)
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaNacimiento)) {
        $errors[] = "Fecha de nacimiento debe estar en formato YYYY-MM-DD.";
    }

    // Si hay errores, se muestra y no ejecuta la consulta
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo $error . "<br>";
        }
        exit; // Termina el script para evitar el envío
    }

    try {
        // Preparar la consulta SQL
        $query = "SELECT * FROM public.gen_m_persona WHERE id_tipoid = :tipoDocumento AND numeroid = :numeroDocumento AND fechanac = :fechaNacimiento";
        
        // Preparar la sentencia
        $stmt = $db->prepare($query);

        // Vincular los parámetros a los valores ingresados por el usuario
        $stmt->bindParam(':tipoDocumento', $tipoDocumento);
        $stmt->bindParam(':numeroDocumento', $numeroDocumento);
        $stmt->bindParam(':fechaNacimiento', $fechaNacimiento);

        // Ejecutar la consulta
        $stmt->execute();

        // Verificar si se encontró una coincidencia
        if ($stmt->rowCount() > 0) {
            // Obtener el usuario
            $user = $stmt->fetch(PDO::FETCH_ASSOC); // Obtiene el primer resultado como un array asociativo

            // Almacenar el ID del usuario en la sesión
            $_SESSION['user_id'] = $user['id']; // Asegúrate de que 'id' es el nombre correcto de la columna

            // Verificar si el usuario está en la tabla fac_p_profesional
            $userId = $_SESSION['user_id'];
            $checkQuery = "SELECT * FROM fac_p_profesional WHERE id_persona = :userId"; // Cambia 'user_id' por el nombre correcto de la columna en fac_p_profesional
            $checkStmt = $db->prepare($checkQuery);
            $checkStmt->bindParam(':userId', $userId);
            $checkStmt->execute();

            if ($checkStmt->rowCount() > 0) {
                // El usuario está en la tabla fac_p_profesional, redirigir a perfil.php
                header("Location: perfil.php");
                exit; // Asegúrate de llamar a exit después de header
            } else {
                // Si no está en la tabla fac_p_profesional, redirigir a home.php
                header("Location: home.php");
                exit; // Asegúrate de llamar a exit después de header
            }
        } else {
            echo "Validación fallida. Los datos no coinciden con ningún registro.";
        }
    } catch (PDOException $e) {
        echo "Error en la validación: " . $e->getMessage();
    }

    
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reto_MD</title>
    <link rel="stylesheet" href="login.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Merriweather+Sans:ital,wght@1,500&display=swap');
    </style>
</head>
<body>
<!--este div contiene todo el formulario de login-->
<div class="login-container">
    <!--Este es un pequeño mensaje de bienvenida-->
    <h2>Iniciar Sesión</h2>
    <p style="text-align: center;">¡Bienvenido a tu portal medico! <br> Por favor ingresa tus datos
        para conocer los resultados de tus examenes</p>
    <!--el formulario que lleva a la pagina php-->
    <form action="index.php" method="GET" onsubmit="return validateForm()">

        <img src="https://i.gifer.com/YCZH.gif" class="loading" id="loadingImg" alt="Cargando...">


        <label for="tipoDocumento">Tipo de documento:</label>
        <select id="tipoDocumento" name="tipoDocumento" required>
            <option value="">Selecciona...</option>
            <option value="1">Cédula de ciudadanía</option>
            <option value="2">Tarjeta de identidad</option>
            <option value="3">Cédula de extranjería</option>
            <option value="4">Registro civil</option>
            <option value="5">Pasaporte</option>
            <option value="6">Adulto sin identificación</option>
            <option value="7">Menor sin identificación</option>
            <option value="8">Número único de identificación</option>
            <option value="9">Certificado de nacido vivo</option>
            <option value="10">Salvoconducto</option>
            <option value="11">Nit</option>
            <option value="12">Carnet diplomático</option>
            <option value="13">Permiso especial de permanencia</option>
            <option value="14">Residente especial para la paz</option>
            <option value="15">Permiso por protección temporal</option>
            <option value="16">Documento extranjero</option>
        </select>

        
        <label for="numeroDocumento">Número de documento:</label>
        <input type="text" id="numeroDocumento" name="numeroDocumento" pattern="[0-9]+" required placeholder="Ingresa tu número de documento">

        
        <label for="fechaNacimiento">Fecha de nacimiento:</label>
        <input type="date" id="fechaNacimiento" name="fechaNacimiento">

        <div class="captcha-container">
            <canvas id="captcha-canvas" width="200" height="50"></canvas>
            <button type="button" onclick="generateCaptcha()">Recargar CAPTCHA</button>
            <input type="text" id="captcha-input" placeholder="Ingrese el código CAPTCHA" required>
        </div>

        <button type="submit" name="btn" class="btn">Ingresar</button>
    </form>
</div>

<div class="img">
    <img src="" alt="">
</div>

<script src="login.js"></script>
</body>
</html>