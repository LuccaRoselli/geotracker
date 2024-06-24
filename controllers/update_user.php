<?php
session_start();

// Conectando ao banco de dados
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "geotracker";

// Cria a conexão
$conection_db = new mysqli($servername, $username, $password, $dbname);

// Verifica a conexão
if ($conection_db->connect_error) {
    die("Connection failed: " . $conection_db->connect_error);
}

// Define variables and initialize with empty values
$name_err = $image_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Prepare an update statement
    $sql = "UPDATE users SET name = ?, image = ?, user_type = ? WHERE id = ?";

    if ($stmt = mysqli_prepare($conection_db, $sql)) {
        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($stmt, "ssii", $param_name, $param_image, $param_user_type, $param_id);

        // Set parameters
        $param_name = trim($_POST["name"]);
        $param_image = trim($_POST["image"]);
        $param_id = $_POST["id"];
        $param_user_type = $_POST["user_type"];

        // Attempt to execute the prepared statement
        if (mysqli_stmt_execute($stmt)) {
            // Atualiza as informações na sessão se o usuário editado é o usuário logado
            if ($_SESSION['id'] == $param_id) {
                $_SESSION['name'] = $param_name;
                $_SESSION['image'] = $param_image;
                $_SESSION['user_type'] = $param_user_type;
            }
// Define variável de sessão para notificação de sucesso
$_SESSION['update_success'] = true;

            // Redirect to main page
            header("location: ../users");
            exit();
        } else {
            echo "Ocorreu um erro! Tente novamente.";
        }

        // Close statement
        mysqli_stmt_close($stmt);
    }

    // Close connection
    mysqli_close($conection_db);
}
?>