<?php
//recibimos los parametros
$ano=$_POST['ano'];
$mes=$_POST['mes'];
$moneda=$_POST['moneda'];

//Activamos el almacenamiento en el buffer
ob_start();
if (strlen(session_id()) < 1) 
  session_start();
 
if (!isset($_SESSION["nombre"]))
{
  echo 'Debe ingresar al sistema correctamente para visualizar el reporte';
}
else
{
if ($_SESSION['inventarios']==1)
{
 
//Inlcuímos a la clase PDF_MC_Table
require('PDF_MC_Table.php');

//Comenzamos a crear las filas de los registros según la consulta mysql
require_once "../modelos/Compra.php";
$compra = new Compra();

$datos = $compra->datosemp();
$datose = $datos->fetch_object();

$rspta = $compra->regcompra($ano,$mes, $moneda);
$rspta2 = $compra->totalregcompraReporte($ano,$mes, $moneda);


//Instanciamos la clase para generar el documento pdf
$pdf=new PDF_MC_Table();
 
//Agregamos la primera página al documento pdf
$pdf->AddPage();
 
//Seteamos el inicio del margen superior en 25 pixeles 
$y_axis_initial = 25;

//Seteamos el tipo de letra y creamos el título de la página. No es un encabezado no se repetirá
$pdf->SetFont('arial','B',10);
$pdf->Cell(40,6,'',0,0,'C');
$pdf->Cell(-50,2,utf8_decode($datose->nombre_razon_social),0,0,'C'); 


$pdf->SetFont('arial','B',10);
$pdf->Cell(40,6,'',0,0,'C');
$pdf->Cell(280,2,'RUC: '.$datose->numero_ruc,0,0,'C'); 
$pdf->Ln(13);

$mesL="";
switch ($_POST['mes']) {
	case '01':
		$mesL="ENERO";
		break;
	case '02':
		$mesL="FEBRERO";
		break;
	case '03':
		$mesL="MARZO";
		break;
		case '04':
		$mesL="ABRIL";
		break;
		case '05':
		$mesL="MAYO";
		break;
		case '06':
		$mesL="JUNIO";
		break;
		case '07':
		$mesL="JULIO";
		break;
		case '08':
		$mesL="AGOSTO";
		break;
		case '09':
		$mesL="SEPTIEMBRE";
		break;
		case '10':
		$mesL="OCTUBRE";
		break;
		case '11':
		$mesL="NOVIEMBRE";
		break;
		case '12':
		$mesL="DICIEMBRE";
		break;

	default:
		# code...
		break;
}

$moneda2="";
if ($moneda=='dolar') {
	$moneda2='DOLARES';
$pdf->SetFont('Arial','B',12);
$pdf->Cell(40,6,'',0,0,'C');
$pdf->Cell(100,2,'REGISTRO DE COMPRAS EN '.$moneda2.' DEL MES DE '.$mesL.' DEL '.$ano,0,0,'C'); 
$m3='$';
}else{
	$moneda2='SOLES';
$pdf->SetFont('Arial','B',12);
$pdf->Cell(40,6,'',0,0,'C');
$pdf->Cell(100,2,'REGISTRO DE COMPRAS EN '.$moneda2.' DEL MES DE '.$mesL.' DEL '.$ano,0,0,'C'); 
$m3='S/';
}





$pdf->Ln(1);

$pdf->SetFont('arial','B',8);
$pdf->Cell(5,20,utf8_decode('DIA'),0,0,'C',0) ;  
$pdf->Cell(20,20,utf8_decode('TIP\DOC'),0,0,'C',0) ;  
$pdf->Cell(20,20,utf8_decode('SERIE'),0,0,'C',0) ;  
$pdf->Cell(20,20,utf8_decode('NUMERO'),0,0,'C',0) ;  
$pdf->Cell(20,20,utf8_decode('RUC'),0,0,'C',0) ;  
$pdf->Cell(25,20,utf8_decode('PROVEEDOR'),0,0,'C',0) ;  
$pdf->Cell(35,20,utf8_decode('BASE '.$m3),0,0,'C',0) ;  
$pdf->Cell(15,20,utf8_decode('IGV '.$m3),0,0,'C',0) ;  
$pdf->Cell(35,20,utf8_decode('TOTAL '.$m3),0,0,'C',0) ;  

$pdf->Ln(1);
$pdf->SetFont('courier','B',8);
$pdf->Cell(192,22,utf8_decode('-----------------------------------------------------------------------------------------------------------------'),0,0,'C',0) ;  

$pdf->Ln(12);
 

//Implementamos las celdas de la tabla con los registros a mostrar
$pdf->SetWidths(array(10,2,10,10,15,2,15,2,18,2,35,2,18,7,18,7,18));
 

while($reg0= $rspta->fetch_object())
{  
    $fechae = $reg0->fecha;
    $tipodoc = $reg0->tipo_documento;
    $serie = $reg0->serie;
    $numero = $reg0->numero;
    $ruc = $reg0->numero_documento;
    $proveedor = $reg0->razon_social;
    $subtotal = $reg0->subtotal;
    $igv = $reg0->igv;
    $total=$reg0->total;


switch ($tipodoc) {
	case '01':
		$tipod="FACTURA";
		break;
	
	default:
		# code...
		break;
}


//Imprime el detalle de todos los comprobantes
$pdf->SetFont('arial','',7);
$pdf->Row(array($fechae,' ', $tipodoc,' ', $serie,' ', $numero,' ', $ruc,' ', $proveedor,' ', $subtotal,' ', $igv,' ', $total));
}
//Imprime el detalle de todos los comprobantes


$pdf->Ln(1);
$pdf->SetFont('courier','B',8);
$pdf->Cell(190,-5,utf8_decode('------------------------------------------------------------------------------------------------------------------'),0,0,'C',0) ;  
$pdf->Ln(1);
//===================PARA TOTALES================================================
while($reg2= $rspta2->fetch_object())
{  
    $stotal = $reg2->subtotal;
    $igv = $reg2->igv;
    $total = $reg2->total;
}
//Resumen
$pdf->SetWidths(array(58,60,20,5,20,5,20));
$pdf->SetFont('arial','B',10);
$pdf->Row(array('',utf8_decode('TOTALES GENERALES: '.$m3),number_format($stotal,2),'', number_format($igv,2),'', number_format($total,2)));    


//===================PARA TOTALES================================================

$pdf->AliasNbPages();
$pdf->SetY(266);
$pdf->SetFont('Arial','I',8);
$pdf->Cell(0,10,utf8_decode('Página').$pdf->PageNo().'/{nb}',10,20,'C');

 
//Mostramos el documento pdf
$pdf->Output();

}
else
{
  echo 'No tiene permiso para visualizar el reporte';
}
 
}
ob_end_flush();
?>