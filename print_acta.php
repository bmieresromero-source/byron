<?php
require_once 'config.php';
require_once 'functions.php';
$pdo = db();
$id = (int)($_GET['id'] ?? 0);
$st = $pdo->prepare("SELECT * FROM actas WHERE id=?");
$st->execute([$id]);
$a = $st->fetch();
if(!$a){ header('Location: index.php?tab=actas'); exit; }
ob_start(); ?>
<!doctype html><html><head><meta charset="utf-8"><style>
body{font-family:DejaVu Sans, Arial, sans-serif; font-size:12px; color:#111}
.header{display:flex; align-items:center; gap:12px; border-bottom:1px solid #ddd; padding-bottom:8px}
.header img{height:60px}
.table{width:100%;border-collapse:collapse;margin-top:8px}.table td{padding:6px;vertical-align:top}
.box{border:1px solid #ddd; border-radius:6px; padding:8px; margin-top:8px}.center{text-align:center}.right{text-align:right}
</style></head><body>
<div class="header"><img src="assets/coile.webp"><div><h2 style="margin:0">ACTA DE ENTREGA</h2><div>COMERCIALIZADORA COILE S.A · AGENCIA SANTA ROSA</div></div>
<div style="margin-left:auto; text-align:right"><div><strong><?= htmlspecialchars($a['numero']) ?></strong></div><div><?= htmlspecialchars($a['fecha']) ?></div></div></div>
<table class="table">
<tr><td><strong>Cliente</strong></td><td><?= htmlspecialchars($a['nombre_cliente']) ?> (<?= htmlspecialchars($a['codigo_cliente']) ?>)</td></tr>
<tr><td><strong>Cédula/RUC</strong></td><td><?= htmlspecialchars($a['cedula_ruc']) ?></td></tr>
<tr><td><strong>Vendedor</strong></td><td><?= htmlspecialchars($a['vendedor_codigo']) ?></td></tr>
</table>
<div class="box"><strong>Detalle</strong><br>Producto: <?= htmlspecialchars($a['producto']) ?><br>Cantidad: <?= (int)$a['cantidad'] ?><br>Descripción: <?= htmlspecialchars($a['descripcion']) ?></div>
<table class="table" style="margin-top:20px"><tr><td class="center"><div>Entregado por</div><div><strong>Comercializadora COILE S.A</strong></div></td><td class="center"><div>Recibido por</div><div><strong><?= htmlspecialchars($a['recibido_por']) ?></strong></div></td></tr></table>
</body></html>
<?php $html=ob_get_clean();
if(has_dompdf()){ $dompdf=new \Dompdf\Dompdf(); $dompdf->loadHtml($html); $dompdf->setPaper('A4','portrait'); $dompdf->render(); $dompdf->stream($a['numero'].".pdf"); exit; } else { echo $html; echo "<script>window.print()</script>"; }