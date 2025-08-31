<?php
require_once 'config.php';
function numero_acta_for($i){ return 'ACTA-'.str_pad($i+1,3,'0',STR_PAD_LEFT); }
function resecuenciar_actas(PDO $pdo){ $rows=$pdo->query("SELECT id FROM actas ORDER BY id ASC")->fetchAll(); $pdo->beginTransaction(); try{ foreach($rows as $i=>$r){ $numero=numero_acta_for($i); $st=$pdo->prepare("UPDATE actas SET numero=? WHERE id=?"); $st->execute([$numero,$r['id']]); } $pdo->commit(); }catch(Exception $e){ $pdo->rollBack(); throw $e; } }
function recompute_inventario(PDO $pdo){ $pdo->exec("UPDATE inventario SET salidas=0"); $rows=$pdo->query("SELECT producto, SUM(cantidad) total FROM actas GROUP BY producto")->fetchAll(); $st=$pdo->prepare("UPDATE inventario SET salidas=? WHERE producto=?"); foreach($rows as $r){ $st->execute([(int)$r['total'],$r['producto']]); } }
function vendedores_map(PDO $pdo){ $map=[]; foreach($pdo->query("SELECT codigo, nombre FROM vendedores") as $v){ $map[$v['codigo']]=$v['nombre']; } return $map; }
