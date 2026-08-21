<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Control de seguridad
if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== true) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Centralizador Académico de Calificaciones</title>
    <link rel="icon" type="image/jpeg" href="view/img/pacioli.jpg">
    <style>
        * { box-sizing: border-box; font-family: 'Helvetica Neue', Arial, sans-serif; }

body { 
    margin: 0; 
    padding: 0; 
    background: linear-gradient(rgba(0, 247, 255, 0.6), rgba(0, 247, 255, 0.6)),
                url('view/img/fondo_pacioli.jpg') no-repeat center center fixed;
    background-size: cover;
    min-height: 200vh;
}
        
        /* Barra superior azul */
.navbar {
    background-color: #1A365D;
    color: white;
    padding: 12px 40px;
    display: flex;
    justify-content: flex-end; /* Empuja el menú de usuario (Admin) a la derecha */
    align-items: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    position: relative;        /* Eje de referencia para el centrado absoluto */
    min-height: 65px;          /* Asegura una buena altura vertical */
}

/* Contenedor del título: ¡Al centro exacto! */
.navbar .brand {
    display: flex;
    flex-direction: column;
    text-align: center;        /* Centra el subtítulo debajo del título principal */
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%); /* Truco matemático para centrar horizontal y verticalmente */
    width: auto;
}
        .navbar .brand .title {
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .navbar .brand .subtitle {
            font-size: 12px;
            opacity: 0.8;
            margin-top: 2px;
        }
        .navbar .user-menu {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }
        .navbar .user-menu img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: #ccc;
        }
        .navbar .user-menu a {
            color: white;
            text-decoration: none;
            font-size: 14px;
        }

        .main-container {
            padding: 30px 40px;
        }
        
        .dashboard-header h1 {
            margin: 0;
            font-size: 28px;
            color: #222;
        }
        .dashboard-header p {
    margin: 5px 0 25px 0;
    color: #000000; /* <-- Cambiado a negro puro */
    font-size: 15px;
}
    </style>
</head>
<body>

<div class="navbar">
    <div class="brand">
        <div class="title">🏛️ INSTITUTO PACCIOLI</div>
        <div class="subtitle">Centralizador Académico de Calificaciones</div>
    </div>
<div class="user-menu">
        <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Admin">
        <span>Admin </span>
        <a href="gestion_estudiantes.php" style="margin-left: 15px; font-size: 13px; font-weight: bold; color: #ffd54f;">Gestionar Estudiantes</a>
        <a href="logout.php" style="margin-left: 15px; font-size: 12px; opacity: 0.7;">(Salir)</a>
    </div>
</div>

<div class="main-container">