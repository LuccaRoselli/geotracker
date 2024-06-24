<?php
// Define variables and initialize with empty values
$password_err = $confirm_password_err = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST")
{
    // Validate password
    if(empty(trim($_POST["passwordInput"])))
    {
        $password_err = "Por favor digite uma nova senha.";
    }
    elseif(strlen(trim($_POST["passwordInput"])) < 6)
    {
        $password_err = "Sua senha deve ter ao menos 6 caracteres.";
    }
    else
    {
        $password = trim($_POST["passwordInput"]);
    }

    // Validate confirm password
    if(empty(trim($_POST["passwordInputConfirm"])))
    {
        $confirm_password_err = "Por favor confirme a senha digitada.";
    }
    else
    {
        $confirm_password = trim($_POST["passwordInputConfirm"]);
        if(empty($password_err) && ($password != $confirm_password))
        {
            $confirm_password_err = "As senhas não coincidem!";
        }
    }

    // Check input errors before inserting in database
    if(empty($password_err) && empty($confirm_password_err))
    {
        // Prepare an update statement
        $sql = "UPDATE users SET password = ?, first_login = 0 WHERE email = ?";

        if($stmt = mysqli_prepare($conection_db, $sql))
        {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "ss", $param_password, $param_email);

            // Set parameters
            $param_email = $_SESSION['email'];
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash

            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt))
            {
                // Update session variable
                $_SESSION['first_login'] = 0;

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
    }

    // Close connection
    mysqli_close($conection_db);
}
?>
