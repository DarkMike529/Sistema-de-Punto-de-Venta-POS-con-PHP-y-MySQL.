<?php
session_start();
include("conexion.php");

// Solo admin puede registrar usuarios
if (!isset($_SESSION["rol"]) || $_SESSION["rol"] != "admin") {
    echo "Acceso denegado";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre = trim($_POST["nombre"]);
    $correo = trim($_POST["correo"]);
    $password = trim($_POST["password"]);
    $rol = $_POST["rol"];

    // Validación básica
    if (empty($nombre) || empty($correo) || empty($password)) {
        echo "Todos los campos son obligatorios";
        exit;
    }

    // Validar formato de correo
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        echo "Correo no válido";
        exit;
    }

    // Verificar si el correo ya existe
    $check = $conn->prepare("SELECT id FROM usuarios WHERE correo = ?");
    $check->bind_param("s", $correo);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo "Este correo ya está registrado";
        exit;
    }

    // Encriptar contraseña
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Insertar usuario
    $stmt = $conn->prepare("INSERT INTO usuarios (nombre, correo, password, rol) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nombre, $correo, $passwordHash, $rol);

    if ($stmt->execute()) {
        echo "Usuario creado correctamente";
    } else {
        echo "Error al crear usuario";
    }

    $stmt->close();
}
?>

<form method="POST">
    <input name="nombre" placeholder="Nombre" required><br><br>
    <input name="correo" type="email" placeholder="Correo" required><br><br>
    <input type="password" name="password" placeholder="Contraseña" required><br><br>

    <select name="rol">
        <option value="admin">Admin</option>
        <option value="empleado">Empleado</option>
    </select><br><br>

    <button>Registrar</button>
</form>