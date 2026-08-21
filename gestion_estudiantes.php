<?php
require_once 'models/Estudiante.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== true) {
    header("Location: index.php");
    exit();
}

$mensaje = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $accion = isset($_POST['accion']) ? $_POST['accion'] : '';

    if ($accion === 'crear') {
        $nombre = trim($_POST['nombre']);
        $password = $_POST['password'];

        if ($nombre === "" || $password === "") {
            $error = "Debes completar todos los campos.";
        } elseif (strlen($password) < 4) {
            $error = "La contraseña debe tener al menos 4 caracteres.";
        } else {
            $existe = Estudiante::getByNombre($nombre);
            if ($existe) {
                $error = "Ya existe un estudiante registrado con ese nombre.";
            } else {
                try {
                    Estudiante::create($nombre, $password);
                    $mensaje = "Estudiante '{$nombre}' creado correctamente. Entregue las credenciales al estudiante.";
                } catch (Exception $e) {
                    $error = "Error al crear el estudiante.";
                }
            }
        }
    } elseif ($accion === 'eliminar') {
        $id = (int)$_POST['id'];
        $estudiante = Estudiante::getById($id);
        if ($estudiante) {
            Estudiante::delete($id);
            $mensaje = "Estudiante '{$estudiante['nombre']}' eliminado correctamente.";
        } else {
            $error = "No se encontró el estudiante a eliminar.";
        }
    }
}

$estudiantes = Estudiante::getAll();

include 'view/cabecera.php';
?>

<style>
    .gestion-content {
        max-width: 760px;
        margin: 0 auto;
    }

    .card {
        background: white;
        border-radius: 10px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .card h2 {
        font-size: 20px;
        margin-top: 0;
        margin-bottom: 20px;
        color: #222;
    }

    .form-group {
        margin-bottom: 15px;
    }
    .form-group label {
        display: block;
        font-size: 13px;
        font-weight: bold;
        color: #444;
        margin-bottom: 6px;
    }
    .form-group input {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 14px;
        box-sizing: border-box;
    }
    .btn-submit {
        width: 100%;
        padding: 12px;
        background-color: #004b93;
        color: white;
        border: none;
        border-radius: 5px;
        font-size: 14px;
        font-weight: bold;
        cursor: pointer;
        margin-top: 10px;
    }
    .btn-submit:hover { background-color: #002d5a; }

    .mensaje {
        padding: 12px 15px;
        border-radius: 6px;
        font-size: 14px;
        margin-bottom: 20px;
        font-weight: bold;
    }
    .mensaje-exito { background-color: #e8f5e9; color: #2e7d32; }
    .mensaje-error { background-color: #fdecea; color: #c62828; }

    .tabla-estudiantes {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }
    .tabla-estudiantes th {
        background-color: #f4f7f6;
        color: #444;
        text-align: left;
        padding: 12px;
        font-size: 14px;
        border-bottom: 2px solid #ddd;
    }
    .tabla-estudiantes td {
        padding: 12px;
        font-size: 14px;
        border-bottom: 1px solid #eee;
        color: #333;
    }
    .btn-eliminar {
        padding: 6px 14px;
        border-radius: 5px;
        font-size: 13px;
        font-weight: bold;
        color: white;
        background-color: #f44336;
        border: none;
        cursor: pointer;
    }
    .btn-eliminar:hover { background-color: #d32f2f; }

    .nota-credenciales {
        margin-top: 10px;
        font-size: 12px;
        color: #777;
    }
</style>

<div class="dashboard-header">
    <h1>Gestionar Estudiantes</h1>
    <p>Como docente puedes crear o eliminar las cuentas de acceso de los estudiantes.</p>
</div>

<div class="gestion-content">

    <?php if(!empty($mensaje)): ?>
        <div class="mensaje mensaje-exito"><?php echo htmlspecialchars($mensaje); ?></div>
    <?php endif; ?>

    <?php if(!empty($error)): ?>
        <div class="mensaje mensaje-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="card">
        <h2>Añadir Estudiante</h2>
        <form action="gestion_estudiantes.php" method="POST">
            <input type="hidden" name="accion" value="crear">
            <div class="form-group">
                <label>Nombre completo:</label>
                <input type="text" name="nombre" required placeholder="Nombre del estudiante" autocomplete="off">
            </div>
            <div class="form-group">
                <label>Contraseña (credenciales que entregarás al estudiante):</label>
                <input type="text" name="password" required placeholder="Ej. Paccioli2026" autocomplete="off">
            </div>
            <button type="submit" class="btn-submit">Crear Cuenta de Estudiante</button>
            <p class="nota-credenciales">El estudiante iniciará sesión con su nombre completo y esta contraseña.</p>
        </form>
    </div>

    <div class="card" style="margin-top: 30px;">
        <h2>Estudiantes Registrados</h2>

        <?php if (empty($estudiantes)): ?>
            <p style="color: #999; text-align: center; padding: 20px;">No hay estudiantes registrados todavía.</p>
        <?php else: ?>
            <table class="tabla-estudiantes">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th style="width: 130px;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($estudiantes as $indice => $est): ?>
                        <tr>
                            <td><?php echo $indice + 1; ?></td>
                            <td><?php echo htmlspecialchars($est['nombre']); ?></td>
                            <td>
                                <form action="gestion_estudiantes.php" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar a <?php echo htmlspecialchars(addslashes($est['nombre'])); ?>?');">
                                    <input type="hidden" name="accion" value="eliminar">
                                    <input type="hidden" name="id" value="<?php echo $est['id']; ?>">
                                    <button type="submit" class="btn-eliminar">Quitar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</div>

<?php include 'view/pie.php'; ?>