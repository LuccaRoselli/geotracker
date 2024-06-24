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

// Obtenha as categorias
$sql_categories = "SELECT id, categoria FROM categorias";
$result_categories = $conn->query($sql_categories);
$categories = [];
if ($result_categories->num_rows > 0) {
    while ($row = $result_categories->fetch_assoc()) {
        $categories[] = $row;
    }
}


// Verifique se um status foi enviado via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['id']) && isset($_POST['action'])) {
        $id = $_POST["id"];
        $action = $_POST["action"];

        if ($action === 'update') {
            $status = $_POST["status"];
            // Atualize o status da ocorrência
            $sql_update = "UPDATE ocorrencias SET status = ? WHERE id = ?";
            $stmt = $conn->prepare($sql_update);
            $stmt->bind_param("ii", $status, $id);
            $stmt->execute();
            $stmt->close();
        } elseif ($action === 'delete') {
            // Delete a ocorrência
            $sql_delete = "DELETE FROM ocorrencias WHERE id = ?";
            $stmt = $conn->prepare($sql_delete);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// Verifique se uma ocorrência existente precisa ser atualizada
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_occurrence_id'])) {
    $edit_occurrence_id = $_POST['edit_occurrence_id'];
    $edit_nome = $_POST['edit_nome'];
    $edit_descricao = $_POST['edit_descricao'];
    $edit_categoria = $_POST['edit_categoria'];
    
    $sql_update = "UPDATE ocorrencias SET nome = ?, descricao = ?, categoria = ? WHERE id = ?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("sssi", $edit_nome, $edit_descricao, $edit_categoria, $edit_occurrence_id);
    $stmt->execute();
    $stmt->close();
}

// Defina o fuso horário, se necessário
date_default_timezone_set('America/Sao_Paulo'); // Ajuste conforme necessário

// Contar o número de ocorrências para a data de hoje
$today = date('Y-m-d');
$sql_count = "SELECT COUNT(*) as count FROM ocorrencias WHERE DATE(created_at) = ?";
$stmt = $conn->prepare($sql_count);
$stmt->bind_param("s", $today);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$count = $row['count'];
$stmt->close();

// Contar o número de ocorrências para a data de hoje com status 2 ou 3
$sql_count_status = "SELECT COUNT(*) as count_status FROM ocorrencias WHERE DATE(created_at) = ? AND (status = 2 OR status = 3)";
$stmt_status = $conn->prepare($sql_count_status);
$stmt_status->bind_param("s", $today);
$stmt_status->execute();
$result_status = $stmt_status->get_result();
$row_status = $result_status->fetch_assoc();
$count_status = $row_status['count_status'];
$stmt_status->close();

// Calcular a porcentagem de ocorrências não resolvidas
$percentage = ($count > 0) ? ($count_status / $count) * 100 : 0;

// Contar o número total de ocorrências
$sql_total = "SELECT COUNT(*) as total FROM ocorrencias";
$result_total = $conn->query($sql_total);
$row_total = $result_total->fetch_assoc();
$total_ocorrencias = $row_total['total'];
                      
// Modifique a consulta SQL para incluir a junção
$sql = "
SELECT ocorrencias.id, ocorrencias.nome, ocorrencias.descricao, ocorrencias.bairro, ocorrencias.rua, ocorrencias.created_at, ocorrencias.status, users.image, categorias.categoria AS categoria, categorias.id AS categoria_id
FROM ocorrencias 
JOIN users ON ocorrencias.email = users.email 
JOIN categorias ON ocorrencias.categoria = categorias.id WHERE DATE(ocorrencias.created_at) = '$today'";


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

$userType = $_SESSION["user_type"];
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

    <style>
    .truncate-text {
        white-space: normal;
        word-wrap: break-word;
        max-width: 200px;
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
                    <li data-username="dashboard Default Ecommerce CRM Analytics Crypto Project"
                        class="nav-item active">
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
                    <li data-username="Table bootstrap datatable footable" class="nav-item">
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

    <!-- [ Main Content ] start -->
    <div class="pcoded-main-container">
        <div class="pcoded-wrapper">
            <div class="pcoded-content">
                <div class="pcoded-inner-content">
                    <!-- [ breadcrumb ] start -->

                    <!-- [ breadcrumb ] end -->
                    <div class="main-body">
                        <div class="page-wrapper">
                            <!-- [ Main Content ] start -->
                            <div class="row">
                                <!--[ daily sales section ] start-->
                                <div class="col-md-6 col-xl-4">
                                    <div class="card daily-sales">
                                        <div class="card-block">
                                            <h6 class="mb-4">Ocorrências diárias atendidas</h6>
                                            <div class="row d-flex align-items-center">
                                                <div class="col-9">
                                                    <h3 class="f-w-300 d-flex align-items-center m-b-0" id="diárias"><i
                                                            class="feather icon-phone-call text-c-green f-30 m-r-10"></i><?php echo $count; ?>
                                                    </h3>
                                                </div>
                                                <!-- <div class="col-3 text-right">
                                                    <p class="m-b-0">100%</p>
                                                </div> -->
                                            </div>
                                            <div class="progress m-t-30" style="height: 7px;">
                                                <div class="progress-bar progress-c-theme" role="progressbar"
                                                    style="width: 100%;" aria-valuenow="100" aria-valuemin="0"
                                                    aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--[ daily sales section ] end-->
                                <!--[ Monthly  sales section ] starts-->
                                <div class="col-md-6 col-xl-4">
                                    <div class="card Monthly-sales">
                                        <div class="card-block">
                                            <h6 class="mb-4">Ocorrências diárias não resolvidas</h6>
                                            <div class="row d-flex align-items-center">
                                                <div class="col-9">
                                                    <h3 class="f-w-300 d-flex align-items-center  m-b-0"><i
                                                            class="feather icon-phone-off text-c-red f-30 m-r-10"></i><?php echo $count_status; ?>
                                                    </h3>
                                                </div>
                                                <div class="col-3 text-right">
                                                    <p class="m-b-0"><?php echo round($percentage, 2); ?>%</p>
                                                </div>
                                            </div>
                                            <div class="progress m-t-30" style="height: 7px;">
                                                <div class="progress-bar progress-c-theme2" role="progressbar"
                                                    style="width: <?php echo $percentage; ?>%;"
                                                    aria-valuenow="<?php echo $percentage; ?>" aria-valuemin="0"
                                                    aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--[ Monthly  sales section ] end-->
                                <!--[ year  sales section ] starts-->
                                <div class="col-md-12 col-xl-4">
                                    <div class="card yearly-sales">
                                        <div class="card-block">
                                            <h6 class="mb-4">Total de ocorrências registradas</h6>
                                            <div class="row d-flex align-items-center">
                                                <div class="col-9">
                                                    <h3 class="f-w-300 d-flex align-items-center  m-b-0"><i
                                                            class="feather icon-activity text-c-green f-30 m-r-10"></i><?php echo $total_ocorrencias; ?>
                                                    </h3>
                                                </div>
                                            </div>
                                            <div class="progress m-t-30" style="height: 7px;">
                                                <div class="progress-bar progress-c-theme" role="progressbar"
                                                    style="width: 100%;" aria-valuenow="100" aria-valuemin="0"
                                                    aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--[ year  sales section ] end-->
                                <!--[ Recent Users ] start-->
                                <div class="col-xl-12 col-md-6">
                                    <div class="card Recent-Users">
                                        <div class="card-header">
                                            <h5>Ocorrências recentes (<?php echo $result->num_rows; ?>)</h5>
                                        </div>
                                        <div class="card-block px-0 py-3">
                                            <div class="table-responsive">
                                                <table class="table table-hover">
                                                    <tbody>
                                                        <?php 
                                                        if ($result->num_rows > 0) {
                                                            // Saída dos dados de cada linha
                                                            while($row = $result->fetch_assoc()) {

                                                                $statusClass = '';
                                                                if ($row["status"] == 1) {
                                                                    $statusClass = 'text-c-green';
                                                                } elseif ($row["status"] == 2) {
                                                                    $statusClass = 'text-c-red';
                                                                } elseif ($row["status"] == 3) {
                                                                    $statusClass = 'text-c-yellow';
                                                                }

                                                                // Converter a data para o formato brasileiro
                                                                $data = new DateTime($row["created_at"]);
                                                                $data_formatada = $data->format('d-m-Y H:i:s');

                                                                echo '<tr class="unread">';
                                                                echo '    <td><img class="rounded-circle" style="width:40px;" src="' . htmlspecialchars($row["image"]) . '" alt="activity-user"></td>';
                                                                echo '    <td>';
                                                                echo '        <h6 class="mb-1" id="nome">' . htmlspecialchars($row["nome"]) . '</h6>';
                                                                echo '        <p class="m-0 truncate-text" id="descricao">' . htmlspecialchars($row["descricao"]) . '</p>';
                                                                echo '    </td>';
                                                                echo '    <td>';
                                                                echo '        <h6 class="mb-1" id="bairro">' . htmlspecialchars($row["bairro"]) . '</h6>';
                                                                echo '        <p class="m-0" id="rua">' . htmlspecialchars($row["rua"]) . '</p>';
                                                                echo '    </td>';
                                                                echo '    <td>';
                                                                echo '        <h6 class="mb-1">' . htmlspecialchars($row["categoria"]) . '</h6>';
                                                                echo '    </td>';
                                                                echo '    <td>';
                                                                echo '        <h6 class="mb-1" id="data"><i class="fas fa-circle ' . $statusClass . ' f-10 m-r-15"></i>' . htmlspecialchars($data_formatada) . '</h6>';
                                                                echo '    </td>';
                                                                
                                                                if (in_array($userType, [2, 3, 4])) {
                                                                    echo '    <td class="action-buttons">';
                                                                    echo '        <div class="approval-buttons">';
                                                                    echo '        <form method="post" style="display:inline-block;">';
                                                                    echo '            <input type="hidden" name="id" value="' . htmlspecialchars($row["id"]) . '">';
                                                                    echo '            <input type="hidden" name="action" value="update">';
                                                                    echo '            <input type="hidden" name="status" value="1">';
                                                                    echo '            <button type="submit" class="label theme-bg text-white f-12" style="border:none;  cursor:pointer;">Aprovar</button>';
                                                                    echo '        </form>';
                                                                    echo '        <form method="post" style="display:inline-block;">';
                                                                    echo '            <input type="hidden" name="id" value="' . htmlspecialchars($row["id"]) . '">';
                                                                    echo '            <input type="hidden" name="action" value="update">';
                                                                    echo '            <input type="hidden" name="status" value="2">';
                                                                    echo '            <button type="submit" class="label theme-bg2 text-white f-12" style="border:none;  cursor:pointer;">Rejeitar</button>';
                                                                    echo '        </form>';
                                                                    echo '        <form method="post" style="display:inline-block;">';
                                                                    echo '            <input type="hidden" name="id" value="' . htmlspecialchars($row["id"]) . '">';
                                                                    echo '            <input type="hidden" name="action" value="update">';
                                                                    echo '            <input type="hidden" name="status" value="3">';
                                                                    echo '            <button type="submit" class="label theme-bg3 text-white f-12" style="border:none; cursor:pointer;">Em análise</button>';
                                                                    echo '        </form>';
                                                                    echo '        </div>';
                                                                    echo '        <button type="button" class="label theme-bg5 text-white f-12 btn-sm2 delete-button" style="border:none; cursor:pointer;" data-toggle="modal" data-target="#editOccurrenceModal" data-id="' . htmlspecialchars($row["id"]) . '" data-nome="' . htmlspecialchars($row["nome"]) . '" data-descricao="' . htmlspecialchars($row["descricao"]) . '" data-bairro="' . htmlspecialchars($row["bairro"]) . '" data-rua="' . htmlspecialchars($row["rua"]) . '" data-categoria-id="' . htmlspecialchars($row["categoria_id"]) . '"><i class="feather icon-edit"></i></button>';

                                                                    echo '        <form method="post" style="display:inline-block;" class="delete-button">';
                                                                    echo '            <input type="hidden" name="id" value="' . htmlspecialchars($row["id"]) . '">';
                                                                    echo '            <input type="hidden" name="action" value="delete">';
                                                                    echo '            <button type="submit" class="label theme-bg4 text-white f-12 cursor-pointer" style="border:none; cursor:pointer;"><i class="feather icon-trash-2"></i></button>';
                                                                    echo '        </form>';
                                                                    echo '    </td>';
                                                                }

                                                                echo '</tr>';
                                                            }
                                                        } else {
                                                            echo "0 resultados";
                                                        }

                                                        $conn->close();
                                                        ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- [ Main Content ] end -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para Editar Ocorrência -->
        <div class="modal fade" id="editOccurrenceModal" tabindex="-1" role="dialog"
            aria-labelledby="editOccurrenceModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editOccurrenceModalLabel">Editar Ocorrência</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form method="post">
                            <input type="hidden" id="edit_occurrence_id" name="edit_occurrence_id">
                            <div class="form-group">
                                <label for="edit_nome">Nome</label>
                                <input type="text" class="form-control" id="edit_nome" name="edit_nome" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_descricao">Descrição</label>
                                <textarea class="form-control" id="edit_descricao" name="edit_descricao" rows="3"
                                    required></textarea>
                            </div>
                            <div class="form-group">
                                <label for="edit_bairro">Bairro</label>
                                <input type="text" class="form-control" id="edit_bairro" name="edit_bairro" readonly
                                    disabled>
                            </div>
                            <div class="form-group">
                                <label for="edit_rua">Rua</label>
                                <input type="text" class="form-control" id="edit_rua" name="edit_rua" readonly disabled>
                            </div>
                            <div class="form-group">
                                <label for="edit_categoria">Categoria</label>
                                <select class="form-control" id="edit_categoria" name="edit_categoria" required>
                                    <?php foreach ($categories as $category): ?>
                                    <option value="<?= htmlspecialchars($category['id']); ?>">
                                        <?= htmlspecialchars($category['categoria']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Salvar alterações</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Required Js -->
    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filterLinks = document.querySelectorAll('#category-filter .dropdown-item');
            const rows = document.querySelectorAll('#occurrencesTable tbody tr');
            const categoryDropdown = document.getElementById('categoryDropdown');

            filterLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const category = this.getAttribute('data-category');

                    rows.forEach(row => {
                        if (category === 'all' || row.getAttribute('data-category') ===
                            category) {
                            row.classList.remove('escondido');
                        } else {
                            row.classList.add('escondido');
                        }
                    });
                });
            });
        });

        $('#editOccurrenceModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var id = button.data('id');
            var nome = button.data('nome');
            var descricao = button.data('descricao');
            var bairro = button.data('bairro');
            var rua = button.data('rua');
            var categoriaId = button.data('categoria-id');

            var modal = $(this);
            modal.find('#edit_occurrence_id').val(id);
            modal.find('#edit_nome').val(nome);
            modal.find('#edit_descricao').val(descricao);
            modal.find('#edit_bairro').val(bairro);
            modal.find('#edit_rua').val(rua);
            modal.find('#edit_categoria').val(categoriaId);
        });
    </script>

</body>

</html>