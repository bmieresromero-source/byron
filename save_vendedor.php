<?php
require_once 'functions.php';
$pdo = db();
$codigo = trim($_POST['codigo'] ?? '');
$nombre = trim($_POST['nombre'] ?? '');
if($codigo!=='' && $nombre!==''){ $st=$pdo->prepare("INSERT INTO vendedores (codigo, nombre) VALUES (?,?) ON DUPLICATE KEY UPDATE nombre=VALUES(nombre)"); $st->execute([$codigo,$nombre]); }
header('Location: index.php?tab=vendedores');