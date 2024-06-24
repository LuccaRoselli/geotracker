<?php
// Define variables and initialize with empty values
$email = $password = $name = $image = $user_type = $first_login = $active = "";
$email_err = $password_err = "";
$attempts_limit = 5;
$lockout_time = 10; // seconds

// Start session if not already started
if(session_status() == PHP_SESSION_NONE){
    session_start();
}

// Initialize attempts and lockout if not already set
if(!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}

if(!isset($_SESSION['lockout_time'])) {
    $_SESSION['lockout_time'] = 0;
}

// Check if the user is locked out
if ($_SESSION['login_attempts'] >= $attempts_limit) {
    $remaining_lockout = time() - $_SESSION['lockout_time'];
    if ($remaining_lockout < $lockout_time) {
        $password_err = "Muitas tentativas de login. Tente novamente em " . ($lockout_time - $remaining_lockout) . " segundos.";
        $_SESSION['login_err'] = $password_err;
    } else {
        // Reset attempts after lockout time has passed
        $_SESSION['login_attempts'] = 0;
        $_SESSION['lockout_time'] = 0;
    }
}

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if email is empty
    if (empty(trim($_POST["email"]))) {
        $email_err = "Por favor, digite um email!";
        $_SESSION['login_err'] = $email_err;
    } else {
        $email = trim($_POST["email"]);
    }

    // Check if password is empty
    if (empty(trim($_POST["password"]))) {
        $password_err = "Por favor, digite uma senha!";
        $_SESSION['login_err'] = $password_err;
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate credentials
    if (empty($email_err) && empty($password_err)) {
        // Prepare a select statement
        $sql = "SELECT id, email, name, image, password, user_type, first_login, active FROM users WHERE email = ?";
        if ($stmt = mysqli_prepare($conection_db, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_email);

            // Set parameters
            $param_email = $email;

            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                // Store result
                mysqli_stmt_store_result($stmt);

                // Check if email exists, if yes then verify password
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    // Bind result variables
                    mysqli_stmt_bind_result($stmt, $id, $email, $name, $image, $hashed_password, $user_type, $first_login, $active);
                    if (mysqli_stmt_fetch($stmt)) {
                        if ($active == 1) {
                            if (password_verify($password, $hashed_password)) {
                                // Password is correct, reset attempts
                                $_SESSION['login_attempts'] = 0;

                                // Start a new session and store data
                                session_start();
                                $_SESSION["loggedin"] = true;
                                $_SESSION["id"] = $id;
                                $_SESSION["email"] = $email;
                                $_SESSION["name"] = $name;
                                $_SESSION["image"] = $image;
                                $_SESSION["user_type"] = $user_type;
                                $_SESSION["first_login"] = $first_login;

                                // Define o cookie de última atividade
                                $timeout_duration = 1800; // 30 minutos
                                setcookie('LAST_ACTIVITY', time(), time() + $timeout_duration, '/');

                                // Check if first login
                                if ($first_login == 1) {
                                    header("location: password_edit");
                                } else {
                                    // Redirect user to welcome page
                                    header("location: index");
                                }
                                exit;
                            } else {
                                // Increment login attempts
                                $_SESSION['login_attempts'] += 1;

                                // Check if attempts exceed limit
                                if ($_SESSION['login_attempts'] >= $attempts_limit) {
                                    $_SESSION['lockout_time'] = time();
                                    $password_err = "Muitas tentativas de login. Tente novamente em " . $lockout_time . " segundos.";
                                    $_SESSION['login_err'] = $password_err;
                                } else {
                                    $password_err = "Senha inválida.";
                                    $_SESSION['login_err'] = $password_err;
                                }
                            }
                        } else {
                            $password_err = "Usuário desativado.";
                            $_SESSION['login_err'] = $password_err;
                        }
                    }
                } else {
                    $email_err = "Nenhuma conta encontrada para esse email.";
                    $_SESSION['login_err'] = $email_err;
                }
            } else {
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
