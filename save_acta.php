<?php
require_once 'functions.php';
$pdo = db();
$id = $_POST['id'] ?? '';
$fecha = $_POST['fecha'] ?? date('Y-m-d');
$codigo_cliente = $_POST['codigo_cliente'] ?? '';
$nombre_cliente = $_POST['nombre_cliente'] ?? '';
$cedula = $_POST['cedula_ruc'] ?? '';
$vendedor = $_POST['vendedor_codigo'] ?? '';
$producto = $_POST['producto'] ?? '';
$cantidad = max(0, (int)($_POST['cantidad'] ?? 0));
$descripcion = $_POST['descripcion'] ?? '';
$recibido_por = $_POST['recibido_por'] ?? '';
if(!$nombre_cliente || !$vendedor || !$producto || $cantidad<=0){ header('Location: index.php?tab=actas'); exit; }
if($id===''){
  $st=$pdo->prepare("INSERT INTO actas (numero,fecha,codigo_cliente,nombre_cliente,cedula_ruc,vendedor_codigo,producto,cantidad,descripcion,recibido_por) VALUES ('PEND',?,?,?,?,?,?,?,?,?)");
  $st->execute([$fecha,$codigo_cliente,$nombre_cliente,$cedula,$vendedor,$producto,$cantidad,$descripcion,$recibido_por]);
  resecuenciar_actas($pdo);
}else{
  $st=$pdo->prepare("UPDATE actas SET fecha=?, codigo_cliente=?, nombre_cliente=?, cedula_ruc=?, vendedor_codigo=?, producto=?, cantidad=?, descripcion=?, recibido_por=? WHERE id=?");
  $st->execute([$fecha,$codigo_cliente,$nombre_cliente,$cedula,$vendedor,$producto,$cantidad,$descripcion,$recibido_por,$id]);
}
recompute_inventario($pdo);
header('Location: index.php?tab=actas');