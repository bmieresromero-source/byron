<?php
require_once 'functions.php';
$pdo = db();
$producto = trim($_POST['producto'] ?? '');
$stock = (int)($_POST['stock_inicial'] ?? 0);
if($producto!==''){ $st=$pdo->prepare("INSERT INTO inventario (producto, stock_inicial) VALUES (?,?) ON DUPLICATE KEY UPDATE stock_inicial=VALUES(stock_inicial)"); $st->execute([$producto,$stock]); }
header('Location: index.php?tab=inventario');