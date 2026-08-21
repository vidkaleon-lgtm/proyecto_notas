<?php

require_once __DIR__ . '/Database.php';

class Carrera
{
    public static function getAll(): array
    {
        return Database::fetchAll('SELECT id, nombre FROM carreras ORDER BY nombre');
    }

    public static function getById(int $id): ?array
    {
        return Database::fetchOne('SELECT id, nombre FROM carreras WHERE id = ?', [$id]);
    }

    public static function getByNombre(string $nombre): ?array
    {
        return Database::fetchOne('SELECT id, nombre FROM carreras WHERE nombre = ?', [$nombre]);
    }

    public static function getForSelect(): array
    {
        $carreras = self::getAll();
        return array_column($carreras, 'nombre', 'id');
    }
}