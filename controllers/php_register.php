<?php
// Define variables and initialize with empty values
$email = $name = $password = $confirm_password = "";
$email_err = $name_err = $password_err = $confirm_password_err = "";
 
// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST")
{
    // Validate email
    if(empty(trim($_POST["email"])))
    {
        $email_err = "Please enter a email.";
    }
        else
    {
        // Prepare a select statement
        $sql = "SELECT id FROM users WHERE email = ?";
        
        if($stmt = mysqli_prepare($conection_db, $sql))
        {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_email);
            
            // Set parameters
            $param_email = trim($_POST["email"]);
            $param_name = trim($_POST["name"]);
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt))
            {
                /* store result */
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 1)
                {
                    $email_err = "Esse email já está em uso.";
                }
                    else
                {
                    $email = trim($_POST["email"]);
                }
            }
                else
            {
                echo "Ocorreu um erro! Tente novamente.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
    
    // Validate password
    if(empty(trim($_POST["password"])))
    {
        $password_err = "Por favor digite uma senha.";     
        }
            elseif
            (strlen(trim($_POST["password"])) < 6)
        {
        $password_err = "Sua senha deve ter no mínimo 6 caracteres.";
    }
        else
    {
        $password = trim($_POST["password"]);
    }
    
    // Validate confirm password
    if(empty(trim($_POST["confirm_password"])))
    {
        $confirm_password_err = "Por favor confirme a senha.";     
    }
        else
    {
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($password_err) && ($password != $confirm_password))
        {
            $confirm_password_err = "As senhas não coincidem!";
        }
    }
    
    // Check input errors before inserting in database
    if(empty($email_err) && empty($password_err) && empty($confirm_password_err) && empty($name_err))
    {
        // Prepare an insert statement
        $sql = "INSERT INTO users (email, password, name) VALUES (?, ?, ?)";
         
        if($stmt = mysqli_prepare($conection_db, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "sss", $param_email, $param_password, $param_name);
            
            // Set parameters
            $param_email = $email;
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt))
            {
                // Redirect to login page
                header("location: login");
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