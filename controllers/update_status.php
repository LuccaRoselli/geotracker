

<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "geotracker";

// Crie uma conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verifique a conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST['id']);
    $status = intval($_POST['status']);

    $sql = "UPDATE ocorrencias SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        echo "Erro ao preparar statement: " . $conn->error;
        exit;
    }

    $stmt->bind_param("ii", $status, $id);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Erro ao atualizar status: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>