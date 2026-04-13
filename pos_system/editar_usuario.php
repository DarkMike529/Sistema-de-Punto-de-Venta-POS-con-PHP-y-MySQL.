<?php
session_start();
include("conexion.php");

if ($_SESSION["rol"] != "admin") {
    echo "Acceso denegado";
    exit;
}

$id = intval($_GET["id"]);

// Obtener datos actuales
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre = $_POST["nombre"];
    $correo = $_POST["correo"];
    $rol = $_POST["rol"];

    $stmt = $conn->prepare("UPDATE usuarios SET nombre=?, correo=?, rol=? WHERE id=?");
    $stmt->bind_param("sssi", $nombre, $correo, $rol, $id);

    if ($stmt->execute()) {
        echo "Usuario actualizado";
    } else {
        echo "Error";
    }
}
?>

<h2>Editar Usuario</h2>

<form method="POST">
    <input name="nombre" value="<?php echo $user["nombre"]; ?>" required><br><br>
    <input name="correo" value="<?php echo $user["correo"]; ?>" required><br><br>

    <select name="rol">
        <option value="admin" <?php if($user["rol"]=="admin") echo "selected"; ?>>Admin</option>
        <option value="empleado" <?php if($user["rol"]=="empleado") echo "selected"; ?>>Empleado</option>
    </select><br><br>

    <button>Guardar cambios</button>
</form>