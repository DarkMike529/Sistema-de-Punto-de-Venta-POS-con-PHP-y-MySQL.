<?php
session_start();
include("conexion.php");

// 🔒 Solo admin
if (!isset($_SESSION["rol"]) || $_SESSION["rol"] != "admin") {
    echo "Acceso denegado";
    exit;
}

// 🗑️ ELIMINAR USUARIO (POST más seguro)
if (isset($_POST["eliminar"])) {
    $id = intval($_POST["eliminar"]);

    $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "Usuario eliminado ✅<br><br>";
    } else {
        echo "Error al eliminar ❌<br><br>";
    }
}
?>

<h2>Panel de Usuarios</h2>

<a href="registro.php">➕ Crear nuevo usuario</a><br><br>

<table border="1" cellpadding="10">
<tr>
    <th>ID</th>
    <th>Nombre</th>
    <th>Correo</th>
    <th>Rol</th>
    <th>Acciones</th>
</tr>

<?php
$result = $conn->query("SELECT * FROM usuarios");

while ($row = $result->fetch_assoc()) {
?>
<tr>
    <td><?php echo $row["id"]; ?></td>
    <td><?php echo $row["nombre"]; ?></td>
    <td><?php echo $row["correo"]; ?></td>
    <td><?php echo $row["rol"]; ?></td>
    <td>

        <!-- EDITAR -->
        <a href="editar_usuario.php?id=<?php echo $row["id"]; ?>">✏️</a>

        <!-- ELIMINAR -->
        <form method="POST" style="display:inline;">
            <input type="hidden" name="eliminar" value="<?php echo $row["id"]; ?>">
            <button onclick="return confirm('¿Eliminar usuario?')">🗑️</button>
        </form>

    </td>
</tr>
<?php
}
?>
</table>
