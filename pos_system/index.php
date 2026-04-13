<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include("conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Sanitizar entradas
    $correo = trim($_POST["correo"]);
    $password = trim($_POST["password"]);

    if (empty($correo) || empty($password)) {
        echo "Todos los campos son obligatorios";
        exit;
    }

    // Prepared Statement (ANTI SQL INJECTION)
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {

        $user = $resultado->fetch_assoc();

        // Verificar contraseña segura
        if (password_verify($password, $user["password"])) {

            // Regenerar sesión (evita secuestro de sesión)
            session_regenerate_id(true);

            $_SESSION["usuario"] = $user["nombre"];
            $_SESSION["rol"] = $user["rol"];

            header("Location: dashboard.php");
            exit;

        } else {
            echo "Contraseña incorrecta";
        }

    } else {
        echo "Usuario no encontrado";
    }

    $stmt->close();
}
?>

<form method="POST">
    <input name="correo" type="email" placeholder="Correo" required>
    <input type="password" name="password" placeholder="Contraseña" required>
    <button>Entrar</button>
</form>