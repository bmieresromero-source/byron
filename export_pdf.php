<?php
require_once 'config.php';
require_once 'functions.php';
$pdo = db();
$scope = $_GET['scope'] ?? 'detalle';
$f_vendedor = $_GET['vendedor'] ?? '';
$f_producto = $_GET['producto'] ?? '';
$f_fecha    = $_GET['fecha'] ?? '';
$where=[]; $params=[];
if($f_vendedor!==''){ $where[]="vendedor_codigo=?"; $params[]=$f_vendedor; }
if($f_producto!==''){ $where[]="producto=?"; $params[]=$f_producto; }
if($f_fecha!==''){ $where[]="fecha=?"; $params[]=$f_fecha; }
$sql_where = $where ? ("WHERE ".implode(" AND ", $where)) : "";
$vend_map = vendedores_map($pdo);
ob_start(); ?>
<!doctype html><html><head><meta charset="utf-8"><style>
body{font-family:DejaVu Sans, Arial, sans-serif; font-size:11px; color:#111}
h2{margin:0 0 6px 0} .table{width:100%;border-collapse:collapse}
th,td{border:1px solid #ddd;padding:6px;text-align:left} .right{text-align:right} .small{color:#64748b}
.header{display:flex;align-items:center;gap:12px;margin-bottom:8px}.header img{height:48px}
</style></head><body>
<div class="header"><img src="assets/coile.webp"><div>
<h2>Reporte <?= htmlspecialchars(ucfirst($scope)) ?></h2>
<div class="small">Filtros: Vendedor=<?= htmlspecialchars($f_vendedor?:'Todos') ?>, Producto=<?= htmlspecialchars($f_producto?:'Todos') ?>, Fecha=<?= htmlspecialchars($f_fecha?:'Todas') ?></div>
</div></div>
<?php if($scope==='detalle'): ?>
<table class="table"><thead><tr><th>N°</th><th>Fecha</th><th>Cliente</th><th>Vendedor</th><th>Producto</th><th>Cantidad</th><th>Descripción</th></tr></thead><tbody>
<?php $st=$pdo->prepare("SELECT * FROM actas $sql_where ORDER BY id ASC"); $st->execute($params); foreach($st as $a){ $vend = isset($vend_map[$a['vendedor_codigo']]) ? $a['vendedor_codigo'].' - '.$vend_map[$a['vendedor_codigo']] : $a['vendedor_codigo']; echo '<tr>'; echo '<td>'.htmlspecialchars($a['numero']).'</td>'; echo '<td>'.htmlspecialchars($a['fecha']).'</td>'; echo '<td>'.htmlspecialchars($a['nombre_cliente'].' ('.$a['codigo_cliente'].')').'</td>'; echo '<td>'.htmlspecialchars($vend).'</td>'; echo '<td>'.htmlspecialchars($a['producto']).'</td>'; echo '<td class=right>'.(int)$a['cantidad'].'</td>'; echo '<td>'.htmlspecialchars($a['descripcion']).'</td>'; echo '</tr>'; } ?>
</tbody></table>
<?php else: ?>
<?php $st=$pdo->prepare("SELECT vendedor_codigo, SUM(cantidad) total FROM actas $sql_where GROUP BY vendedor_codigo ORDER BY vendedor_codigo"); $st->execute($params); $sumVend=$st->fetchAll();
      $st=$pdo->prepare("SELECT producto, SUM(cantidad) total FROM actas $sql_where GROUP BY producto ORDER BY producto"); $st->execute($params); $sumProd=$st->fetchAll(); ?>
<h3>Resumen por vendedor</h3>
<table class="table"><thead><tr><th>Vendedor</th><th class="right">Total</th></tr></thead><tbody>
<?php foreach($sumVend as $r): $label = isset($vend_map[$r['vendedor_codigo']]) ? $r['vendedor_codigo'].' - '.$vend_map[$r['vendedor_codigo']] : $r['vendedor_codigo']; ?>
<tr><td><?= htmlspecialchars($label) ?></td><td class="right"><?= (int)$r['total'] ?></td></tr><?php endforeach; ?>
</tbody></table>
<h3>Resumen por producto</h3>
<table class="table"><thead><tr><th>Producto</th><th class="right">Total</th></tr></thead><tbody>
<?php foreach($sumProd as $r): ?><tr><td><?= htmlspecialchars($r['producto']) ?></td><td class="right"><?= (int)$r['total'] ?></td></tr><?php endforeach; ?>
</tbody></table>
<?php endif; ?>
</body></html>
<?php $html=ob_get_clean();
if(has_dompdf()){ $dompdf=new \Dompdf\Dompdf(); $dompdf->loadHtml($html); $dompdf->setPaper('A4','portrait'); $dompdf->render(); $dompdf->stream("reporte_".$scope.".pdf"); exit; } else { echo $html; echo "<script>window.print()</script>"; }