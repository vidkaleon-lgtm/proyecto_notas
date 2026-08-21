<?php 
require_once 'models/Nota.php';
require_once 'models/Estudiante.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== true) {
    header("Location: index.php");
    exit();
}

$semestre_actual = isset($_GET['semestre']) ? (int)$_GET['semestre'] : 1;

/* ==========================================================
   LÓGICA: PROCESAR REGISTRO O EDICIÓN DE NOTA (POST)
   ========================================================== */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'create') {
    $data = [
        'estudiante' => $_POST['estudiante'],
        'carrera' => $_POST['carrera'],
        'materia' => $_POST['materia'],
        'n1' => round((float)$_POST['nota1']),
        'n2' => round((float)$_POST['nota2']),
        'n3' => round((float)$_POST['nota3']),
        'n4' => round((float)$_POST['nota4']),
        'semestre' => (int)$_POST['semestre_destino'],
    ];
    $id_editar = $_POST['id_editar'] ?? '';

    try {
        if ($id_editar !== "") {
            Nota::update((int)$id_editar, $data);
        } else {
            Nota::create($data);
        }
    } catch (Exception $e) {
        // Error handling could be improved
    }
    
    header("Location: dashboard.php?semestre=" . $semestre_actual);
    exit();
}

/* ==========================================================
   LÓGICA: PROCESAR ELIMINACIÓN (POST)
   ========================================================== */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'delete_selected') {
    if (isset($_POST['seleccionados']) && is_array($_POST['seleccionados'])) {
        foreach ($_POST['seleccionados'] as $id) {
            Nota::delete((int)$id);
        }
    }
    
    header("Location: dashboard.php?semestre=" . $semestre_actual);
    exit();
}

$lista_notas = Nota::getBySemestre($semestre_actual);

include 'view/cabecera.php'; 
?>

<style>
    .dashboard-content {
        display: grid;
        grid-template-columns: 350px 1fr;
        gap: 30px;
        align-items: start;
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
    .form-group input, .form-group select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 14px;
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

    .table-header-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        gap: 10px;
    }

    .action-buttons-group {
        display: flex;
        gap: 10px;
    }

    .btn-delete-global {
        padding: 8px 16px;
        border-radius: 5px;
        font-size: 13px;
        font-weight: bold;
        color: white;
        background-color: #f44336;
        border: none;
        cursor: pointer;
        transition: background 0.2s;
    }
    .btn-delete-global:hover { background-color: #d32f2f; }

    .btn-edit-global {
        padding: 8px 16px;
        border-radius: 5px;
        font-size: 13px;
        font-weight: bold;
        color: white;
        background-color: #ff9800;
        border: none;
        cursor: pointer;
        transition: background 0.2s;
    }
    .btn-edit-global:hover { background-color: #e68a00; }

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

    .chk-select {
        transform: scale(1.2);
        cursor: pointer;
    }
</style>

<div class="dashboard-header">
    <h1>INSTITUTO-PACCIOLI</h1>
    <p>Centralizador Académico de Calificaciones</p>
</div>

<div class="dashboard-content">
    
    <div class="card">
        <h2 id="form-titulo">Registrar Calificaciones</h2>
        <form action="dashboard.php?semestre=<?php echo $semestre_actual; ?>" method="POST" id="form-registro">
            <input type="hidden" name="action" value="create">
            <input type="hidden" name="id_editar" id="id_editar" value="">
            
            <div class="form-group">
                <label>Estudiante:</label>
                <input type="text" name="estudiante" id="form_estudiante" required placeholder="Nombre completo">
            </div>
            
            <div class="form-group">
                <label>Carrera:</label>
                <select name="carrera" id="form_carrera">
                    <option value="Contaduría General">Contaduría General</option>
                    <option value="Secretariado Ejecutivo">Secretariado Ejecutivo</option>
                    <option value="Sistemas Informáticos">Sistemas Informáticos</option>
                    <option value="Electrónica">Electrónica</option>
                    <option value="Electricidad Industrial">Electricidad Industrial</option>
                    <option value="Gastronomía">Gastronomía</option>
                </select>
            </div>

            <div class="form-group">
                <label>Materia / Módulo:</label>
                <input type="text" name="materia" id="form_materia" required placeholder="Nombre de la materia">
            </div>

            <div class="form-group" id="contenedor-semestre-destino">
                <label>Semestre Destino:</label>
                <select name="semestre_destino">
                    <option value="1" <?php echo $semestre_actual == 1 ? 'selected' : ''; ?>>Primer Semestre</option>
                    <option value="2" <?php echo $semestre_actual == 2 ? 'selected' : ''; ?>>Segundo Semestre</option>
                    <option value="3" <?php echo $semestre_actual == 3 ? 'selected' : ''; ?>>Tercer Semestre</option>
                    <option value="4" <?php echo $semestre_actual == 4 ? 'selected' : ''; ?>>Cuarto Semestre</option>
                </select>
            </div>

            <div class="form-group">
                <label>Nota 1 (25%):</label>
                <input type="number" name="nota1" id="form_n1" min="0" max="100" step="1" required>
            </div>

            <div class="form-group">
                <label>Nota 2 (25%):</label>
                <input type="number" name="nota2" id="form_n2" min="0" max="100" step="1" required>
            </div>

            <div class="form-group">
                <label>Nota 3 (25%):</label>
                <input type="number" name="nota3" id="form_n3" min="0" max="100" step="1" required>
            </div>

            <div class="form-group">
                <label>Nota 4 (25%):</label>
                <input type="number" name="nota4" id="form_n4" min="0" max="100" step="1" required>
            </div>

            <button type="submit" class="btn-submit" id="btn-enviar-formulario">Guardar en Centralizador</button>
        </form>
    </div>

    <div>
        <div class="semestres-tabs">
            <a href="dashboard.php?semestre=1" class="tab <?php echo $semestre_actual === 1 ? 'active' : ''; ?>">1° Semestre</a>
            <a href="dashboard.php?semestre=2" class="tab <?php echo $semestre_actual === 2 ? 'active' : ''; ?>">2° Semestre</a>
            <a href="dashboard.php?semestre=3" class="tab <?php echo $semestre_actual === 3 ? 'active' : ''; ?>">3° Semestre</a>
            <a href="dashboard.php?semestre=4" class="tab <?php echo $semestre_actual === 4 ? 'active' : ''; ?>">4° Semestre</a>
        </div>

        <form action="dashboard.php?semestre=<?php echo $semestre_actual; ?>" method="POST" id="form-centralizador">
            <input type="hidden" name="action" value="delete_selected">

            <div class="table-header-actions">
                <h2 style="margin: 0; font-size: 18px; color: #222;">Centralizador — Semestre <?php echo $semestre_actual; ?></h2>
                
                <div class="action-buttons-group">
                    <button type="button" class="btn-edit-global" onclick="cargarDatosEnFormulario()">
                        ✏️ Modificar Notas
                    </button>

                    <button type="submit" class="btn-delete-global" onclick="return confirmarEliminacion()">
                        🗑️ Eliminar Seleccionado
                    </button>
                </div>
            </div>

            <div class="card">
                <table class="table-notas">
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;"><input type="checkbox" id="master-check" onclick="seleccionarTodos(this)"></th>
                            <th>Estudiante</th>
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
                        if (empty($lista_notas)): 
                        ?>
                            <tr>
                                <td colspan="10" style="text-align: center; color: #999; padding: 30px;">
                                    No hay calificaciones registradas en este semestre.
                                </td>
                            </tr>
                        <?php 
                        else: 
                            foreach ($lista_notas as $item): 
                                $promedio_formateado = number_format($item['promedio'], 0);
                                $estado = $item['estado'];
                                $badge_clase = ($estado === 'APROBADO') ? 'badge-aprobado' : 'badge-reprobado';
                        ?>
                            <tr>
                                <td style="text-align: center;">
                                    <input type="checkbox" name="seleccionados[]" value="<?php echo $item['id']; ?>" class="chk-select" 
                                           data-nombre="<?php echo htmlspecialchars($item['estudiante_nombre']); ?>"
                                           data-carrera="<?php echo htmlspecialchars($item['carrera_nombre']); ?>"
                                           data-materia="<?php echo htmlspecialchars($item['materia']); ?>"
                                           data-n1="<?php echo number_format($item['n1'], 0); ?>"
                                           data-n2="<?php echo number_format($item['n2'], 0); ?>"
                                           data-n3="<?php echo number_format($item['n3'], 0); ?>"
                                           data-n4="<?php echo number_format($item['n4'], 0); ?>">
                                </td>
                                <td><?php echo htmlspecialchars($item['estudiante_nombre']); ?></td>
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
        </form>
    </div>

</div>

<script>
function seleccionarTodos(source) {
    const checkboxes = document.getElementsByClassName('chk-select');
    for (let i = 0; i < checkboxes.length; i++) {
        checkboxes[i].checked = source.checked;
    }
}

function confirmarEliminacion() {
    const checkboxes = document.getElementsByClassName('chk-select');
    let seleccionado = false;
    for (let i = 0; i < checkboxes.length; i++) {
        if (checkboxes[i].checked) { seleccionado = true; break; }
    }
    if (!seleccionado) {
        alert("Por favor, selecciona a un estudiante marcando su casillero antes de presionar Eliminar.");
        return false;
    }
    return confirm("¿Estás seguro de que deseas eliminar al estudiante seleccionado?");
}

function cargarDatosEnFormulario() {
    const checkboxes = document.getElementsByClassName('chk-select');
    let casilleroSeleccionado = null;
    let contador = 0;

    for (let i = 0; i < checkboxes.length; i++) {
        if (checkboxes[i].checked) {
            casilleroSeleccionado = checkboxes[i];
            contador++;
        }
    }

    if (contador === 0) {
        alert("Por favor, selecciona un estudiante marcando su casillero para modificar sus notas.");
        return;
    }
    if (contador > 1) {
        alert("Por favor, selecciona solo un estudiante a la vez para modificar.");
        return;
    }

    const id = casilleroSeleccionado.value;
    const nombre = casilleroSeleccionado.getAttribute('data-nombre');
    const carrera = casilleroSeleccionado.getAttribute('data-carrera');
    const materia = casilleroSeleccionado.getAttribute('data-materia');
    const n1 = casilleroSeleccionado.getAttribute('data-n1');
    const n2 = casilleroSeleccionado.getAttribute('data-n2');
    const n3 = casilleroSeleccionado.getAttribute('data-n3');
    const n4 = casilleroSeleccionado.getAttribute('data-n4');

    document.getElementById('id_editar').value = id;
    document.getElementById('form_estudiante').value = nombre;
    document.getElementById('form_carrera').value = carrera;
    document.getElementById('form_materia').value = materia;
    document.getElementById('form_n1').value = n1;
    document.getElementById('form_n2').value = n2;
    document.getElementById('form_n3').value = n3;
    document.getElementById('form_n4').value = n4;

    document.getElementById('form-titulo').innerText = "✏️ Editando Notas de: " + nombre;
    document.getElementById('btn-enviar-formulario').innerText = "Actualizar Calificaciones";
    document.getElementById('btn-enviar-formulario').style.backgroundColor = "#ff9800";
    document.getElementById('contenedor-semestre-destino').style.display = "none"; 

    document.getElementById('form-registro').scrollIntoView({ behavior: 'smooth' });
}
</script>

<?php include 'view/pie.php'; ?>
