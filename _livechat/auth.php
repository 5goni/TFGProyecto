<?php
session_start();
include '../conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? null;
    $nombre = trim($_POST['nombre'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($action === 'login') {
        // Login
        if (empty($nombre) || empty($password)) {
            $_SESSION['error'] = 'Por favor completa todos los campos';
            header('Location: ../index.php');
            exit;
        }

        // CORRECCIÓN: Se cambió 'username' por 'nombre' según la estructura de la tabla
        $stmt = $conn->prepare("SELECT id, password FROM usuarios WHERE nombre = ?");
        $stmt->bind_param("s", $nombre);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $nombre;
                header('Location: ../index.php');
                exit;
            } else {
                $_SESSION['error'] = 'Contraseña incorrecta';
            }
        } else {
            $_SESSION['error'] = 'Usuario no encontrado';
        }
        header('Location: ../index.php');
        exit;

    } elseif ($action === 'register') {
        // Register
        $email = trim($_POST['email'] ?? '');
        if (empty($nombre) || empty($password) || empty($email)) {
            $_SESSION['error'] = 'Por favor completa todos los campos';
            header('Location: ../index.php');
            exit;
        }

        // Verificar si el nombre o email ya existen
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE nombre = ? OR email = ?");
        $stmt->bind_param("ss", $nombre, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $_SESSION['error'] = 'El usuario o correo electrónico ya existe';
            header('Location: ../index.php');
            exit;
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nombre, $email, $hashedPassword);

        if ($stmt->execute()) {
            $_SESSION['user_id'] = $stmt->insert_id;
            $_SESSION['username'] = $nombre;
            header('Location: ../index.php');
            exit;
        } else {
            $_SESSION['error'] = 'Error al crear la cuenta';
        }
        header('Location: ../index.php');
        exit;
    }
}

header('Location: ../index.php');
exit;
?>