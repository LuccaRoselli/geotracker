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

// Obtenha os dados de latitude, longitude e descrição da tabela de ocorrências
$sql = "SELECT o.id, o.nome, o.latitude, o.longitude, o.descricao, c.categoria AS categoria_nome
        FROM ocorrencias o 
        JOIN categorias c ON o.categoria = c.id
        WHERE o.status = 1 and o.latitude IS NOT NULL AND o.longitude IS NOT NULL";
$result = $conn->query($sql);

$locations = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $locations[] = [
            'name' => $row['nome'],
            'lat' => floatval($row['latitude']),
            'lng' => floatval($row['longitude']),
            'description' => $row['descricao'],
            'category_name' => $row['categoria_nome'],
        ];
    }
}

$conn->close();

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
                    <li data-username="Maps Google" class="nav-item active">
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
                                        <h5 class="m-b-10">Mapa de ocorrências</h5>
                                    </div>
                                    <ul class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="index.html"><i
                                                    class="feather icon-home"></i></a></li>
                                        <li class="breadcrumb-item"><a href="javascript:">Você está visualizando o
                                                mapa</a></li>
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
                                <!-- [ basic-map ] start -->
                                <div class="col-lg-12 col-xl-12">
                                    <div class="card">
                                        <div class="card-block">
                                            <div id="map" class="set-map" style="height:900px;"></div>
                                        </div>
                                    </div>
                                </div>
                                <!-- [ basic-map ] end -->
                            </div>
                            <!-- [ Main Content ] end -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Required Js -->
    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>


    <!-- google-map Js -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBzYzK9rUADsptmaFh0hwodf_ex_H8HJSk"></script>
    <script>
    // Dados de localização do PHP
    var locations = <?php echo json_encode($locations); ?>;

    function initMap() {
        // Cria um novo mapa centralizado na primeira localização ou em uma localização padrão
        var mapCenter = {
            lat: -22.805740752883917,
            lng: -45.1861641891689
        }; // São Paulo, Brasil
        if (locations.length > 0) {
            mapCenter = {
                lat: locations[0].lat,
                lng: locations[0].lng
            };
        }

        var map = new google.maps.Map(document.getElementById('map'), {
            center: mapCenter,
            zoom: 10
        });

        // Adiciona pinos no mapa para cada localização
        locations.forEach(function(location) {
            var marker = new google.maps.Marker({
                position: {
                    lat: location.lat,
                    lng: location.lng
                },
                map: map,
                title: location.category_name
            });

            // Adiciona uma janela de informação para cada pino
            var infowindow = new google.maps.InfoWindow({
                content: '<h5><b>' + location.category_name + '</b></h5><h6><b>AUTOR</b>: ' + location
                    .name + '</h6><p>' + location.description + '</p>'
            });

            marker.addListener('click', function() {
                infowindow.open(map, marker);
            });
        });
    }

    // Inicializa o mapa
    google.maps.event.addDomListener(window, 'load', initMap);
    </script>

</body>

</html>