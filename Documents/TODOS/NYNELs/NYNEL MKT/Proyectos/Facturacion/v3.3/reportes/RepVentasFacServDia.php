<?php
//recibimos los parametros

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
if ($_SESSION['escritorio']==1)
{
 
//Inlcuímos a la clase PDF_MC_Table
require('PDF_VentasDia.php');

//Comenzamos a crear las filas de los registros según la consulta mysql
require_once "../modelos/Venta.php";
$venta = new Venta();
$rspta = $venta->regventareporteFacServturaDia($_SESSION['idempresa']);
$rspta2 = $venta->regventareportetotalesFacturaServDia( $_SESSION['idempresa']);


require_once "../modelos/Factura.php";
$factura= new Factura();
$datos = $factura->datosemp($_SESSION['idempresa']);
$datose = $datos->fetch_object();


//Instanciamos la clase para generar el documento pdf
$pdf=new PDF_MC_Table();
//Agregamos la primera página al documento pdf

//Cabecera
$pdf->AddPage();
//Seteamos el inicio del margen superior en 25 pixeles 
$y_axis_initial = 25;
//$pdf->Ln(13);

//Implementamos las celdas de la tabla con los registros a mostrar
$pdf->SetWidths(array(8,10,20,25,25,55,20,18,18));
while($reg0= $rspta->fetch_object())
{  
    $fechae = $reg0->fecha;
    $hora = $reg0->hora;
    $tipodoc = $reg0->tipodocu;
    $ncomprobante = $reg0->documento;
    $ndocliente = $reg0->numero_documento;
    $cliente = $reg0->razon_social;
    $subtotal = $reg0->subtotal;
    $igv = $reg0->igv;
    $total = $reg0->total;
    $estado=$reg0->estado;

switch ($estado) {
	case '3':
		$ndocliente="DE BAJA";
        $cliente="DE BAJA";
        $subtotal = '0';
    $igv = '0';
    $total='0';
		break;
	case '0':
        $ndocliente="CON NOTA";
        break;
	default:
		# code...
		break;
}

switch ($tipodoc) {
	case '01':
		$tipod="FACTURA";
		break;
	case '03':
		$tipod="BOLETA";
		break;
	case '07':
		$tipod=utf8_decode("N.CRÉDITO");
		break;
    case '08':
        $tipod=utf8_decode("N.DÉBITO");
        break;
    case '50':
        $tipod="NOTA PED";
        break;
	
	default:
		# code...
		break;
}

//Imprime el detalle de todos los comprobantes
$pdf->SetFont('Arial','',8);
$pdf->Row(array($fechae, '', $tipod, $ncomprobante, $ndocliente, htmlspecialchars_decode($cliente), $subtotal, $igv, $total));
}
//Imprime el detalle de todos los comprobantes
$pdf->Ln(1);
$pdf->SetFont('courier','B',8);
$pdf->Cell(190,5,utf8_decode('----------------------------------------------------------------------------------------------------------------------'),0,0,'C',0) ;  
$pdf->Ln(5);
//===================PARA TOTALES================================================
while($reg2= $rspta2->fetch_object())
{  
    $stotal = $reg2->subtotal;
    $igv = $reg2->igv;
    $total = $reg2->total;
}

//Resumen
$pdf->SetWidths(array(87,55,18,2,18,2,18));
$pdf->SetFont('arial','B',8);
$pdf->Row(array('',utf8_decode('TOTAL FACTURAS (S/): '),number_format($stotal,2),'', number_format($igv,2),'', number_format($total,2)));
//===================PARA TOTALES================================================

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