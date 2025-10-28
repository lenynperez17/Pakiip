<?php
//recibimos los parametros
$ano=$_POST['ano'];
$mes=$_POST['mes'];
$tmon=$_POST['tmonedaa'];


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
require('PDF_MC_Table4.php');

//Comenzamos a crear las filas de los registros según la consulta mysql
require_once "../modelos/Venta.php";
$venta = new Venta();
$rspta = $venta->regventaagruxdia($ano,$mes, $_SESSION['idempresa'], $tmon);
$rspta2 = $venta->regventareportetotales($ano,$mes, $_SESSION['idempresa'], $tmon);

$rspta3 = $venta->regventaagruxdianotap($ano,$mes, $_SESSION['idempresa']);
$rspta4 = $venta->regventareportetotalesnotap($ano,$mes, $_SESSION['idempresa']);

	require_once "../modelos/Compra.php";
	$compra= new Compra();
    $datos = $compra->datosemp($_SESSION['idempresa']);
    $datose = $datos->fetch_object();



//Instanciamos la clase para generar el documento pdf
$pdf=new PDF_MC_Table();
 
//Agregamos la primera página al documento pdf
$pdf->AddPage();

#Establecemos los márgenes izquierda, arriba y derecha: 
$pdf->SetMargins(18, 10 , 10); 

#Establecemos el margen inferior: 
$pdf->SetAutoPageBreak(true,10);
 
//Seteamos el inicio del margen superior en 25 pixeles 
$y_axis_initial = 25;

//Seteamos el tipo de letra y creamos el título de la página. No es un encabezado no se repetirá
$pdf->SetFont('Arial','B',8);
$pdf->Cell(40,6,'',0,0,'C');
$pdf->Cell(-20,2,utf8_decode($datose->nombre_comercial),0,0,'C'); 
//$pdf->SetFont('arial','B',8);
//$pdf->Cell(30,10,'REPORTE DE VENTAS AGRUPADO POR DÍA',0,0,'C'); 

$pdf->SetFont('Arial','B',8);
$pdf->Cell(40,6,'',0,0,'C');
$pdf->Cell(160,2,'RUC: '.$datose->numero_ruc,0,0,'C'); 
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

$pdf->SetFont('Arial','B',12);
$pdf->Cell(40,6,'',0,0,'C');
$pdf->Cell(80,2,utf8_decode('REPORTE DE VENTAS AGRUPADO POR DÍA DE ').$mesL.' DEL '.$ano,0,0,'C'); 
$pdf->Ln(4);
$pdf->SetFont('Arial','B',10);
$pdf->Cell(80,10,utf8_decode('COMPROBANTES ELECTRONICOS'),0,0,'C'); 


$pdf->Ln(1);

$pdf->SetFont('COURIER','B',10);
$pdf->Cell(20,20,utf8_decode('DÍA'),0,0,'C',0) ;  
$pdf->Cell(70,20,utf8_decode('VALOR AFECTO '),0,0,'C',0) ;  
$pdf->Cell(1,20,utf8_decode('IGV'),0,0,'C',0) ;  
$pdf->Cell(85,20,utf8_decode('TOTAL'),0,0,'C',0) ;  


$pdf->Ln(1);
$pdf->SetFont('courier','',8);
//$pdf->Cell(187,22,utf8_decode('====================================================================================='),0,0,'L',0) ;  

$pdf->Ln(12);
 
//Implementamos las celdas de la tabla con los registros a mostrar
$pdf->SetWidths(array(30,15,25,15,25,15,25));
 

while($reg0= $rspta->fetch_object())
{  
    $fechae = $reg0->fecha;
    $subtotal = $reg0->subtotal;
    $igv = $reg0->igv;
    $total = $reg0->total;

//Imprime el detalle de todos los comprobantes
$pdf->SetFont('COURIER','',10);
$pdf->Row(array($fechae,'', $subtotal,'', $igv,'', $total));
}
//Imprime el detalle de todos los comprobantes


$pdf->Ln(1);
$pdf->SetFont('courier','',8);
$pdf->Cell(185,5,utf8_decode('====================================================================================='),0,0,'L',0) ;  
$pdf->Ln(2);
//===================PARA TOTALES================================================
while($reg2= $rspta2->fetch_object())
{  
    $stotal = $reg2->subtotal;
    $igv = $reg2->igv;
    $total = $reg2->total;
}
//Resumen
$pdf->SetWidths(array(40,3,25,16,25,15,25));
$pdf->SetFont('courier','B',9);
$pdf->Row(array(utf8_decode('TOTAL'),'',number_format($stotal,2),'', number_format($igv,2),'', number_format($total,2)));    
//===================PARA TOTALES================================================



// PARA NOTAS DE PEDIDO
$pdf->SetFont('Arial','B',10);
$pdf->Ln(4);
$pdf->Cell(30,10,utf8_decode('NOTAS DE PEDIDO'),0,0,'C'); 

$pdf->Ln(1);
$pdf->SetFont('COURIER','B',10);
$pdf->Cell(20,20,utf8_decode('DÍA'),0,0,'C',0) ;  
$pdf->Cell(30,20,utf8_decode('TOTAL'),0,0,'C',0) ;  
$pdf->Ln(1);
$pdf->SetFont('courier','',8);
//$pdf->Cell(187,22,utf8_decode('====================================================================================='),0,0,'L',0) ;  
$pdf->Ln(12);
 
//Implementamos las celdas de la tabla con los registros a mostrar
$pdf->SetWidths(array(30, 25));


while($reg3= $rspta3->fetch_object())
{  
    $fechae = $reg3->fecha;
    $total = $reg3->total;
//Imprime el detalle de todos los comprobantes
$pdf->SetFont('COURIER','',10);
$pdf->Row(array($fechae,  $total));
}
//Imprime el detalle de todos los comprobantes
$pdf->ln(1);
$pdf->SetFont('courier','',8);
$pdf->Cell(185,5,utf8_decode('==========================='),0,0,'L',0) ;  
$pdf->ln(2);

//===================PARA TOTALES================================================
while($reg4= $rspta4->fetch_object())
{  
    $total = $reg4->total;
}
//Resumen
$pdf->SetWidths(array(30, 25));
$pdf->SetFont('courier','B',9);
$pdf->Row(array(utf8_decode('TOTAL'),
 				number_format($total,2)));    


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