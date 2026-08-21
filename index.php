<?php
require_once 'models/Estudiante.php';

session_start();

$error = "";

// Mensaje de éxito tras registrarse
$exito = "";
if (isset($_GET['registrado']) && $_GET['registrado'] == 1) {
    $exito = "Registro exitoso. Ahora puedes iniciar sesión como estudiante.";
}

// Pestaña activa por defecto
$tab_activa = isset($_GET['tab']) ? $_GET['tab'] : 'docente';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tipo = isset($_POST['tipo']) ? $_POST['tipo'] : 'docente';

    if ($tipo === 'docente') {
        $password_ingresada = $_POST['password'];

        if (Estudiante::verifyDocente($password_ingresada)) {
            $_SESSION['autenticado'] = true;
            unset($_SESSION['estudiante_autenticado']);
            unset($_SESSION['estudiante_nombre']);
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Contraseña incorrecta. Inténtelo de nuevo.";
        }
    } elseif ($tipo === 'estudiante') {
        $nombre = trim($_POST['estudiante_nombre']);
        $password_ingresada = $_POST['estudiante_password'];

        $estudiante = Estudiante::verifyPassword($nombre, $password_ingresada);
        if ($estudiante) {
            $_SESSION['estudiante_autenticado'] = true;
            $_SESSION['estudiante_nombre'] = $estudiante['nombre'];
            unset($_SESSION['autenticado']);
            header("Location: dashboard_estudiante.php");
            exit();
        }

        $error = "Estudiante o contraseña incorrecta. Si aún no tienes cuenta, pide al docente que te la cree.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Instituto Paccioli</title>
    <link rel="icon" type="image/jpeg" href="view/img/pacioli.jpg">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(rgba(0, 51, 102, 0.85), rgba(0, 51, 102, 0.85)), 
                        url('view/img/fondo_pacioli.jpg') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-card {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.3);
            text-align: center;
            width: 340px;
        }
        .login-card img {
            width: 70px;
            margin-bottom: 10px;
        }
        .login-card h2 {
            color: #003366;
            margin: 0 0 5px 0;
            font-size: 22px;
        }
        .login-card p {
            color: #666;
            font-size: 14px;
            margin-bottom: 20px;
        }
        .tabs {
            display: flex;
            gap: 5px;
            margin-bottom: 20px;
            background: #eef1f5;
            border-radius: 8px;
            padding: 4px;
        }
        .tabs button {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 6px;
            background: transparent;
            color: #555;
            font-weight: bold;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .tabs button.active {
            background-color: #004b93;
            color: white;
        }
        input[type="password"], input[type="text"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 14px;
        }
        button[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: #004b93;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: background 0.3s;
        }
        button[type="submit"]:hover {
            background-color: #002d5a;
        }
        .error {
            color: red;
            font-size: 13px;
            margin-bottom: 15px;
        }
        .exito {
            color: #2e7d32;
            font-size: 13px;
            margin-bottom: 15px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="login-card">
    <img src="view/img/pacioli.jpg" alt="Logo">
    <h2>INSTITUTO PACCIOLI</h2>
    <p>Centralizador de Calificaciones</p>

    <div class="tabs">
        <button type="button" id="tab-docente" class="tab-btn" onclick="cambiarTab('docente')">Docente</button>
        <button type="button" id="tab-estudiante" class="tab-btn" onclick="cambiarTab('estudiante')">Estudiante</button>
    </div>

    <?php if(!empty($error)): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if(!empty($exito)): ?>
        <div class="exito"><?php echo $exito; ?></div>
    <?php endif; ?>

    <!-- Login Docente -->
    <form id="form-docente" action="index.php" method="POST">
        <input type="hidden" name="tipo" value="docente">
        <input type="password" name="password" placeholder="Contraseña del Sistema" autocomplete="new-password" required>
        <button type="submit">Ingresar al Sistema</button>
    </form>

    <!-- Login Estudiante -->
    <form id="form-estudiante" action="index.php" method="POST" style="display:none;" autocomplete="off">
        <input type="hidden" name="tipo" value="estudiante">
        <input type="text" name="estudiante_nombre" placeholder="Tu nombre completo" autocomplete="off" required>
        <input type="password" name="estudiante_password" placeholder="Tu contraseña" autocomplete="new-password" required>
        <button type="submit">Ver Mis Notas</button>
    </form>
</div>

<script>
var tabActiva = "<?php echo $tab_activa; ?>";

function cambiarTab(tab) {
    var btnDocente = document.getElementById('tab-docente');
    var btnEstudiante = document.getElementById('tab-estudiante');
    var formDocente = document.getElementById('form-docente');
    var formEstudiante = document.getElementById('form-estudiante');

    if (tab === 'estudiante') {
        btnDocente.classList.remove('active');
        btnEstudiante.classList.add('active');
        formDocente.style.display = 'none';
        formEstudiante.style.display = 'block';
    } else {
        btnEstudiante.classList.remove('active');
        btnDocente.classList.add('active');
        formEstudiante.style.display = 'none';
        formDocente.style.display = 'block';
    }
}

cambiarTab(tabActiva);
</script>

</body>
</html>
