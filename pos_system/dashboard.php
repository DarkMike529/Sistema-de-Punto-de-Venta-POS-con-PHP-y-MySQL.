<h3>Menú</h3>

<?php
session_start();
include("conexion.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<?php if ($_SESSION["rol"] == "admin"): ?>
    <a href="productos.php">Productos</a><br>
    <a href="dashboard.php">Dashboard</a><br>
<?php endif; ?>

<a href="ventas.php">Ventas</a><br>
<a href="logout.php">Salir</a>
<?php

// PROTEGER ACCESO
if (!isset($_SESSION["usuario"])) {
    header("Location: index.php");
    exit;
}

// total de ventas
$resTotal = $conn->query("SELECT SUM(total) as total FROM ventas");
$totalRow = $resTotal->fetch_assoc();
$totalGeneral = $totalRow["total"] ?? 0;

// datos para gráfica
$res = $conn->query("
    SELECT DATE(fecha) as dia, SUM(total) as total
    FROM ventas
    GROUP BY DATE(fecha)
    ORDER BY dia ASC
");

$fechas = [];
$totales = [];

while ($row = $res->fetch_assoc()) {
    $fechas[] = $row["dia"];
    $totales[] = $row["total"];
}
?>

<h1>Dashboard POS</h1>
<?php
// total de ventas
$resTotal = $conn->query("SELECT SUM(total) as total FROM ventas");
$totalGeneral = $resTotal->fetch_assoc()["total"] ?? 0;

// número de ventas
$resCount = $conn->query("SELECT COUNT(*) as total FROM ventas");
$totalVentas = $resCount->fetch_assoc()["total"] ?? 0;
?>

<div style="display:flex; gap:20px;">
    <div style="background:#4CAF50;color:white;padding:10px;">
        Total vendido: $<?php echo $totalGeneral; ?>
    </div>

    <div style="background:#2196F3;color:white;padding:10px;">
        Ventas realizadas: <?php echo $totalVentas; ?>
    </div>
</div>
<p>Bienvenido: <?php echo $_SESSION["usuario"]; ?></p>
<p>Rol: <?php echo $_SESSION["rol"]; ?></p>

<h2>Total vendido: $<?php echo $totalGeneral; ?></h2>
<?php
$resProd = $conn->query("
SELECT producto, COUNT(*) as cantidad
FROM ventas_detalle
GROUP BY producto
ORDER BY cantidad DESC
LIMIT 5
");

$productos = [];
$cantidades = [];

while ($row = $resProd->fetch_assoc()) {
    $productos[] = $row["producto"];
    $cantidades[] = $row["cantidad"];
}
?>
<canvas id="grafica"></canvas>
<canvas id="graficaProductos"></canvas>
<script>
const ctx2 = document.getElementById('graficaProductos');

new Chart(ctx2, {
    type: 'pie',
    data: {
        labels: <?php echo json_encode($productos); ?>,
        datasets: [{
            data: <?php echo json_encode($cantidades); ?>
        }]
    }
});
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const ctx = document.getElementById('grafica');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($fechas); ?>,
        datasets: [{
            label: 'Ventas por día',
            data: <?php echo json_encode($totales); ?>
        }]
    }
});
</script>

<h2>Productos más vendidos</h2>

<?php
$result = $conn->query("
SELECT 
    producto,
    COUNT(*) as cantidad
FROM ventas_detalle
GROUP BY producto
ORDER BY cantidad DESC
LIMIT 5
");

while ($row = $result->fetch_assoc()) {
    echo "<p>{$row['producto']} | ventas: {$row['cantidad']}</p>";
}
?>

<h2>Alertas de stock bajo</h2>

<?php
$result = $conn->query("
SELECT nombre, stock 
FROM productos
WHERE stock <= 5
ORDER BY stock ASC
");

if ($result->num_rows == 0) {
    echo "<p>Todo bien, no hay productos bajos</p>";
} else {
    while ($row = $result->fetch_assoc()) {
        echo "<p style='color:red'>
            {$row['nombre']} | stock: {$row['stock']}
        </p>";
    }
}
?>
