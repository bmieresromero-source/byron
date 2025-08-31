<?php
require_once 'functions.php';
$pdo = db();
$id = (int)($_GET['id'] ?? 0);
if($id>0){ $pdo->prepare("DELETE FROM actas WHERE id=?")->execute([$id]); resecuenciar_actas($pdo); recompute_inventario($pdo); }
header('Location: index.php?tab=actas');