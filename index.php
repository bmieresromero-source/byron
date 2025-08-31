<?php
require_once 'config.php';
require_once 'functions.php';
$pdo = db();
$f_vendedor = $_GET['vendedor'] ?? '';
$f_producto = $_GET['producto'] ?? '';
$f_fecha    = $_GET['fecha'] ?? '';
$tab = $_GET['tab'] ?? 'actas';
$vendedores = $pdo->query("SELECT codigo, nombre FROM vendedores ORDER BY codigo")->fetchAll();
$inventario = $pdo->query("SELECT * FROM inventario ORDER BY producto")->fetchAll();
$where=[]; $params=[];
if($f_vendedor!==''){ $where[]="vendedor_codigo=?"; $params[]=$f_vendedor; }
if($f_producto!==''){ $where[]="producto=?"; $params[]=$f_producto; }
if($f_fecha!==''){ $where[]="fecha=?"; $params[]=$f_fecha; }
$sql_where = $where ? ("WHERE ".implode(" AND ", $where)) : "";
$actas = $pdo->prepare("SELECT * FROM actas $sql_where ORDER BY id ASC"); $actas->execute($params); $actas=$actas->fetchAll();
$vend_map = vendedores_map($pdo);
function vend_label($c,$map){ return isset($map[$c]) ? "$c - ".$map[$c] : $c; }
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>COILE · Gestión Documental</title>
<style>
body{font-family:system-ui,Segoe UI,Roboto,Arial,sans-serif;margin:0;background:#f6f7f9;color:#111}
header{display:flex;align-items:center;gap:12px;padding:12px;border-bottom:1px solid #e5e7eb;background:#fff;position:sticky;top:0;z-index:10}
header img{height:56px}
main{max-width:1100px;margin:0 auto;padding:12px}
.card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:12px;margin-top:12px;box-shadow:0 2px 6px rgba(0,0,0,.04)}
h1{font-size:20px;margin:0} h2{font-size:18px;margin:6px 0 8px} label{display:block;margin-top:8px;color:#334155}
input,select,textarea,button{padding:8px;border:1px solid #d1d5db;border-radius:10px;font-size:14px}
input,select,textarea{width:100%}
.row{display:flex;gap:8px;align-items:center} .row>*{flex:1}
.toolbar{display:flex;gap:8px;flex-wrap:wrap;margin-top:8px}
table{width:100%;border-collapse:collapse;margin-top:8px} th,td{border-bottom:1px solid #eee;padding:8px;text-align:left} th{background:#f3f4f6}
.actions form{display:inline}.badge{background:#eef2ff;border:1px solid #e0e7ff;border-radius:999px;padding:2px 8px;font-size:12px}
.tabs{display:flex;gap:6px;margin-top:8px}.tabs a{padding:8px 10px;border:1px solid #e5e7eb;border-radius:10px;background:#fff;text-decoration:none;color:#111}.tabs a.active{background:#111;color:#fff;border-color:#111}
.right{text-align:right}.small{font-size:12px;color:#64748b}
</style></head><body>
<header><img src="assets/coile.webp" alt="Coile"><div><h1>ACTA DE ENTREGA</h1><div class="small">COMERCIALIZADORA COILE S.A · AGENCIA SANTA ROSA</div></div></header>
<main>
<nav class="tabs">
  <a href="?tab=actas" class="<?= $tab==='actas'?'active':'' ?>">Actas</a>
  <a href="?tab=reporte" class="<?= $tab==='reporte'?'active':'' ?>">Reporte</a>
  <a href="?tab=inventario" class="<?= $tab==='inventario'?'active':'' ?>">Inventario</a>
  <a href="?tab=vendedores" class="<?= $tab==='vendedores'?'active':'' ?>">Vendedores</a>
</nav>

<?php if($tab==='actas'): ?>
<section class="card"><h2>Nueva / Editar Acta</h2>
<form action="save_acta.php" method="post">
  <input type="hidden" name="id" value="<?= htmlspecialchars($_GET['edit']??'') ?>">
  <?php $edit=null; if(isset($_GET['edit'])){ $st=$pdo->prepare("SELECT * FROM actas WHERE id=?"); $st->execute([intval($_GET['edit'])]); $edit=$st->fetch(); }
    $numero = $edit ? $edit['numero'] : numero_acta_for($pdo->query("SELECT COUNT(*) c FROM actas")->fetch()['c']);
    $fecha  = $edit ? $edit['fecha'] : date('Y-m-d'); ?>
  <div class="row">
    <div><label>N° Acta</label><input value="<?= htmlspecialchars($numero) ?>" readonly></div>
    <div><label>Fecha</label><input type="date" name="fecha" value="<?= htmlspecialchars($fecha) ?>" required></div>
  </div>
  <div class="row">
    <div><label>Código Cliente</label><input name="codigo_cliente" value="<?= htmlspecialchars($edit['codigo_cliente']??'') ?>"></div>
    <div><label>Nombre Cliente</label><input name="nombre_cliente" required value="<?= htmlspecialchars($edit['nombre_cliente']??'') ?>"></div>
  </div>
  <div class="row">
    <div><label>Cédula/RUC</label><input name="cedula_ruc" value="<?= htmlspecialchars($edit['cedula_ruc']??'') ?>"></div>
    <div><label>Vendedor</label>
      <select name="vendedor_codigo" required>
        <option value="">Seleccione</option>
        <?php foreach($vendedores as $v): $sel=($edit && $edit['vendedor_codigo']===$v['codigo'])?'selected':''; ?>
          <option <?=$sel?> value="<?= htmlspecialchars($v['codigo']) ?>"><?= htmlspecialchars($v['codigo'].' - '.$v['nombre']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>
  <div class="row">
    <div><label>Producto</label>
      <select name="producto" required>
        <option value="">Seleccione</option>
        <?php foreach($inventario as $p): $sel=($edit && $edit['producto']===$p['producto'])?'selected':''; ?>
          <option <?=$sel?> value="<?= htmlspecialchars($p['producto']) ?>"><?= htmlspecialchars($p['producto']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div><label>Cantidad</label><input type="number" name="cantidad" min="1" value="<?= htmlspecialchars($edit['cantidad']??'1') ?>" required></div>
  </div>
  <label>Descripción</label><textarea name="descripcion"><?= htmlspecialchars($edit['descripcion']??'') ?></textarea>
  <label>Recibido por</label><input name="recibido_por" value="<?= htmlspecialchars($edit['recibido_por']??'') ?>">
  <div class="toolbar"><button type="submit">Guardar</button><?php if($edit): ?><a class="badge" href="?tab=actas">Cancelar edición</a><?php endif; ?></div>
</form></section>

<section class="card"><h2>Listado de Actas</h2>
<form method="get" class="row">
  <input type="hidden" name="tab" value="actas">
  <div><label>Vendedor</label><select name="vendedor"><option value="">Todos</option>
    <?php foreach($vendedores as $v): ?><option value="<?= $v['codigo'] ?>" <?= $f_vendedor===$v['codigo']?'selected':'' ?>><?= htmlspecialchars($v['codigo'].' - '.$v['nombre']) ?></option><?php endforeach; ?>
  </select></div>
  <div><label>Producto</label><select name="producto"><option value="">Todos</option>
    <?php foreach($inventario as $p): ?><option value="<?= htmlspecialchars($p['producto']) ?>" <?= $f_producto===$p['producto']?'selected':'' ?>><?= htmlspecialchars($p['producto']) ?></option><?php endforeach; ?>
  </select></div>
  <div><label>Fecha</label><input type="date" name="fecha" value="<?= htmlspecialchars($f_fecha) ?>"></div>
  <div style="align-self:flex-end">
    <button type="submit">Filtrar</button>
    <a class="badge" href="?tab=actas">Limpiar</a>
    <a class="badge" href="export_pdf.php?scope=detalle&<?= http_build_query(['vendedor'=>$f_vendedor,'producto'=>$f_producto,'fecha'=>$f_fecha]) ?>" target="_blank">Exportar PDF</a>
  </div>
</form>
<table><thead><tr><th>N°</th><th>Fecha</th><th>Cliente</th><th>Vendedor</th><th>Producto</th><th class="right">Cantidad</th><th>Acciones</th></tr></thead>
<tbody>
<?php foreach($actas as $a): ?>
<tr>
  <td><?= htmlspecialchars($a['numero']) ?></td>
  <td><?= htmlspecialchars($a['fecha']) ?></td>
  <td><?= htmlspecialchars($a['nombre_cliente'].' ('.$a['codigo_cliente'].')') ?></td>
  <td><?= htmlspecialchars(vend_label($a['vendedor_codigo'],$vend_map)) ?></td>
  <td><?= htmlspecialchars($a['producto']) ?></td>
  <td class="right"><?= (int)$a['cantidad'] ?></td>
  <td class="actions">
    <a href="?tab=actas&edit=<?= $a['id'] ?>">Editar</a>
    <a href="print_acta.php?id=<?= $a['id'] ?>" target="_blank">Imprimir</a>
    <a href="delete_acta.php?id=<?= $a['id'] ?>" onclick="return confirm('¿Eliminar acta?')">Eliminar</a>
  </td>
</tr>
<?php endforeach; ?>
</tbody></table>
</section>
<?php endif; ?>

<?php if($tab==='reporte'): ?>
<section class="card"><h2>Reporte</h2>
<?php $sub = $_GET['sub'] ?? 'detalle';
function link_sub($s,$label){ $q=$_GET; $q['sub']=$s; $q['tab']='reporte'; $href='?'.http_build_query($q); $cls=($GLOBALS['sub']===$s)?'active':''; echo "<a class='$cls' href='$href'>$label</a>"; }?>
<div class="tabs"><?php link_sub('detalle','Detalle'); ?><?php link_sub('resumen','Resumen'); ?></div>
<form method="get" class="row" style="margin-top:8px">
  <input type="hidden" name="tab" value="reporte"><input type="hidden" name="sub" value="<?= htmlspecialchars($sub) ?>">
  <div><label>Vendedor</label><select name="vendedor"><option value="">Todos</option>
    <?php foreach($vendedores as $v): ?><option value="<?= $v['codigo'] ?>" <?= $f_vendedor===$v['codigo']?'selected':'' ?>><?= htmlspecialchars($v['codigo'].' - '.$v['nombre']) ?></option><?php endforeach; ?>
  </select></div>
  <div><label>Producto</label><select name="producto"><option value="">Todos</option>
    <?php foreach($inventario as $p): ?><option value="<?= htmlspecialchars($p['producto']) ?>" <?= $f_producto===$p['producto']?'selected':'' ?>><?= htmlspecialchars($p['producto']) ?></option><?php endforeach; ?>
  </select></div>
  <div><label>Fecha</label><input type="date" name="fecha" value="<?= htmlspecialchars($f_fecha) ?>"></div>
  <div style="align-self:flex-end">
    <button type="submit">Filtrar</button>
    <a class="badge" href="?tab=reporte&sub=<?= $sub ?>">Limpiar</a>
    <a class="badge" href="export_pdf.php?scope=<?= $sub ?>&<?= http_build_query(['vendedor'=>$f_vendedor,'producto'=>$f_producto,'fecha'=>$f_fecha]) ?>" target="_blank">Exportar PDF</a>
  </div>
</form>
<?php if($sub==='detalle'): ?>
<table><thead><tr><th>N°</th><th>Fecha</th><th>Cliente</th><th>Vendedor</th><th>Producto</th><th class="right">Cantidad</th><th>Descripción</th></tr></thead><tbody>
<?php foreach($actas as $a): ?><tr>
  <td><?= htmlspecialchars($a['numero']) ?></td>
  <td><?= htmlspecialchars($a['fecha']) ?></td>
  <td><?= htmlspecialchars($a['nombre_cliente'].' ('.$a['codigo_cliente'].')') ?></td>
  <td><?= htmlspecialchars(vend_label($a['vendedor_codigo'],$vend_map)) ?></td>
  <td><?= htmlspecialchars($a['producto']) ?></td>
  <td class="right"><?= (int)$a['cantidad'] ?></td>
  <td><?= htmlspecialchars($a['descripcion']) ?></td>
</tr><?php endforeach; ?></tbody></table>
<?php else: ?>
<?php $st=$pdo->prepare("SELECT vendedor_codigo, SUM(cantidad) total FROM actas $sql_where GROUP BY vendedor_codigo ORDER BY vendedor_codigo"); $st->execute($params); $sumVend=$st->fetchAll();
      $st=$pdo->prepare("SELECT producto, SUM(cantidad) total FROM actas $sql_where GROUP BY producto ORDER BY producto"); $st->execute($params); $sumProd=$st->fetchAll(); ?>
<div class="row">
  <div class="card" style="flex:1"><h3>Por vendedor</h3>
    <table><thead><tr><th>Vendedor</th><th class="right">Total</th></tr></thead><tbody>
      <?php foreach($sumVend as $r): $label=isset($vend_map[$r['vendedor_codigo']])?$r['vendedor_codigo'].' - '.$vend_map[$r['vendedor_codigo']]:$r['vendedor_codigo']; ?>
      <tr><td><?= htmlspecialchars($label) ?></td><td class="right"><?= (int)$r['total'] ?></td></tr><?php endforeach; ?>
    </tbody></table>
  </div>
  <div class="card" style="flex:1"><h3>Por producto</h3>
    <table><thead><tr><th>Producto</th><th class="right">Total</th></tr></thead><tbody>
      <?php foreach($sumProd as $r): ?><tr><td><?= htmlspecialchars($r['producto']) ?></td><td class="right"><?= (int)$r['total'] ?></td></tr><?php endforeach; ?>
    </tbody></table>
  </div>
</div>
<?php endif; ?>
</section>
<?php endif; ?>

<?php if($tab==='inventario'): ?>
<section class="card"><h2>Inventario</h2>
<form action="save_producto.php" method="post" class="row">
  <div><label>Producto</label><input name="producto" required placeholder="Nombre del producto"></div>
  <div><label>Stock inicial</label><input type="number" name="stock_inicial" min="0" value="0"></div>
  <div style="align-self:flex-end"><button type="submit">Agregar/Actualizar</button></div>
</form>
<table><thead><tr><th>Producto</th><th class="right">Inicial</th><th class="right">Entradas</th><th class="right">Salidas</th><th class="right">Stock actual</th></tr></thead><tbody>
<?php foreach($inventario as $p): $actual=$p['stock_inicial']+$p['entradas']-$p['salidas']; ?>
<tr><td><?= htmlspecialchars($p['producto']) ?></td><td class="right"><?= (int)$p['stock_inicial'] ?></td><td class="right"><?= (int)$p['entradas'] ?></td><td class="right"><?= (int)$p['salidas'] ?></td><td class="right"><b><?= (int)$actual ?></b></td></tr>
<?php endforeach; ?></tbody></table>
</section>
<?php endif; ?>

<?php if($tab==='vendedores'): ?>
<section class="card"><h2>Vendedores</h2>
<form action="save_vendedor.php" method="post" class="row">
  <div><label>Código</label><input name="codigo" required placeholder="V001"></div>
  <div><label>Nombre</label><input name="nombre" required placeholder="Nombre del vendedor"></div>
  <div style="align-self:flex-end"><button type="submit">Guardar</button></div>
</form>
<table><thead><tr><th>Código</th><th>Nombre</th></tr></thead><tbody>
<?php foreach($vendedores as $v): ?><tr><td><?= htmlspecialchars($v['codigo']) ?></td><td><?= htmlspecialchars($v['nombre']) ?></td></tr><?php endforeach; ?></tbody></table>
</section>
<?php endif; ?>

</main></body></html>
