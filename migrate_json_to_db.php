<?php

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/models/Database.php';
require_once __DIR__ . '/models/Estudiante.php';
require_once __DIR__ . '/models/Carrera.php';
require_once __DIR__ . '/models/Nota.php';

echo "=== Migración JSON → MySQL ===\n\n";

$pdo = require __DIR__ . '/config/db.php';

echo "1. Verificando docente...\n";
$docente = $pdo->query("SELECT COUNT(*) FROM docentes")->fetchColumn();
if ($docente == 0) {
    $hash = password_hash('paccioli2026', PASSWORD_DEFAULT);
    $pdo->prepare("INSERT INTO docentes (password) VALUES (?)")->execute([$hash]);
    echo "   ✓ Docente creado con password 'paccioli2026'\n";
} else {
    echo "   ✓ Docente ya existe\n";
}

echo "\n2. Migrando estudiantes desde data/estudiantes.json...\n";
$estudiantesFile = __DIR__ . '/data/estudiantes.json';
if (file_exists($estudiantesFile)) {
    $estudiantes = json_decode(file_get_contents($estudiantesFile), true);
    if (is_array($estudiantes)) {
        $migrados = 0;
        foreach ($estudiantes as $est) {
            $exists = $pdo->prepare("SELECT COUNT(*) FROM estudiantes WHERE nombre = ?");
            $exists->execute([$est['nombre']]);
            if ($exists->fetchColumn() == 0) {
                $pdo->prepare("INSERT INTO estudiantes (nombre, password) VALUES (?, ?)")
                    ->execute([$est['nombre'], $est['password']]);
                $migrados++;
                echo "   ✓ {$est['nombre']}\n";
            } else {
                echo "   - {$est['nombre']} ya existe, omitido\n";
            }
        }
        echo "   Total migrados: $migrados\n";
    }
} else {
    echo "   Archivo no encontrado, omitiendo\n";
}

echo "\n3. Migrando notas desde data/notas.json...\n";
$notasFile = __DIR__ . '/data/notas.json';
if (file_exists($notasFile)) {
    $notasData = json_decode(file_get_contents($notasFile), true);
    if (is_array($notasData)) {
        $migradas = 0;
        foreach ($notasData as $semestre => $notas) {
            foreach ($notas as $nota) {
                $estudiante = $pdo->prepare("SELECT id FROM estudiantes WHERE LOWER(TRIM(nombre)) = LOWER(TRIM(?))");
                $estudiante->execute([$nota['estudiante']]);
                $estId = $estudiante->fetchColumn();

                $carrera = $pdo->prepare("SELECT id FROM carreras WHERE LOWER(TRIM(nombre)) = LOWER(TRIM(?))");
                $carrera->execute([$nota['carrera']]);
                $carId = $carrera->fetchColumn();

                if ($estId && $carId) {
                    $promedio = round(($nota['n1'] * 0.25) + ($nota['n2'] * 0.25) + ($nota['n3'] * 0.25) + ($nota['n4'] * 0.25), 2);
                    $estado = ($promedio >= 61) ? 'APROBADO' : 'REPROBADO';

                    $exists = $pdo->prepare("
                        SELECT COUNT(*) FROM notas 
                        WHERE estudiante_id = ? AND carrera_id = ? AND materia = ? AND semestre = ?
                    ");
                    $exists->execute([$estId, $carId, $nota['materia'], $semestre]);

                    if ($exists->fetchColumn() == 0) {
                        $pdo->prepare("
                            INSERT INTO notas (estudiante_id, carrera_id, materia, n1, n2, n3, n4, semestre, promedio, estado)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ")->execute([$estId, $carId, $nota['materia'], $nota['n1'], $nota['n2'], $nota['n3'], $nota['n4'], $semestre, $promedio, $estado]);
                        $migradas++;
                        echo "   ✓ {$nota['estudiante']} - {$nota['materia']} (Sem $semestre)\n";
                    } else {
                        echo "   - {$nota['estudiante']} - {$nota['materia']} (Sem $semestre) ya existe, omitido\n";
                    }
                } else {
                    echo "   ✗ No se encontró estudiante/carrera para: {$nota['estudiante']} / {$nota['carrera']}\n";
                }
            }
        }
        echo "   Total migradas: $migradas\n";
    }
} else {
    echo "   Archivo no encontrado, omitiendo\n";
}

echo "\n=== Migración completada ===\n";
echo "Verifica en phpMyAdmin: centralizador_paccioli > estudiantes, notas\n";