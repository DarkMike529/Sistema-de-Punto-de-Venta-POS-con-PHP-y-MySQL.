<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: index.php");
    exit;
}

// SOLO ADMIN
if ($_SESSION["rol"] != "admin") {
    echo "Acceso restringido";
    exit;
}
?>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include("conexion.php");

// proteger acceso
if (!isset($_SESSION["usuario"])) {
    header("Location: index.php");
    exit;
}

// =======================
// AGREGAR / EDITAR
// =======================
if (isset($_POST["guardar"])) {

    $id = $_POST["id"] ?? null;
    $nombre = $_POST["nombre"];
    $codigo = $_POST["codigo"];
    $precio = $_POST["precio"];
    $stock = $_POST["stock"];

    if ($id) {
        // EDITAR
        $sql = "UPDATE productos SET
                nombre='$nombre',
                codigo_barras='$codigo',
                precio='$precio',
                stock='$stock'
                WHERE id=$id";
    } else {
        // INSERTAR
        $sql = "INSERT INTO productos (nombre, codigo_barras, precio, stock)
                VALUES ('$nombre', '$codigo', '$precio', '$stock')";
    }

    $conn->query($sql);

    header("Location: productos.php");
    exit;
}

// =======================
// ELIMINAR
// =======================
if (isset($_GET["eliminar"])) {
    $id = $_GET["eliminar"];
    $conn->query("DELETE FROM productos WHERE id=$id");

    header("Location: productos.php");
    exit;
}

// =======================
// EDITAR (CARGAR DATOS)
// =======================
$producto = null;

if (isset($_GET["editar"])) {
    $id = $_GET["editar"];
    $res = $conn->query("SELECT * FROM productos WHERE id=$id");
    $producto = $res->fetch_assoc();
}

?>

<h2>Inventario</h2>

<!-- ======================= -->
<!-- FORMULARIO -->
<!-- ======================= -->
<form method="POST">

    <input type="hidden" name="id" value="<?php echo $producto['id'] ?? ''; ?>">

    <input name="nombre" placeholder="Nombre"
        value="<?php echo $producto['nombre'] ?? ''; ?>">

    <input name="codigo" placeholder="Código de barras"
        value="<?php echo $producto['codigo_barras'] ?? ''; ?>">

    <input name="precio" placeholder="Precio"
        value="<?php echo $producto['precio'] ?? ''; ?>">

    <input name="stock" placeholder="Stock"
        value="<?php echo $producto['stock'] ?? ''; ?>">

    <button name="guardar">
        <?php echo isset($producto) ? "Actualizar" : "Guardar"; ?>
    </button>

</form>

<hr>

<!-- ======================= -->
<!-- TABLA -->
<!-- ======================= -->
<table border="1">
<tr>
    <th>ID</th>
    <th>Nombre</th>
    <th>Código</th>
    <th>Precio</th>
    <th>Stock</th>
    <th>Acciones</th>
</tr>

<?php
$result = $conn->query("SELECT * FROM productos");

while ($row = $result->fetch_assoc()) {
    echo "<tr>
        <td>{$row['id']}</td>
        <td>{$row['nombre']}</td>
        <td>{$row['codigo_barras']}</td>
        <td>{$row['precio']}</td>
        <td>{$row['stock']}</td>
        <td>
            <a href='?editar={$row['id']}'>Editar</a>
            <a href='?eliminar={$row['id']}'>Eliminar</a>
        </td>
    </tr>";
}
?>

</table>
