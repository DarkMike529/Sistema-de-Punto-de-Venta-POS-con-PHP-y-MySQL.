<?php
session_start();
include("conexion.php");

$id = $_GET["id"];

// obtener venta
$venta = $conn->query("SELECT * FROM ventas WHERE id=$id")->fetch_assoc();

// obtener detalle
$detalle = $conn->query("
    SELECT * FROM ventas_detalle WHERE venta_id=$id
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Ticket</title>

    <style>
        body {
            font-family: monospace;
            text-align: center;
        }

        .ticket {
            width: 250px;
    	    font-size: 12px;
            margin: auto;
            border: 1px dashed black;
            padding: 10px;
        }

        .no-print {
            margin-top: 10px;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>

<div class="ticket">
    <h3>🛒 MI TIENDA</h3>
    <p>Fecha: <?php echo $venta["fecha"]; ?></p>
    <hr>

    <?php
    while ($row = $detalle->fetch_assoc()) {
        echo "<p>{$row['producto']} - $ {$row['precio']}</p>";
    }
    ?>

    <hr>
    <h3>Total: $<?php echo $venta["total"]; ?></h3>
    <p>Gracias por su compra 🙌</p>
</div>

<div class="no-print">
    <button onclick="window.print()">Imprimir</button>
    <br><br>
    <a href="ventas.php">⬅ Volver</a>
</div>

</body>
</html>
