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
$sql_categories = "SELECT id, categoria FROM categorias WHERE enabled = 1";
$result_categories = $conn->query($sql_categories);

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
SELECT ocorrencias.id, ocorrencias.nome, ocorrencias.descricao, ocorrencias.bairro, ocorrencias.rua, ocorrencias.created_at, ocorrencias.status, users.image, categorias.categoria AS categoria 
FROM ocorrencias 
JOIN users ON ocorrencias.email = users.email 
JOIN categorias ON ocorrencias.categoria = categorias.id WHERE DATE(ocorrencias.created_at) = '$today'";


$result = $conn->query($sql);

// Selecionar todas as ocorrências já geradas
$sql_all = "
    SELECT ocorrencias.id, ocorrencias.nome, ocorrencias.descricao, ocorrencias.bairro, ocorrencias.rua, ocorrencias.created_at, ocorrencias.status, users.image, categorias.categoria AS categoria 
    FROM ocorrencias 
    JOIN users ON ocorrencias.email = users.email 
    JOIN categorias ON ocorrencias.categoria = categorias.id";

$result_all = $conn->query($sql_all);


// Selecionar todas as ocorrências já geradas no dia
$today = date('Y-m-d');
$sql_all_day = "
    SELECT ocorrencias.id, ocorrencias.nome, ocorrencias.descricao, ocorrencias.bairro, ocorrencias.rua, ocorrencias.created_at, ocorrencias.status, users.image, categorias.categoria AS categoria 
    FROM ocorrencias 
    JOIN users ON ocorrencias.email = users.email 
    JOIN categorias ON ocorrencias.categoria = categorias.id WHERE DATE(ocorrencias.created_at) = '$today'";
$result_all_day = $conn->query($sql_all_day);

// Selecionar todas as ocorrências já geradas na semana
$week = date('Y-m-d', strtotime('-1 week'));
$sql_all_week = "
    SELECT ocorrencias.id, ocorrencias.nome, ocorrencias.descricao, ocorrencias.bairro, ocorrencias.rua, ocorrencias.created_at, ocorrencias.status, users.image, categorias.categoria AS categoria 
    FROM ocorrencias 
    JOIN users ON ocorrencias.email = users.email 
    JOIN categorias ON ocorrencias.categoria = categorias.id WHERE DATE(ocorrencias.created_at) <= '$week'";
$result_all_week = $conn->query($sql_all_week);





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
$user_has_access = $_SESSION["user_type"] == 2 || $_SESSION["user_type"] == 3 || $_SESSION["user_type"] == 4;
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
    .responsive-table {
        width: 100%;
        max-width: 100%;
        overflow-x: auto;
        display: block;
    }

    .responsive-table th,
    .responsive-table td {
        white-space: nowrap;
    }

    @media (max-width: 600px) {
        .responsive-table thead {
            display: none;
        }

        .responsive-table tr {
            display: block;
            margin-bottom: 15px;
        }

        .responsive-table td {
            display: block;
            text-align: right;
            font-size: 13px;
            border-bottom: 1px solid #ddd;
            position: relative;
            padding-left: 50%;
        }

        .responsive-table td::before {
            content: attr(data-label);
            position: absolute;
            left: 0;
            width: 50%;
            padding-left: 15px;
            font-weight: bold;
            text-align: left;
        }
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
                    <li data-username="Table bootstrap datatable footable" class="nav-item active">
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
                    <div class="page-header">
                        <div class="page-block">
                            <div class="row align-items-center">
                                <div class="col-md-12">
                                    <div class="page-header-title">
                                        <h5 class="m-b-10">Visualizando ocorrências</h5>
                                    </div>
                                    <ul class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="index"><i
                                                    class="feather icon-home"></i></a></li>
                                        <li class="breadcrumb-item"><a href="javascript:">Você está visualizando as
                                                ocorrências</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- [ breadcrumb ] end -->
                    <div class="main-body">
                        <div class="page-wrapper">
                            <!-- [ rating list ] end-->
                            <div class="col-xl-12 col-md-12 m-b-30">
                                <ul class="nav nav-tabs" id="myTab" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active show" id="contact-tab" data-toggle="tab"
                                            href="#contact" role="tab" aria-controls="contact" aria-selected="true">Tudo
                                            (<?php echo $result_all->num_rows; ?>)</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="home-tab" data-toggle="tab" href="#home" role="tab"
                                            aria-controls="home" aria-selected="false">Hoje
                                            (<?php echo $result_all_day->num_rows; ?>)</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="profile-tab" data-toggle="tab" href="#profile"
                                            role="tab" aria-controls="profile" aria-selected="false">Mais antigo que uma
                                            semana (<?php echo $result_all_week->num_rows; ?>)</a>
                                    </li>
                                    <li class="nav-item dropdown ml-auto">
                                        <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#"
                                            role="button" aria-haspopup="true" aria-expanded="false">Filtrar</a>
                                        <div class="dropdown-menu" id="category-filter">
                                            <a class="dropdown-item" href="#" data-category="all">Todas</a>
                                            <?php
                                                if ($result_categories->num_rows > 0) {
                                                    while ($row = $result_categories->fetch_assoc()) {
                                                        echo '<a class="dropdown-item" href="#" data-category="' . htmlspecialchars($row['categoria']) . '">' . htmlspecialchars($row['categoria']) . '</a>';
                                                    }
                                                } else {
                                                    echo '<a class="dropdown-item" href="#">Nenhuma categoria encontrada</a>';
                                                }
                                                ?>
                                        </div>
                                    </li>
                                </ul>
                                <div class="tab-content" id="myTabContent">
                                    <div class="tab-pane fade responsive-table" id="home" role="tabpanel"
                                        aria-labelledby="home-tab">
                                        <table class="table table-hover" id="occurrencesTable">
                                            <thead>
                                                <tr>
                                                    <th>Denunciante</th>
                                                    <th>Ocorrência</th>
                                                    <th>Data</th>
                                                    <th>Localização</th>
                                                    <th>Categoria</th>
                                                    <th>Status</th>
                                                    <th class="text-right"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                    if ($result_all_day->num_rows > 0) {
                                                        // Saída dos dados de cada linha
                                                        while($row = $result_all_day->fetch_assoc()) {
                                                            // Defina a classe de status com base no valor do campo "status"
                                                            $statusClass = '';
                                                            $statusText = '';
                                                            $buttonColor = '';
                                                            if ($row["status"] == 1) {
                                                                $statusClass = 'text-c-green';
                                                                $statusText = 'Aprovado';
                                                                $buttonColor = '#1de9b6';
                                                            } elseif ($row["status"] == 2) {
                                                                $statusClass = 'text-c-red';
                                                                $statusText = 'Negado';
                                                                $buttonColor = '#f44236';
                                                            } elseif ($row["status"] == 3) {
                                                                $statusClass = 'text-c-yellow';
                                                                $statusText = 'Em análise';
                                                                $buttonColor = '#f4c22b';
                                                            }

                                                            // Converter a data para o formato brasileiro
                                                            $data = new DateTime($row["created_at"]);
                                                            $data_formatada = $data->format('d-m-Y H:i:s');

                                                            echo '<tr data-id="' . htmlspecialchars($row["id"]) . '" data-category="' . htmlspecialchars($row["categoria"]) . '">';
                                                            echo '    <td>';
                                                            //echo '        <h6 class="m-0"><img class="rounded-circle m-r-10" style="width:40px;" src="' . htmlspecialchars($row["image"]) . '" alt="activity-user">' . htmlspecialchars($row["nome"]) . '</h6>';
                                                            echo '        <h6 class="m-0">' . htmlspecialchars($row["nome"]) . '</h6>';
                                                            echo '    </td>';
                                                            echo '    <td>';
                                                            echo '        <h6 class="m-0" id="descricao">' . htmlspecialchars($row["descricao"]) . '</h6>';
                                                            echo '    </td>';
                                                            echo '    <td>';
                                                            echo '        <h6 class="m-0" id="data">' . htmlspecialchars($data_formatada) . '</h6>';
                                                            echo '    </td>';
                                                            echo '    <td>';
                                                            echo '        <h6 class="m-0" id="localizacao">' . htmlspecialchars($row["bairro"]) . ' - ' . htmlspecialchars($row["rua"]) . '</h6>';
                                                            echo '    </td>';
                                                            echo '    <td>';
                                                            echo '        <h6 class="m-0">' . htmlspecialchars($row["categoria"]) . '</h6>';
                                                            echo '    </td>';
                                                            echo '    <td>';
                                                            echo '        <div class="dropdown">';
                                                            echo '            <button class="btn dropdown-toggle btn-status ' . $statusClass . '" style="background-color: ' . $buttonColor . ';" type="button" id="statusDropdown' . htmlspecialchars($row["id"]) . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
                                                            echo '                ' . $statusText;
                                                            echo '            </button>';
                                                            echo '            <div class="dropdown-menu" aria-labelledby="statusDropdown' . htmlspecialchars($row["id"]) . '">';
                                                            echo '                <a class="dropdown-item" href="#" data-status="1" data-id="' . htmlspecialchars($row["id"]) . '">Aprovado</a>';
                                                            echo '                <a class="dropdown-item" href="#" data-status="2" data-id="' . htmlspecialchars($row["id"]) . '">Negado</a>';
                                                            echo '                <a class="dropdown-item" href="#" data-status="3" data-id="' . htmlspecialchars($row["id"]) . '">Em análise</a>';
                                                            echo '            </div>';
                                                            echo '        </div>';
                                                            echo '    </td>';
                                                            echo '    <td class="text-right"><i class="fas fa-circle ' . $statusClass . ' f-10" id="statusIcon' . htmlspecialchars($row["id"]) . '"></i></td>';
                                                            echo '</tr>';
                                                        }
                                                    } else {
                                                        echo "<tr><td colspan='6'>Nenhuma ocorrência encontrada</td></tr>";
                                                    }
                                                    ?>
                                            </tbody>
                                        </table>

                                    </div>
                                    <div class="tab-pane fade responsive-table" id="profile" role="tabpanel"
                                        aria-labelledby="profile-tab">
                                        <table class="table table-hover" id="occurrencesTable">
                                            <thead>
                                                <tr>
                                                    <th>Denunciante</th>
                                                    <th>Ocorrência</th>
                                                    <th>Data</th>
                                                    <th>Localização</th>
                                                    <th>Categoria</th>
                                                    <th>Status</th>
                                                    <th class="text-right"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                    if ($result_all_week->num_rows > 0) {
                                                        // Saída dos dados de cada linha
                                                        while($row = $result_all_week->fetch_assoc()) {
                                                            // Defina a classe de status com base no valor do campo "status"
                                                            $statusClass = '';
                                                            $statusText = '';
                                                            $buttonColor = '';
                                                            if ($row["status"] == 1) {
                                                                $statusClass = 'text-c-green';
                                                                $statusText = 'Aprovado';
                                                                $buttonColor = '#1de9b6';
                                                            } elseif ($row["status"] == 2) {
                                                                $statusClass = 'text-c-red';
                                                                $statusText = 'Negado';
                                                                $buttonColor = '#f44236';
                                                            } elseif ($row["status"] == 3) {
                                                                $statusClass = 'text-c-yellow';
                                                                $statusText = 'Em análise';
                                                                $buttonColor = '#f4c22b';
                                                            }

                                                            // Converter a data para o formato brasileiro
                                                            $data = new DateTime($row["created_at"]);
                                                            $data_formatada = $data->format('d-m-Y H:i:s');

                                                            echo '<tr data-id="' . htmlspecialchars($row["id"]) . '" data-category="' . htmlspecialchars($row["categoria"]) . '">';
                                                            echo '    <td>';
                                                           //echo '        <h6 class="m-0"><img class="rounded-circle m-r-10" style="width:40px;" src="' . htmlspecialchars($row["image"]) . '" alt="activity-user">' . htmlspecialchars($row["nome"]) . '</h6>';
                                                           echo '        <h6 class="m-0">' . htmlspecialchars($row["nome"]) . '</h6>';
                                                            echo '    </td>';
                                                            echo '    <td>';
                                                            echo '        <h6 class="m-0" id="descricao">' . htmlspecialchars($row["descricao"]) . '</h6>';
                                                            echo '    </td>';
                                                            echo '    <td>';
                                                            echo '        <h6 class="m-0" id="data">' . htmlspecialchars($data_formatada) . '</h6>';
                                                            echo '    </td>';
                                                            echo '    <td>';
                                                            echo '        <h6 class="m-0" id="localizacao">' . htmlspecialchars($row["bairro"]) . ' - ' . htmlspecialchars($row["rua"]) . '</h6>';
                                                            echo '    </td>';
                                                            echo '    <td>';
                                                            echo '        <h6 class="m-0">' . htmlspecialchars($row["categoria"]) . '</h6>';
                                                            echo '    </td>';
                                                            echo '    <td>';
                                                            echo '        <div class="dropdown">';
                                                            echo '            <button class="btn dropdown-toggle btn-status ' . $statusClass . '" style="background-color: ' . $buttonColor . ';" type="button" id="statusDropdown' . htmlspecialchars($row["id"]) . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
                                                            echo '                ' . $statusText;
                                                            echo '            </button>';
                                                            echo '            <div class="dropdown-menu" aria-labelledby="statusDropdown' . htmlspecialchars($row["id"]) . '">';
                                                            echo '                <a class="dropdown-item" href="#" data-status="1" data-id="' . htmlspecialchars($row["id"]) . '">Aprovado</a>';
                                                            echo '                <a class="dropdown-item" href="#" data-status="2" data-id="' . htmlspecialchars($row["id"]) . '">Negado</a>';
                                                            echo '                <a class="dropdown-item" href="#" data-status="3" data-id="' . htmlspecialchars($row["id"]) . '">Em análise</a>';
                                                            echo '            </div>';
                                                            echo '        </div>';
                                                            echo '    </td>';
                                                            echo '    <td class="text-right"><i class="fas fa-circle ' . $statusClass . ' f-10" id="statusIcon' . htmlspecialchars($row["id"]) . '"></i></td>';
                                                            echo '</tr>';
                                                        }
                                                    } else {
                                                        echo "<tr><td colspan='6'>Nenhuma ocorrência encontrada</td></tr>";
                                                    }
                                                    ?>
                                            </tbody>
                                            </tbody>
                                        </table>

                                    </div>
                                    <div class="tab-pane fade active show responsive-table" id="contact" role="tabpanel"
                                        aria-labelledby="contact-tab">
                                        <table class="table table-hover" id="occurrencesTable">
                                            <thead>
                                                <tr>
                                                    <th>Denunciante</th>
                                                    <th>Ocorrência</th>
                                                    <th>Data</th>
                                                    <th>Localização</th>
                                                    <th>Categoria</th>
                                                    <th>Status</th>
                                                    <th class="text-right"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                    if ($result_all->num_rows > 0) {
                                                        // Saída dos dados de cada linha
                                                        while($row = $result_all->fetch_assoc()) {
                                                            // Defina a classe de status com base no valor do campo "status"
                                                            $statusClass = '';
                                                            $statusText = '';
                                                            $buttonColor = '';
                                                            if ($row["status"] == 1) {
                                                                $statusClass = 'text-c-green';
                                                                $statusText = 'Aprovado';
                                                                $buttonColor = '#1de9b6';
                                                            } elseif ($row["status"] == 2) {
                                                                $statusClass = 'text-c-red';
                                                                $statusText = 'Negado';
                                                                $buttonColor = '#f44236';
                                                            } elseif ($row["status"] == 3) {
                                                                $statusClass = 'text-c-yellow';
                                                                $statusText = 'Em análise';
                                                                $buttonColor = '#f4c22b';
                                                            }

                                                            // Converter a data para o formato brasileiro
                                                            $data = new DateTime($row["created_at"]);
                                                            $data_formatada = $data->format('d-m-Y H:i:s');

                                                            echo '<tr data-id="' . htmlspecialchars($row["id"]) . '" data-category="' . htmlspecialchars($row["categoria"]) . '">';
                                                            echo '    <td>';
                                                           //echo '        <h6 class="m-0"><img class="rounded-circle m-r-10" style="width:40px;" src="' . htmlspecialchars($row["image"]) . '" alt="activity-user">' . htmlspecialchars($row["nome"]) . '</h6>';
                                                           echo '        <h6 class="m-0">' . htmlspecialchars($row["nome"]) . '</h6>';
                                                            echo '    </td>';
                                                            echo '    <td>';
                                                            echo '        <h6 class="m-0" id="descricao">' . htmlspecialchars($row["descricao"]) . '</h6>';
                                                            echo '    </td>';
                                                            echo '    <td>';
                                                            echo '        <h6 class="m-0" id="data">' . htmlspecialchars($data_formatada) . '</h6>';
                                                            echo '    </td>';
                                                            echo '    <td>';
                                                            echo '        <h6 class="m-0" id="localizacao">' . htmlspecialchars($row["bairro"]) . ' - ' . htmlspecialchars($row["rua"]) . '</h6>';
                                                            echo '    </td>';
                                                            echo '    <td>';
                                                            echo '        <h6 class="m-0">' . htmlspecialchars($row["categoria"]) . '</h6>';
                                                            echo '    </td>';
                                                            echo '    <td>';
                                                            echo '        <div class="dropdown">';
                                                            echo '            <button class="btn btn-sm dropdown-toggle btn-status ' . $statusClass . '" style="background-color: ' . $buttonColor . ';" type="button" id="statusDropdown' . htmlspecialchars($row["id"]) . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
                                                            echo '                ' . $statusText;
                                                            echo '            </button>';
                                                            echo '            <div class="dropdown-menu" aria-labelledby="statusDropdown' . htmlspecialchars($row["id"]) . '">';
                                                            echo '                <a class="dropdown-item" href="#" data-status="1" data-id="' . htmlspecialchars($row["id"]) . '">Aprovado</a>';
                                                            echo '                <a class="dropdown-item" href="#" data-status="2" data-id="' . htmlspecialchars($row["id"]) . '">Negado</a>';
                                                            echo '                <a class="dropdown-item" href="#" data-status="3" data-id="' . htmlspecialchars($row["id"]) . '">Em análise</a>';
                                                            echo '            </div>';
                                                            echo '        </div>';
                                                            echo '    </td>';
                                                            echo '    <td class="text-right"><i class="fas fa-circle ' . $statusClass . ' f-10" id="statusIcon' . htmlspecialchars($row["id"]) . '"></i></td>';
                                                            echo '</tr>';
                                                        }
                                                    } else {
                                                        echo "<tr><td colspan='7'>Nenhuma ocorrência encontrada</td></tr>";
                                                    }
                                                    ?>
                                            </tbody>
                                        </table>
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
    </div>

    <!-- Required Js -->
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
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

    document.addEventListener('DOMContentLoaded', function() {
        var userHasAccess = <?php echo json_encode($user_has_access); ?>;

        function showToast(message, type = 'success') {
            toastr[type](message);
        }

        function updateStatus(element) {

            if (!userHasAccess) {
                showToast("Você não tem permissão para alterar o status.", 'error');
                return;
            }

            const newStatus = element.getAttribute('data-status');
            const newStatusText = element.textContent;
            const occurrenceId = element.getAttribute('data-id');

            // Ajax UPDATE
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'controllers/update_status.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    if (xhr.responseText.trim() === "success") {
                        // Atualizar o css
                        const statusButton = document.querySelectorAll('#statusDropdown' + occurrenceId);
                        const statusIcon = document.querySelectorAll('#statusIcon' + occurrenceId);

                        statusButton.forEach(button => {
                            button.innerHTML = newStatusText;

                            button.classList.remove('text-c-green', 'text-c-red', 'text-c-yellow');
                            button.classList.add('btn-status');

                            if (newStatus == 1) {
                                button.classList.add('text-c-green');
                                button.style.backgroundColor = '#1de9b6';
                            } else if (newStatus == 2) {
                                button.classList.add('text-c-red');
                                button.style.backgroundColor = '#f44236';
                            } else if (newStatus == 3) {
                                button.classList.add('text-c-yellow');
                                button.style.backgroundColor = '#f4c22b';
                            }
                        });

                        statusIcon.forEach(icon => {
                            icon.classList.remove('text-c-green', 'text-c-red', 'text-c-yellow');

                            if (newStatus == 1) {
                                icon.classList.add('text-c-green');
                            } else if (newStatus == 2) {
                                icon.classList.add('text-c-red');
                            } else if (newStatus == 3) {
                                icon.classList.add('text-c-yellow');
                            }
                        });
                        showToast("Status atualizado com sucesso!", 'success');
                    } else {
                        showToast("Erro ao atualizar status: " + xhr.responseText, 'error');
                    }
                }
            };
            xhr.send('id=' + occurrenceId + '&status=' + newStatus);
        }

        const statusDropdowns = document.querySelectorAll('.dropdown-menu a');

        statusDropdowns.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                updateStatus(this);
            });
        });
    });
    </script>

</body>

</html>