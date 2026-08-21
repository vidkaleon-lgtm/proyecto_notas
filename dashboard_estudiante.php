<?php
require_once 'models/Nota.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Control de seguridad
if (!isset($_SESSION['estudiante_autenticado']) || $_SESSION['estudiante_autenticado'] !== true) {
    header("Location: index.php");
    exit();
}

$nombre_estudiante = $_SESSION['estudiante_nombre'];
$semestre_actual = isset($_GET['semestre']) ? (int)$_GET['semestre'] : 1;

$mis_notas = Nota::getByEstudiante($nombre_estudiante, $semestre_actual);

include 'view/cabecera_estudiante.php';
?>

<style>
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

    .semestres-tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 15px;
    }
    .tab {
        padding: 10px 20px;
        background-color: #e0e0e0;
        color: #333;
        text-decoration: none;
        font-weight: bold;
        border-radius: 5px;
        font-size: 14px;
        transition: all 0.3s;
    }
    .tab.active {
        background-color: #004080;
        color: white;
    }

    .table-notas {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }
    .table-notas th {
        background-color: #f4f7f6;
        color: #444;
        text-align: left;
        padding: 12px;
        font-size: 14px;
        border-bottom: 2px solid #ddd;
    }
    .table-notas td {
        padding: 14px 12px;
        font-size: 14px;
        border-bottom: 1px solid #eee;
        color: #333;
    }

    .promedio-cell { font-weight: bold; color: #004080; }

    .badge {
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: bold;
        text-transform: uppercase;
        color: white;
        display: inline-block;
    }
    .badge-aprobado { background-color: #4caf50; }
    .badge-reprobado { background-color: #e53935; }
</style>

<div class="dashboard-header">
    <h1>Mis Notas</h1>
    <p>Hola, <?php echo htmlspecialchars($nombre_estudiante); ?>. Aquí puedes consultar tus calificaciones (solo lectura).</p>
</div>

<div>
    <div class="semestres-tabs">
        <a href="dashboard_estudiante.php?semestre=1" class="tab <?php echo $semestre_actual === 1 ? 'active' : ''; ?>">1° Semestre</a>
        <a href="dashboard_estudiante.php?semestre=2" class="tab <?php echo $semestre_actual === 2 ? 'active' : ''; ?>">2° Semestre</a>
        <a href="dashboard_estudiante.php?semestre=3" class="tab <?php echo $semestre_actual === 3 ? 'active' : ''; ?>">3° Semestre</a>
        <a href="dashboard_estudiante.php?semestre=4" class="tab <?php echo $semestre_actual === 4 ? 'active' : ''; ?>">4° Semestre</a>
    </div>

    <div class="card">
        <h2>Centralizador — Semestre <?php echo $semestre_actual; ?></h2>
        <table class="table-notas">
            <thead>
                <tr>
                    <th>Carrera</th>
                    <th>Materia</th>
                    <th>N1</th>
                    <th>N2</th>
                    <th>N3</th>
                    <th>N4</th>
                    <th>Promedio</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if (empty($mis_notas)): 
                ?>
                    <tr>
                        <td colspan="8" style="text-align: center; color: #999; padding: 30px;">
                            No tienes calificaciones registradas en este semestre.
                        </td>
                    </tr>
                <?php 
                else: 
                    foreach ($mis_notas as $item): 
                        $promedio_formateado = number_format($item['promedio'], 0);
                        $estado = $item['estado'];
                        $badge_clase = ($estado === 'APROBADO') ? 'badge-aprobado' : 'badge-reprobado';
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['carrera_nombre']); ?></td>
                        <td><?php echo htmlspecialchars($item['materia']); ?></td>
                        <td><?php echo number_format($item['n1'], 0); ?></td>
                        <td><?php echo number_format($item['n2'], 0); ?></td>
                        <td><?php echo number_format($item['n3'], 0); ?></td>
                        <td><?php echo number_format($item['n4'], 0); ?></td>
                        <td class="promedio-cell"><?php echo $promedio_formateado; ?></td>
                        <td><span class="badge <?php echo $badge_clase; ?>"><?php echo $estado; ?></span></td>
                    </tr>
                <?php 
                    endforeach; 
                endif; 
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'view/pie.php'; ?>
