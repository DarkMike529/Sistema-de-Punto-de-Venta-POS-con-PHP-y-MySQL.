<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: index.php");
    exit;
}

if (!in_array($_SESSION["rol"], ["admin", "empleado"])) {
    echo "⛔ Sin permisos";
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

include("conexion.php");

// iniciar carrito
if (!isset($_SESSION["carrito"])) {
    $_SESSION["carrito"] = [];
}

//ELIMINAR
if (isset($_GET["eliminar"])) {

    $id = $_GET["eliminar"];

    foreach ($_SESSION["carrito"] as $key => $item) {

        if ($item["id"] == $id) {

            if ($item["cantidad"] > 1) {
                $_SESSION["carrito"][$key]["cantidad"]--;
            } else {
                unset($_SESSION["carrito"][$key]);
            }

            break;
        }
    }

    header("Location: ventas.php");
    exit;
}

// =======================
// AGREGAR POR CODIGO
// =======================
if (isset($_POST["codigo"])) {

    $codigo = $_POST["codigo"];

    $res = $conn->query("SELECT * FROM productos WHERE codigo_barras = '$codigo'");
    $producto = $res->fetch_assoc();

    if (!$producto) {
        echo "<p style='color:red'>Producto no existe</p>";

    } elseif ($producto["stock"] <= 0) {
        echo "<p style='color:red'>Sin stock disponible</p>";

    } else {

        $existe = false;

        foreach ($_SESSION["carrito"] as &$item) {
            if ($item["id"] == $producto["id"]) {
                $item["cantidad"]++;
                $existe = true;
                break;
            }
        }

        if (!$existe) {
            $_SESSION["carrito"][] = [
                "id" => $producto["id"],
                "nombre" => $producto["nombre"],
                "precio" => $producto["precio"],
                "cantidad" => 1
            ];
        }

        header("Location: ventas.php");
        exit;
    }
}

// =======================
// LIMPIAR CARRITO
// =======================
if (isset($_GET["clear"])) {
    $_SESSION["carrito"] = [];
    header("Location: ventas.php");
    exit;
}

// =======================
// FINALIZAR VENTA
// =======================
if (isset($_POST["finalizar"])) {

    if (empty($_SESSION["carrito"])) {
        echo "<p style='color:red'>Carrito vacío</p>";

    } else {

        $total = 0;

        foreach ($_SESSION["carrito"] as $item) {
            $total += $item["precio"] * $item["cantidad"];
        }

        // guardar venta
        $conn->query("INSERT INTO ventas (total) VALUES ($total)");
        $venta_id = $conn->insert_id;

        foreach ($_SESSION["carrito"] as $item) {

            $id = $item["id"];
            $cantidad = $item["cantidad"];

            // verificar stock
            $check = $conn->query("SELECT stock FROM productos WHERE id = $id");
            $data = $check->fetch_assoc();

            if ($data["stock"] < $cantidad) {
                echo "<p style='color:red'>Stock insuficiente: {$item["nombre"]}</p>";
                continue;
            }

            // restar stock correctamente
            $conn->query("
                UPDATE productos 
                SET stock = stock - $cantidad 
                WHERE id = $id
            ");

            // guardar detalle
            for ($i = 0; $i < $cantidad; $i++) {
                $conn->query("
                    INSERT INTO ventas_detalle (venta_id, producto, precio)
                    VALUES ($venta_id, '{$item["nombre"]}', {$item["precio"]})
                ");
            }
        }

        $_SESSION["carrito"] = [];

        header("Location: ticket.php?id=$venta_id");
        exit;
    }
}
?>

<!-- ======================= -->
<!-- SCANNER -->
<!-- ======================= -->

<script src="https://unpkg.com/html5-qrcode"></script>

<h2>VENTAS (POS)</h2>

<button onclick="iniciarCamara()">📷 Activar cámara</button>

<div id="reader" style="width:300px;"></div>

<!-- INPUT MANUAL (IMPORTANTE 🔥) -->
<form method="POST" id="formScan">
    <input id="codigo" name="codigo" autofocus 
           placeholder="Escanear o escribir código">
</form>

<a href="?clear=1">Vaciar carrito</a>

<h3>Carrito</h3>

<table border="1">
<tr>
    <th>Acción</th>
    <th>Producto</th>
    <th>Cantidad</th>
    <th>Precio</th>
</tr>

<?php
$total = 0;

foreach ($_SESSION["carrito"] as $p) {

    $subtotal = $p["precio"] * $p["cantidad"];
    $total += $subtotal;

    echo "<tr>
    <td><a href='?eliminar={$p['id']}'>X</a></td>
    <td>{$p['nombre']}</td>
    <td>{$p['cantidad']}</td>
    <td>$subtotal</td>
</tr>";
}
?>

</table>

<form method="POST">
    <button name="finalizar">Finalizar venta</button>
</form>

<h3>Total: $<?php echo $total; ?></h3>

<audio id="beep" src="https://www.soundjay.com/buttons/sounds/beep-07.mp3"></audio>

<script>
function beep() {
    document.getElementById("beep").play();
}

// ESCANER CAMARA
function iniciarCamara() {
    const scanner = new Html5Qrcode("reader");

    scanner.start(
        { facingMode: "environment" },
        { fps: 10, qrbox: 250 },
        (decodedText) => {
            document.getElementById("codigo").value = decodedText;
            beep();
            document.getElementById("formScan").submit();
        }
    );
}

// ENTER (scanner físico o teclado)
document.getElementById("codigo").addEventListener("keypress", function(e) {
    if (e.key === "Enter") {
        e.preventDefault();
        beep();
        document.getElementById("formScan").submit();
    }
});
</script>