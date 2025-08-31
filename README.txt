COILE · Gestión Documental (PHP + MySQL)
- Numeración secuencial ACTA-001…, resecuencia al eliminar, conserva número al editar.
- Reporte separado: Detalle y Resumen (filtros por vendedor, producto, fecha).
- Inventario con recálculo automático (salidas desde actas).
- Vendedores (CRUD).
- Exportación a PDF con Dompdf si está disponible; si no, impresión del navegador.
Instalación: importe db.sql, configure config.php y abra index.php.
Para Dompdf: `composer require dompdf/dompdf` o coloque Dompdf en ./vendor con autoload.php.
