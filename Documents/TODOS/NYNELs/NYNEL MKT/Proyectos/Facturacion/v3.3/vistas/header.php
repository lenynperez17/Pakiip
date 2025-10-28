<?php
// NOTA: La sesión se inicia automáticamente en Conexion.php mediante iniciarSesionSegura()
// Esto garantiza que la sesión funcione correctamente en todas las páginas
// No es necesario llamar session_start() aquí

// SEGURIDAD: Cargar headers de seguridad HTTP
require_once "../config/security_headers.php";

require_once "../modelos/Consultas.php";
$consulta = new Consultas();

$rsptav = $consulta->totalventahoyFactura($_SESSION['idempresa']);
$regv = $rsptav->fetch_object();
$totalvfacturahoy = $regv->total_venta_factura_hoy;

$rsptav = $consulta->totalventahoyBoleta($_SESSION['idempresa']);
$regv = $rsptav->fetch_object();
$totalvboletahoy = $regv->total_venta_boleta_hoy;

$rsptav = $consulta->totalventahoyNotapedido($_SESSION['idempresa']);
$regv = $rsptav->fetch_object();
$totalvnpedidohoy = $regv->total_venta_npedido_hoy;


$totalventas = $totalvfacturahoy + $totalvboletahoy + $totalvnpedidohoy;
$rsptav = $consulta->insertarVentaDiaria($totalventas);
//echo $rsptav ? "Venta guardada" : "Error al guardar";

?>
<!DOCTYPE html>
<html lang="es" dir="ltr" data-nav-layout="vertical" data-theme-mode="light" data-header-styles="light"
    data-menu-styles="dark" data-toggled="close">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>SISTEMA FACTUFACIL | Facturación electrónica</title>

    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link rel="stylesheet" href="../custom/modules/fontawesome6.1.1/css/all.css">
    <link rel="stylesheet" href="../public/css/factura.css">
    <!-- Favicon -->
    <<link rel="apple-touch-icon" href="https://wfacx.com/tienda/assets/images/logoIcon/logo.png">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black">
        <meta name="apple-mobile-web-app-title" content="wfacx - Home">
        <!-- Choices JS -->
        <script src="../assets/libs/choices.js/public/assets/scripts/choices.min.js"></script>
        <!-- Main Theme Js -->
        <script src="../assets/js/main.js"></script>
        <!-- Bootstrap Css -->
        <link id="style" href="../assets/libs/bootstrap/css/bootstrap.min.css" rel="stylesheet">
        <!-- Style Css -->
        <link href="../assets/css/styles.css" rel="stylesheet">
        <!-- Icons Css -->
        <link href="../assets/css/icons.css" rel="stylesheet">
        <!-- Node Waves Css -->
        <link href="../assets/libs/node-waves/waves.min.css" rel="stylesheet">
        <!-- Simplebar Css -->
        <link href="../assets/libs/simplebar/simplebar.min.css" rel="stylesheet">
        <!-- Color Picker Css -->
        <link rel="stylesheet" href="../assets/libs/flatpickr/flatpickr.min.css">
        <link rel="stylesheet" href="../assets/libs/@simonwep/pickr/themes/nano.min.css">
        <!-- Choices Css -->
        <link rel="stylesheet" href="../assets/libs/choices.js/public/assets/styles/choices.min.css">
        <link rel="stylesheet" href="../assets/libs/jsvectormap/css/jsvectormap.min.css">
        <link rel="stylesheet" href="../assets/libs/swiper/swiper-bundle.min.css">

        <link rel="stylesheet" href="../public/css/toastr.css">

        <link rel="stylesheet" href="../custom/css/custom.css">

        <!-- DATATABLES -->
        <link rel="stylesheet" type="text/css" href="../public/datatables/jquery.dataTables.min.css">
        <!-- <link href="../public/datatables/buttons.dataTables.min.css" rel="stylesheet" />
    <link href="../public/datatables/responsive.dataTables.min.css" rel="stylesheet" /> -->

        <link rel="stylesheet" href="../public/css/autobusqueda.css">
        <link rel="stylesheet" href="style.css">

        <!-- Bootstrap Select CSS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>

</head>


<body>

    <input type="hidden" name="iva" id="iva" value='<?php echo $_SESSION['iva']; ?>'>

    <?php include_once "template/switcher.php" ?>

    <!-- Loader -->
    <div id="loader">
        <img src="../assets/images/media/loader.svg" alt="">
    </div>
    <!-- Loader -->
    <div class="page">

        <?php include_once "template/app-header.php" ?>

        <?php include_once "template/sidebar.php" ?>

        <div class="main-content app-content">
            <div class="container-fluid">