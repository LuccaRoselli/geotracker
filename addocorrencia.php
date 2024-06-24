<?php

// Iniciando sessão
session_start();

// Incluindo config
require_once 'controllers/config.php';

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

// Obtenha as categorias da tabela categorias
$sql = "SELECT id, categoria FROM categorias WHERE enabled = 1";
$result = $conn->query($sql);

// Verifique se há ações de registro de ocorrência
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['nameInput']) && isset($_POST['descInput']) && isset($_POST['bairroInput']) && isset($_POST['ruaInput']) && isset($_POST['categoriaInput']) && isset($_POST['latitude']) && isset($_POST['longitude'])) {
        $name = $_POST['nameInput'];
        $desc = $_POST['descInput'];
        $bairro = $_POST['bairroInput'];
        $rua = $_POST['ruaInput'];
        $categoria = intval($_POST['categoriaInput']);
        $latitude = $_POST['latitude'];
        $longitude = $_POST['longitude'];
        $email = $_SESSION['email'];

        $sql_insert = "INSERT INTO ocorrencias (email, nome, descricao, bairro, rua, categoria, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql_insert);
        $stmt->bind_param("ssssssdd",  $email, $name, $desc, $bairro, $rua, $categoria, $latitude, $longitude);
        $stmt->execute();
        $stmt->close();

        header("location: ocorrencias");
    }
}

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

// Verifique o tipo de usuário
$user_has_access = $_SESSION["user_type"] == 3 || $_SESSION["user_type"] == 4;
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
                    <li data-username="Table bootstrap datatable footable" class="nav-item active">
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


    <?php if ($user_has_access): ?>
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
                                        <h5 class="m-b-10">Editando perfil</h5>
                                    </div>
                                    <ul class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="index"><i
                                                    class="feather icon-home"></i></a></li>
                                        <li class="breadcrumb-item"><a href="javascript:">Inserindo nova ocorrência</a>
                                        </li>
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
                                <div class="col-sm-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5>Inserindo nova ocorrência</h5>
                                            <hr>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <form action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>"
                                                        method="post">
                                                        <div class="form-group">
                                                            <label for="emailInput">Endereço de email</label>
                                                            <input type="email" class="form-control" id="emailInput"
                                                                aria-describedby="emailHelp"
                                                                value="<?php echo $_SESSION['email']; ?>" disabled>
                                                            <small id="emailHelp" class="form-text text-muted">Nós nunca
                                                                compartilharemos seu e-mail com outra
                                                                instituição/alguém.</small>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="nameInput">Nome do registrante</label>
                                                            <input type="text" class="form-control" id="nameInput"
                                                                name="nameInput"
                                                                placeholder="Digite o nome do registrante" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="descInput">Descrição da ocorrência</label>
                                                            <textarea class="form-control" id="descInput"
                                                                name="descInput"
                                                                placeholder="Digite a descrição da ocorrência"
                                                                required></textarea>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="addressInput">Endereço</label>
                                                            <input type="text" class="form-control" id="addressInput"
                                                                name="addressInput" placeholder="Digite o endereço"
                                                                required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="bairroInput">Bairro</label>
                                                            <input type="text" class="form-control" id="bairroInput"
                                                                name="bairroInput" placeholder="Digite o bairro"
                                                                required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="ruaInput">Rua</label>
                                                            <input type="text" class="form-control" id="ruaInput"
                                                                name="ruaInput" placeholder="Digite a rua" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="latitudeInput">Latitude</label>
                                                            <input type="text" class="form-control" id="latitudeInput"
                                                                name="latitude" readonly>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="longitudeInput">Longitude</label>
                                                            <input type="text" class="form-control" id="longitudeInput"
                                                                name="longitude" readonly>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="categoriaInput">Categoria</label>
                                                            <select class="form-control" id="categoriaInput"
                                                                name="categoriaInput" required>
                                                                <?php
                                                            if ($result->num_rows > 0) {
                                                                while($row = $result->fetch_assoc()) {
                                                                    echo "<option value='" . htmlspecialchars($row["id"]) . "'>" . htmlspecialchars($row["categoria"]) . "</option>";
                                                                }
                                                            } else {
                                                                echo "<option value=''>Nenhuma categoria encontrada</option>";
                                                            }
                                                            ?>
                                                            </select>
                                                        </div>
                                                        <button type="submit" class="btn btn-primary">Registrar nova
                                                            ocorrência</button>
                                                    </form>
                                                </div>
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
    </div>
    <?php endif; ?>

    <!-- Required Js -->
    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/pcoded.min.js"></script>

    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBzYzK9rUADsptmaFh0hwodf_ex_H8HJSk&libraries=places">
    </script>
    <script>
    function initAutocomplete() {
        var input = document.getElementById('addressInput');
        var autocomplete = new google.maps.places.Autocomplete(input);

        autocomplete.addListener('place_changed', function() {
            var place = autocomplete.getPlace();
            var addressComponents = place.address_components;

            var rua = '';
            var bairro = '';

            for (var i = 0; i < addressComponents.length; i++) {
                var component = addressComponents[i];
                if (component.types.includes('route')) {
                    rua = component.long_name;
                }
                if (component.types.includes('sublocality') || component.types.includes('neighborhood')) {
                    bairro = component.long_name;
                }
            }

            document.getElementById('ruaInput').value = rua;
            document.getElementById('bairroInput').value = bairro;
            document.getElementById('latitudeInput').value = place.geometry.location.lat();
            document.getElementById('longitudeInput').value = place.geometry.location.lng();
        });
    }

    google.maps.event.addDomListener(window, 'load', initAutocomplete);
    </script>

</body>

</html>