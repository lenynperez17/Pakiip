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
//if ($_SESSION['inventarios']==1)
//{
 
//Inlcuímos a la clase PDF_MC_Table
require('PDF_MC_Table6.php');

require_once "../modelos/Compra.php";
$compra = new Compra();
$datos = $compra->datosemp($_SESSION['idempresa']);
$datose = $datos->fetch_object();


$rspta = $compra->regcompra($ano,$mes, $moneda, $_SESSION['idempresa']);
$rspta2 = $compra->totalregcompraReporte($ano,$mes, $moneda,  $_SESSION['idempresa']);


//Instanciamos la clase para generar el documento pdf
$pdf=new PDF_MC_Table();
 
//Agregamos la primera página al documento pdf
$pdf->AddPage();
 
//Seteamos el inicio del margen superior en 25 pixeles 
$y_axis_initial = 25;

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
$pdf->Row(array('',utf8_decode('TOTALES: '),number_format($stotal,2),'', number_format($igv,2),'', number_format($total,2)));    


//===================PARA TOTALES================================================

 
//Mostramos el documento pdf
$pdf->Output();

//}
//else
//{
  //echo 'No tiene permiso para visualizar el reporte';
//}
 
}
ob_end_flush();
?>