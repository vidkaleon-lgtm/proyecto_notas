<?php

require_once __DIR__ . '/Database.php';

class Estudiante
{
    public static function create(string $nombre, string $password): int
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        Database::query(
            'INSERT INTO estudiantes (nombre, password) VALUES (?, ?)',
            [$nombre, $hash]
        );
        return (int)Database::lastInsertId();
    }

    public static function getByNombre(string $nombre): ?array
    {
        return Database::fetchOne('SELECT id, nombre, password FROM estudiantes WHERE nombre = ?', [$nombre]);
    }

    public static function getById(int $id): ?array
    {
        return Database::fetchOne('SELECT id, nombre, password FROM estudiantes WHERE id = ?', [$id]);
    }

    public static function getAll(): array
    {
        return Database::fetchAll('SELECT id, nombre FROM estudiantes ORDER BY nombre');
    }

    public static function delete(int $id): bool
    {
        $result = Database::query('DELETE FROM estudiantes WHERE id = ?', [$id]);
        return $result->rowCount() > 0;
    }

    public static function verifyPassword(string $nombre, string $password): ?array
    {
        $estudiante = self::getByNombre($nombre);
        if ($estudiante && password_verify($password, $estudiante['password'])) {
            return $estudiante;
        }
        return null;
    }

    public static function verifyDocente(string $password): bool
    {
        $docente = Database::fetchOne('SELECT password FROM docentes LIMIT 1');
        if ($docente && password_verify($password, $docente['password'])) {
            return true;
        }
        return false;
    }

    public static function createDocente(string $password): int
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        Database::query('INSERT INTO docentes (password) VALUES (?)', [$hash]);
        return (int)Database::lastInsertId();
    }
}