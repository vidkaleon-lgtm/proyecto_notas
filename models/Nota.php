<?php

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Carrera.php';

class Nota
{
    public static function create(array $data): int
    {
        $estudiante = Database::fetchOne('SELECT id FROM estudiantes WHERE nombre = ?', [$data['estudiante']]);
        if (!$estudiante) {
            throw new Exception('Estudiante no encontrado');
        }

        $carrera = Carrera::getByNombre($data['carrera']);
        if (!$carrera) {
            throw new Exception('Carrera no encontrada');
        }

        $promedio = round(($data['n1'] * 0.25) + ($data['n2'] * 0.25) + ($data['n3'] * 0.25) + ($data['n4'] * 0.25), 2);
        $estado = ($promedio >= 61) ? 'APROBADO' : 'REPROBADO';

        Database::query(
            'INSERT INTO notas (estudiante_id, carrera_id, materia, n1, n2, n3, n4, semestre, promedio, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $estudiante['id'],
                $carrera['id'],
                $data['materia'],
                $data['n1'],
                $data['n2'],
                $data['n3'],
                $data['n4'],
                $data['semestre'],
                $promedio,
                $estado
            ]
        );

        return (int)Database::lastInsertId();
    }

    public static function getBySemestre(int $semestre): array
    {
        $sql = '
            SELECT n.*, e.nombre as estudiante_nombre, c.nombre as carrera_nombre
            FROM notas n
            JOIN estudiantes e ON n.estudiante_id = e.id
            JOIN carreras c ON n.carrera_id = c.id
            WHERE n.semestre = ?
            ORDER BY n.created_at DESC
        ';
        return Database::fetchAll($sql, [$semestre]);
    }

    public static function update(int $id, array $data): bool
    {
        $estudiante = Database::fetchOne('SELECT id FROM estudiantes WHERE nombre = ?', [$data['estudiante']]);
        if (!$estudiante) {
            throw new Exception('Estudiante no encontrado');
        }

        $carrera = Carrera::getByNombre($data['carrera']);
        if (!$carrera) {
            throw new Exception('Carrera no encontrada');
        }

        $promedio = round(($data['n1'] * 0.25) + ($data['n2'] * 0.25) + ($data['n3'] * 0.25) + ($data['n4'] * 0.25), 2);
        $estado = ($promedio >= 61) ? 'APROBADO' : 'REPROBADO';

        $result = Database::query(
            'UPDATE notas SET estudiante_id = ?, carrera_id = ?, materia = ?, n1 = ?, n2 = ?, n3 = ?, n4 = ?, promedio = ?, estado = ? WHERE id = ?',
            [
                $estudiante['id'],
                $carrera['id'],
                $data['materia'],
                $data['n1'],
                $data['n2'],
                $data['n3'],
                $data['n4'],
                $promedio,
                $estado,
                $id
            ]
        );

        return $result->rowCount() > 0;
    }

    public static function delete(int $id): bool
    {
        $result = Database::query('DELETE FROM notas WHERE id = ?', [$id]);
        return $result->rowCount() > 0;
    }

    public static function getByEstudiante(string $nombre, int $semestre): array
    {
        $sql = '
            SELECT n.*, c.nombre as carrera_nombre
            FROM notas n
            JOIN estudiantes e ON n.estudiante_id = e.id
            JOIN carreras c ON n.carrera_id = c.id
            WHERE e.nombre = ? AND n.semestre = ?
            ORDER BY n.created_at DESC
        ';
        return Database::fetchAll($sql, [$nombre, $semestre]);
    }

    public static function getById(int $id): ?array
    {
        $sql = '
            SELECT n.*, e.nombre as estudiante_nombre, c.nombre as carrera_nombre
            FROM notas n
            JOIN estudiantes e ON n.estudiante_id = e.id
            JOIN carreras c ON n.carrera_id = c.id
            WHERE n.id = ?
        ';
        return Database::fetchOne($sql, [$id]);
    }

    public static function getCarrerasForSelect(): array
    {
        return Carrera::getAll();
    }
}