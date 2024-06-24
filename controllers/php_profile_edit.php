<?php
// Define variables and initialize with empty values
$password_err = $confirm_password_err = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST")
{


    // Prepare an update statement
    $sql = "UPDATE users SET name = ?, image = ? WHERE email = ?";

    if($stmt = mysqli_prepare($conection_db, $sql))
    {
        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($stmt, "sss", $param_name, $param_image, $param_email);

        // Set parameters
        $param_name = trim($_POST["nameInput"]);
        $param_image = trim($_POST["imageInput"]);
        $param_email = $_SESSION['email'];
       

        // Attempt to execute the prepared statement
        if(mysqli_stmt_execute($stmt))
        {
            $_SESSION['name'] = trim($_POST["nameInput"]);
            $_SESSION['image'] = trim($_POST["imageInput"]);
            // Redirect to index page
            header("location: index");
        }
        else
        {
            echo "Ocorreu um erro! Tente novamente.";
        }

        // Close statement
        mysqli_stmt_close($stmt);
    }

    // Close connection
    mysqli_close($conection_db);
}
?>