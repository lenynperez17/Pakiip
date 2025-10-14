<?php
require_once "../config/Conexion.php";
require_once "../modelos/Cajachica.php";

$cajachica=new Cajachica();

$idsaldoini=isset($_POST["idsaldoini"])? limpiarCadena($_POST["idsaldoini"]):"";
$total_venta=isset($_POST["total_venta"])? limpiarCadena($_POST["total_venta"]):"";
$saldo_inicial=isset($_POST["saldo_inicial"])? limpiarCadena($_POST["saldo_inicial"]):"";
// $codigo=isset($_POST["codigo"])? limpiarCadena($_POST["codigo"]):"";
// $descripcion=isset($_POST["descripcion"])? limpiarCadena($_POST["descripcion"]):"";

if (isset($_GET['action'])) {
	$action = $_GET['action'];
  } else {
	$action = '';
  }

  //total de ventas
  if ($action == 'TotalVentas') {
	$rspta = $cajachica->TotalVentas();
	$data = array();
  
	while ($reg=$rspta->fetch_object()){
        $data[]=array(
			"total_venta"=>$reg->total_venta
        );
    }
	$results = array(
		"aaData"=>$data
	);
	
	header('Content-type: application/json');
	echo json_encode($results);
	exit;
  }

  //Total gastos

  if ($action == 'TotalGastos') {
	$rspta = $cajachica->verEgresos();
	$data = array();
  
	while ($reg=$rspta->fetch_object()){
        $data[]=array(
			"total_gasto"=>$reg->total_gasto
        );
    }
	$results = array(
		"aaData"=>$data
	);
	
	header('Content-type: application/json');
	echo json_encode($results);
	exit;
  }


  //total ingresos


  if ($action == 'TotalIngresos') {
	$rspta = $cajachica->verIngresos();
	$data = array();
  
	while ($reg=$rspta->fetch_object()){
        $data[]=array(
			"total_ingreso"=>$reg->total_ingreso
        );
    }
	$results = array(
		"aaData"=>$data
	);
	
	header('Content-type: application/json');
	echo json_encode($results);
	exit;
  }


  if ($action == 'SaldoInicial') {
	$rspta = $cajachica->verSaldoini();
	$data = array();
  
	while ($reg=$rspta->fetch_object()){
        $data[]=array(
			"idsaldoini"=>$reg->idsaldoini,
			"total_ingreso"=>$reg->saldo_inicial
        );
    }
	$results = array(
		"aaData"=>$data
	);
	
	header('Content-type: application/json');
	echo json_encode($results);
	exit;
  }


  if ($action == 'listarcierre') {
	$rspta = $cajachica->listarCierre();
	$data = array();
  
	while ($reg=$rspta->fetch_object()){
        $data[]=array(
			'fecha_cierre' => $reg->fecha_cierre,
            'total_ingreso' => $reg->total_ingreso,
            'total_gasto' => $reg->total_gasto,
			'saldo_inicial' => $reg->saldo_inicial,
            'total_caja' => $reg->total_caja
        );
    }
	$results = array(
		"aaData"=>$data
	);
	
	header('Content-type: application/json');
	echo json_encode($results);
	exit;
  }


  if (isset($_GET["op"])) {
  switch ($_GET["op"]){
    case 'guardaryeditar':
        if ($cajachica->existeSaldoInicialDiaActual()) {
            echo "Ya existe un saldo inicial registrado para hoy, no se puede registrar otro.";
        } else {
            $resultado = $cajachica->insertarSaldoInicial($saldo_inicial);
            echo $resultado ? "Saldo registrado" : "Saldo no se pudo registrar";
        }
    break;

	
	case 'cerrarcaja':
		$cajachica->resetearValoresCierreCaja();
		$resultado = $cajachica->cerrarCaja();
		echo $resultado ? "Caja cerrada" : "No se pudo cerrar la caja";
		break;

  } // Cierre del switch
  } // Cierre del if (isset($_GET["op"]))

?>