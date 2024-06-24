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

// Verifique se há ações de ativar ou deletar
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        $id = intval($_POST['id']);
        
        if ($_POST['action'] == 'delete') {
            $sql = "UPDATE users SET active = 0 WHERE id = ?";
        } elseif ($_POST['action'] == 'activate') {
            $sql = "UPDATE users SET active = 1 WHERE id = ?";
        }
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
}

// Verifique se há um novo usuário para ser inserido
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_user_email'])) {
    $new_user_email = $_POST['new_user_email'];
    $new_user_name = $_POST['new_user_name'];
    $new_user_password = password_hash($_POST['new_user_password'], PASSWORD_DEFAULT);
    $new_user_image = $_POST['new_user_image'];
    
    $sql_insert = "INSERT INTO users (email, name, password, image) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql_insert);
    $stmt->bind_param("ssss", $new_user_email, $new_user_name, $new_user_password, $new_user_image);
    $stmt->execute();
    $stmt->close();
    header("location: users");
}

// Obtenha os dados da tabela users
$sql = "SELECT id, email, name, created_at, active, image, user_type FROM users";
$result = $conn->query($sql);

// Iniciar sessão
session_start();

function abreviarNome($nomeCompleto) {
    // Separar o nome completo em partes
    $partes = explode(' ', $nomeCompleto);
    
    // Se o nome tiver menos de três partes, retornar o nome completo
    if (count($partes) < 3) {
        return $nomeCompleto;
    }
    
    // Obter o primeiro e o último nome
    $primeiroNome = array_shift($partes);
    $ultimoNome = array_pop($partes);
    
    // Abreviar os nomes intermediários
    $abreviatura = '';
    foreach ($partes as $parte) {
        $abreviatura .= strtoupper($parte[0]) . '. ';
    }
    
    // Montar o nome abreviado
    $nomeAbreviado = $primeiroNome . ' ' . trim($abreviatura) . ' ' . $ultimoNome;
    
    return $nomeAbreviado;
}
 
// Checar se usuário está logado, se não volta para tela de login
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login");
    exit;
}

if(isset($_SESSION["first_login"]) && $_SESSION["first_login"] == 1) {
    header("location: password_edit");
    exit;
}

$timeout_duration = 1800; // 30 minutos

// Verifica se o cookie de última atividade está definido
if (isset($_COOKIE['LAST_ACTIVITY'])) {
    // Calcula o tempo de inatividade
    $elapsed_time = time() - $_COOKIE['LAST_ACTIVITY'];

    // Verifica se o tempo de inatividade excede o tempo máximo permitido
    if ($elapsed_time > $timeout_duration) {
        // Se exceder, destrói a sessão
        session_unset(); // Remove todas as variáveis de sessão
        session_destroy(); // Destroi a sessão

        // Remove o cookie
        setcookie('LAST_ACTIVITY', '', time() - 3600, '/');

        // Redireciona para a página de login (ou qualquer outra página desejada)
        header("Location: login");
        exit();
    }
}

// Verifique o tipo de usuário
$user_has_access = $_SESSION["user_type"] == 4;
if (!$user_has_access) {
    // Usuário não tem acesso, exibe a modal
    echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            $("#accessDeniedModal").modal("show");
        });
    </script>';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>GeoTracker - Seu sistema de ocorrências!</title>

    <!-- Meta -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />


    <meta name="author" content="Lucca" />

    <!-- Favicon icon -->
    <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">
    <!-- fontawesome icon -->
    <link rel="stylesheet" href="assets/fonts/fontawesome/css/fontawesome-all.min.css">
    <!-- animation css -->
    <link rel="stylesheet" href="assets/plugins/animation/css/animate.min.css">
    <!-- vendor css -->
    <link rel="stylesheet" href="assets/css/style.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <style>
    .action-buttons2 {
        display: flex;
        gap: 10px;
        /* Espaço entre os botões */
        align-items: center;
    }

    .action-buttons2 form,
    .action-buttons2 button {
        margin: 0;
    }

    .emoji-eye {
        font-size: 1rem;
        /* Tamanho do emoji */
    }
    </style>

</head>

<body>
    <!-- [ Pre-loader ] start -->
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>
    <!-- [ Pre-loader ] End -->

    <!-- Modal -->
    <div class="modal fade" id="accessDeniedModal" tabindex="-1" role="dialog" aria-labelledby="accessDeniedModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="accessDeniedModalLabel">Acesso Negado</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Você não tem permissão para acessar esta página.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary"
                        onclick="window.location.href='index.php'">Aceitar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- [ navigation menu ] start -->
    <nav class="pcoded-navbar">
        <div class="navbar-wrapper">
            <div class="navbar-brand header-logo">
                <a href="index" class="b-brand">
                    <div class="b-bg">
                        <img class="rounded-circle" style="width:40px;" src="<?php echo $_SESSION['image']; ?>"
                            alt="activity-user">
                    </div>
                    <?php
                    if(isset($_SESSION['name'])) {
                        echo '<div class="b-title-container">';
                        echo '<span class="b-title">'.abreviarNome($_SESSION['name']).'</span>';
                        
                        // Definir o tipo de usuário baseado na sessão
                        $user_type = '';
                        if(isset($_SESSION['user_type'])) {
                            switch($_SESSION['user_type']) {
                                case 1:
                                    $user_type = 'Visualizador';
                                    break;
                                case 2:
                                    $user_type = 'Validador';
                                    break;
                                case 3:
                                    $user_type = 'Cadastrador';
                                    break;
                                case 4:
                                    $user_type = 'Super Admin';
                                    break;
                                default:
                                    $user_type = 'Desconhecido';
                            }
                        }
                        echo '<span class="b-subtitle">'.$user_type.'</span>';
                        echo '</div>';
                    }
                ?>
                </a>
                <!-- <a class="mobile-menu" id="mobile-collapse" href="javascript:"><span></span></a> -->
            </div>
            <div class="navbar-content scroll-div">
                <ul class="nav pcoded-inner-navbar">
                    <li class="nav-item pcoded-menu-caption">
                        <label>Menu de navegação</label>
                    </li>
                    <li data-username="dashboard Default Ecommerce CRM Analytics Crypto Project" class="nav-item">
                        <a href="index" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-home"></i></span><span class="pcoded-mtext">Página
                                inicial</span></a>
                    </li>
                    <li data-username="Maps Google" class="nav-item">
                        <a href="map" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-map"></i></span><span class="pcoded-mtext">Visualizar
                                mapa</span></a>
                    </li>
                    <li class="nav-item pcoded-menu-caption">
                        <label>Ocorrências</label>
                    </li>
                    <li data-username="Table bootstrap datatable footable" class="nav-item">
                        <a href="addocorrencia" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-file-plus"></i></span><span class="pcoded-mtext">Registrar nova
                                ocorrência</span></a>
                    </li>
                    <li data-username="Table bootstrap datatable footable" class="nav-item">
                        <a href="ocorrencias" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-list"></i></span><span class="pcoded-mtext">Lista de
                                ocorrências</span></a>
                    </li>

                    <li class="nav-item pcoded-menu-caption">
                        <label>Administração</label>
                    </li>
                    <li data-username="Table bootstrap datatable footable" class="nav-item active">
                        <a href="users" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-users"></i></span><span class="pcoded-mtext">Usuários</span></a>
                    </li>
                    <li data-username="Table bootstrap datatable footable" class="nav-item">
                        <a href="categorias" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-globe"></i></span><span class="pcoded-mtext">Editar
                                categorias</span></a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- [ navigation menu ] end -->

    <!-- [ Header ] start -->
    <header class="navbar pcoded-header navbar-expand-lg navbar-light">
        <div class="m-header">
            <a class="mobile-menu" id="mobile-collapse1" href="javascript:"><span></span></a>
            <a href="index" class="b-brand">
                <div class="b-bg">
                    <i class="feather icon-trending-up"></i>
                </div>
                <span class="b-title"><?php
                    if(isset($_SESSION['name'])) {
                        echo '<div class="b-title-container">';
                        echo '<span class="b-title">'.abreviarNome($_SESSION['name']).'</span>';
                        
                        // Definir o tipo de usuário baseado na sessão
                        $user_type = '';
                        if(isset($_SESSION['user_type'])) {
                            switch($_SESSION['user_type']) {
                                case 1:
                                    $user_type = 'Visualizador';
                                    break;
                                case 2:
                                    $user_type = 'Validador';
                                    break;
                                case 3:
                                    $user_type = 'Cadastrador';
                                    break;
                                case 4:
                                    $user_type = 'Super Admin';
                                    break;
                                default:
                                    $user_type = 'Desconhecido';
                            }
                        }
                        echo '<span class="b-subtitle">'.$user_type.'</span>';
                        echo '</div>';
                    }
                ?></span>
            </a>
        </div>
        <a class="mobile-menu" id="mobile-header" href="javascript:">
            <i class="feather icon-more-horizontal"></i>
        </a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav mr-auto">
                <li><a href="javascript:" class="full-screen" onclick="javascript:toggleFullScreen()"><i
                            class="feather icon-maximize"></i></a></li>
                <!-- <li class="nav-item">
                    <div class="main-search">
                        <div class="input-group">
                            <input type="text" id="m-search" class="form-control" placeholder="Pesquisar...">
                            <a href="javascript:" class="input-group-append search-close">
                                <i class="feather icon-x input-group-text"></i>
                            </a>
                            <span class="input-group-append search-btn btn btn-primary">
                                <i class="feather icon-search input-group-text"></i>
                            </span>
                        </div>
                    </div>
                </li> -->
            </ul>
            <ul class="navbar-nav ml-auto">
                <li>
                    <div class="dropdown drp-user">
                        <a href="javascript:" class="dropdown-toggle" data-toggle="dropdown">
                            <i class="icon feather icon-settings"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right profile-notification">
                            <div class="pro-head">
                                <img src="<?php echo $_SESSION['image']; ?>" class="img-radius"
                                    alt="User-Profile-Image">
                                <?php
                                    if(isset($_SESSION['name']))
                                    echo '<span>'.$_SESSION['name'].'</span>'; 
                                ?>
                                <a href="logout" class="dud-logout" title="Logout">
                                    <i class="feather icon-log-out"></i>
                                </a>
                            </div>
                            <ul class="pro-body">
                                <li><a href="profile_edit" class="dropdown-item"><i class="feather icon-user"></i>
                                        Editar perfil</a></li>
                                <li><a href="password_edit" class="dropdown-item"><i
                                            class="feather feather icon-lock"></i> Editar senha</a></li>
                            </ul>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </header>
    <!-- [ Header ] end -->

    <?php if ($user_has_access): ?>
    <!-- [ Main Content ] start -->
    <section class="pcoded-main-container">
        <div class="pcoded-wrapper">
            <div class="pcoded-content">
                <div class="pcoded-inner-content">
                    <!-- [ breadcrumb ] start -->
                    <div class="page-header">
                        <div class="page-block">
                            <div class="row align-items-center">
                                <div class="col-md-12">
                                    <div class="page-header-title">
                                        <h5 class="m-b-10">Gerenciando usuários</h5>
                                    </div>
                                    <ul class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="index.html"><i
                                                    class="feather icon-home"></i></a></li>
                                        <li class="breadcrumb-item"><a href="javascript:">Você está gerenciando os
                                                usuários</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- [ breadcrumb ] end -->
                    <div class="main-body">
                        <div class="page-wrapper">
                            <!-- [ Main Content ] start -->
                            <div class="row">
                                <!-- [ Hover-table ] start -->
                                <div class="col-xl-12">
                                    <div class="card">
                                        <div class="card-block table-border-style">
                                            <div class="table-responsive">
                                                <table class="table table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>ID</th>
                                                            <th>Imagem</th>
                                                            <th>Email</th>
                                                            <th>Nome</th>
                                                            <th>Tipo de Usuário</th>
                                                            <th>Data de criação</th>
                                                            <th>Ações</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                    if ($result->num_rows > 0) {
                                                        // Saída dos dados de cada linha
                                                        while($row = $result->fetch_assoc()) {

                                                            $user_type = "";
                                                            switch ($row["user_type"]) {
                                                                case 1:
                                                                    $user_type = 'Visualizador';
                                                                    break;
                                                                case 2:
                                                                    $user_type = 'Validador';
                                                                    break;
                                                                case 3:
                                                                    $user_type = 'Cadastrador';
                                                                    break;
                                                                case 4:
                                                                    $user_type = 'Super Admin';
                                                                    break;
                                                                default:
                                                                    $user_type = 'Desconhecido';
                                                            }

                                                            echo "<tr>";
                                                            echo "<th scope='row'>" . htmlspecialchars($row["id"]) . "</th>";
                                                            echo '<td><img class="rounded-circle" style="width:30px;" src="' . htmlspecialchars($row["image"]) . '" alt="activity-user"></td>';
                                                            echo "<td>" . htmlspecialchars($row["email"]) . "</td>";
                                                            echo "<td>" . htmlspecialchars($row["name"]) . "</td>";
                                                            echo "<td>" . htmlspecialchars($user_type) . "</td>";
                                                            echo "<td>" . htmlspecialchars($row["created_at"]) . "</td>";
                                                            echo "<td class='action-buttons2'>";
                                                            if ($row["active"] == 0) {
                                                                echo "<form method='post'>
                                                                        <input type='hidden' name='id' value='" . htmlspecialchars($row["id"]) . "'>
                                                                        <input type='hidden' name='action' value='activate'>
                                                                        <button type='submit' class='btn btn-success btn-sm' style='padding: 0.25rem 0.75rem; font-size: 0.75rem; text-align: center;'>Ativar</button>
                                                                      </form>";
                                                            } else {
                                                                echo "<form method='post'>
                                                                        <input type='hidden' name='id' value='" . htmlspecialchars($row["id"]) . "'>
                                                                        <input type='hidden' name='action' value='delete'>
                                                                        <button type='submit' class='btn btn-danger btn-sm' style='padding: 0.25rem 0.75rem; font-size: 0.75rem; text-align: center;'>Desativar</button>
                                                                    </form>";
                                                            }
                                                            echo "<button class='btn btn-primary btn-sm btn-edit' data-toggle='modal' data-target='#editUserModal'
                                                                data-id='" . htmlspecialchars($row["id"]) . "'
                                                                data-email='" . htmlspecialchars($row["email"]) . "'
                                                                data-name='" . htmlspecialchars($row["name"]) . "'
                                                                data-image='" . htmlspecialchars($row["image"]) . "'
                                                                data-user_type='" . htmlspecialchars($row["user_type"]) . "'
                                                                style='padding: 0.25rem 0.75rem; font-size: 0.75rem; text-align: center;'>Editar</button>"; 
                                                            echo "</td>";
                                                            echo "</tr>";
                                                        }
                                                    } else {
                                                        echo "<tr><td colspan='6'>Nenhum resultado encontrado</td></tr>";
                                                    }
                                                    ?>
                                                    </tbody>
                                                </table>
                                                <button type="button" class="btn btn-primary" data-toggle="modal"
                                                    data-target="#addUserModal">Adicionar Usuário</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- [ Hover-table ] end -->
                            </div>
                            <!-- [ Main Content ] end -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Edit User Modal -->
        <div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form id="editUserForm" method="post" action="controllers/update_user.php">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editUserModalLabel">Editar Usuário</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="id" id="editUserId">
                            <div class="form-group">
                                <label for="editUserName">Nome</label>
                                <input type="text" class="form-control" id="editUserName" name="name" required>
                            </div>
                            <div class="form-group">
                                <label for="editUserEmail">Email</label>
                                <input type="email" class="form-control" id="editUserEmail" name="email" required
                                    readonly>
                            </div>
                            <div class="form-group">
                                <label for="editUserImage">Imagem</label>
                                <input type="text" class="form-control" id="editUserImage" name="image" required>
                            </div>
                            <div class="form-group">
                                <label for="editUserType">Tipo de Usuário</label>
                                <select class="form-control" id="editUserType" name="user_type" required>
                                    <option value="1">Visualizador</option>
                                    <option value="2">Validador</option>
                                    <option value="3">Cadastrador</option>
                                    <option value="4">Super Admin</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                            <button type="submit" class="btn btn-primary">Salvar alterações</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal para Adicionar Usuário -->
        <div class="modal fade" id="addUserModal" tabindex="-1" role="dialog" aria-labelledby="addUserModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addUserModalLabel">Adicionar Novo Usuário</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form method="post">
                            <div class="form-group">
                                <label for="new_user_email">Email</label>
                                <input type="email" class="form-control" id="new_user_email" name="new_user_email"
                                    required>
                            </div>
                            <div class="form-group">
                                <label for="new_user_name">Nome</label>
                                <input type="text" class="form-control" id="new_user_name" name="new_user_name"
                                    required>
                            </div>
                            <div class="form-group">
                                <label for="new_user_password">Senha</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="new_user_password"
                                        name="new_user_password" value="123456" required>
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <span class="emoji-eye" aria-hidden="true">👁️</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="new_user_image">Imagem (URL)</label>
                                <input type="text" class="form-control" id="new_user_image" name="new_user_image"
                                    required>
                            </div>
                            <button type="submit" class="btn btn-primary">Adicionar Usuário</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </section>

    <!-- Required Js -->
    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>


    <script>
    $(document).ready(function() {
        $('.btn-edit').on('click', function() {
            var id = $(this).data('id');
            var email = $(this).data('email');
            var name = $(this).data('name');
            var image = $(this).data('image');
            var user_type = $(this).data('user_type');

            $('#editUserId').val(id);
            $('#editUserEmail').val(email);
            $('#editUserName').val(name);
            $('#editUserImage').val(image);
            $('#editUserType').val(user_type);
        });

        <?php if (isset($_SESSION['update_success']) && $_SESSION['update_success']): ?>
        toastr.success('Usuário atualizado com sucesso!');
        <?php unset($_SESSION['update_success']); ?>
        <?php endif; ?>
    });
    </script>
    <script>
    document.getElementById('togglePassword').addEventListener('click', function() {
        var passwordInput = document.getElementById('new_user_password');
        var button = document.getElementById('togglePassword');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            button.innerHTML =
            '<span class="emoji-eye" aria-hidden="true">👁️</span>'; // Altera para o emoji de olho aberto
        } else {
            passwordInput.type = 'password';
            button.innerHTML =
            '<span class="emoji-eye" aria-hidden="true">👁️</span>'; // Altera para o emoji de olho fechado
        }
    });
    </script>


</body>

</html>