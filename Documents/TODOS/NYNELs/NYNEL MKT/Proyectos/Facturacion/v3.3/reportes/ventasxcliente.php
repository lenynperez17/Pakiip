<?php
//Recibimos el número de documento
$ndoc=$_POST['nruc'];
$tipo=$_POST['tipo'];

$ano=$_POST['anor'];
$mes=$_POST['mesr'];



$numerofac="";
$fechaemision="";
$codigo="";
$nombre="";
$cantidad="";
$subtotal="";
$igv="";
$total="";
$numero_documento="";		//+'&idempresa='$idempresa
$razon_social="";
$um="";
//Activamos el almacenamiento en el buffer

ob_start();
if(strlen(session_id())<1)
session_start();
if (!isset($_SESSION["nombre"]))
{
  echo 'Debe ingresar al sistema correctamente para visualizar el reporte';
}
else
{
//if ($_SESSION['inventarios']==1)
//{

//incluimos a la clase PDF
	require('PDF_MC_Table2.php');

//Creamos las filas de los registros 
	require_once "../modelos/Venta.php";
	$venta= new Venta();

	//Instanciamos la clase para generar el documento pdf
	$pdf=new PDF_MC_Table('P', 'mm', 'A4');
	#Establecemos los márgenes izquierda, arriba y derecha: 
		$pdf->SetMargins(10, 10 , 10); 
		#Establecemos el margen inferior: 
		$pdf->SetAutoPageBreak(true,10); 
	//Agregamos la primera pagina al documento pdf
	$pdf->AddPage();
	//Seteamos el inicio del margen superior en 25 pixeles
	$y_axis_initial=25;

		


//Inicio

if ($tipo=="RUC") {
	//$rspta= $venta->ventasxCliente($ndoc);
	$rspta2= $venta->ventasxCliente($ndoc,  $ano, $mes);
	$rspta3= $venta->ventasxClienteTotales($ndoc,  $ano, $mes);
	$rspta4= $venta->ventasxClienteTotalesCantidad($ndoc,  $ano, $mes);

//Implementamos las celdas de la tabla con los registros a mostrar
$pdf->SetWidths(array(25,20,15,45,20,15,15,15,15));

while($reg2=$rspta2->fetch_object())
{
	$numerofac=$reg2->numerofac;
	$fechaemision=$reg2->fechaemision;
	$codigo=$reg2->codigo;
	$nombre=$reg2->nombre;
	$cantidad=$reg2->cantidad;
	$subtotal=$reg2->subtotal;
	$igv=$reg2->igv;
	$icbper=$reg2->icbper;
	$total=$reg2->total;
	$unidad_medida=$reg2->unidad_medida;
	$descripcion=$reg2->descdet;

	$pdf->SetFont('courier','',7);
	$pdf->Row(array($numerofac, utf8_decode($fechaemision), utf8_decode($codigo), utf8_decode($nombre) ." ".utf8_decode($descripcion), $cantidad." ".$unidad_medida, number_format($subtotal,2), number_format($igv,2),'' ,number_format($total,2)));
}


$pdf->Ln(2);

$pdf->SetFillColor(232,232,232); 
$pdf->SetFont('courier','b',8);
$pdf->Cell(180,6,utf8_decode('XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX'),0,0,'C',0) ; 
$pdf->Ln(5);

while($reg3=$rspta3->fetch_object())
{
	$subtotal=$reg3->subtotal;
	$igv=$reg3->igv;
	$icbper2=$reg3->icbper;
	$total=$reg3->total;
	//$totalcanti=$reg3->tcantidad;
}

while($reg4=$rspta4->fetch_object())
{
	$totalcanti=$reg4->tcantidad;
}

//Creamos las celdas para los títulos de cada columna y le asignamos un fondo gris y el tipo de letra
$pdf->SetFillColor(232,232,232); 
$pdf->SetFont('arial','b',7);
$pdf->Cell(105,6,utf8_decode('TOTAL S/: '),0,0,'C',0) ; 
$pdf->Cell(20,6,utf8_decode('').number_format($totalcanti,2),0,0,'C',0) ; 
$pdf->Cell(15,6,utf8_decode('').number_format($subtotal,2),0,0,'C',0) ; 
$pdf->Cell(15,6,utf8_decode('').number_format($igv,2),0,0,'C',0) ; 
$pdf->Cell(15,6,utf8_decode('').number_format($icbper2,2),0,0,'C',0) ; 
$pdf->Cell(15,6,utf8_decode('').number_format($total,2),0,0,'C',0) ;

}
elseif ($tipo=="RUCAG" ) // SI ES RUC AGRUPADO POR NUMERO DE FACTURA   
{
	$rspta2= $venta->ventasxClienteAgrupado($ndoc, $ano, $mes);
	$rspta3= $venta->ventasxClienteTotales($ndoc, $ano, $mes);
	$rspta4= $venta->ventasxClienteTotalesCantidad($ndoc,$ano, $mes);

//Implementamos las celdas de la tabla con los registros a mostrar
$pdf->SetWidths(array(25,20,15,45,18,15,15,15,15));

while($reg2=$rspta2->fetch_object())
{
	$numerofac=$reg2->numerofac;
	$fechaemision=$reg2->fechaemision;
	//$codigo=$reg2->codigo;
	//$nombre=$reg2->nombre;
	$cantidad=$reg2->cantidad;
	$subtotal=$reg2->subtotal;
	$igv=$reg2->igv;
	$icbper=$reg2->icbper;
	$total=$reg2->total;
	$unidad_medida=$reg2->unidad_medida;

	$pdf->SetFont('Arial','',8);
	$pdf->Row(array($numerofac, $fechaemision, 'AGRUP.', 'AGRUPADO' , $cantidad." ".$unidad_medida, $subtotal, 
		$igv,  $icbper  ,$total));
}


$pdf->Ln(2);

$pdf->SetFillColor(232,232,232); 
$pdf->SetFont('courier','b',8);
$pdf->Cell(180,6,utf8_decode('XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX'),0,0,'C',0) ; 
$pdf->Ln(5);

while($reg3=$rspta3->fetch_object())
{
	$subtotal=$reg3->subtotal;
	$igv=$reg3->igv;
	$icbper2=$reg3->icbper;
	$total=$reg3->total;
	//$totalcanti=$reg3->tcantidad;
}

while($reg4=$rspta4->fetch_object())
{
	$totalcanti=$reg4->tcantidad;
}

//Creamos las celdas para los títulos de cada columna y le asignamos un fondo gris y el tipo de letra
$pdf->SetFillColor(232,232,232); 
$pdf->SetFont('arial','b',8);
$pdf->Cell(104,6,utf8_decode('TOTAL S/: '),0,0,'C',0) ; 
$pdf->Cell(20,6,utf8_decode('').number_format($totalcanti,2),0,0,'C',0) ; 
$pdf->Cell(15,6,utf8_decode('').number_format($subtotal,2),0,0,'C',0) ; 
$pdf->Cell(15,6,utf8_decode('').number_format($igv,2),0,0,'C',0) ; 
$pdf->Cell(15,6,utf8_decode('').number_format($icbper2,2),0,0,'C',0) ; 
$pdf->Cell(15,6,utf8_decode('').number_format($total,2),0,0,'C',0) ; 



}
else //======================ELSE SI ES DNI BOLETA===============================
{
	//$rspta= $venta->ventasxClienteDni($ndoc);
	$rspta2= $venta->ventasxClienteDni($ndoc, $_SESSION['idempresa']);
	$rspta3= $venta->ventasxClienteTotalesDni($ndoc, $_SESSION['idempresa']);
	$rspta4= $venta->ventasxClienteTotalesDniCantidad($ndoc, $_SESSION['idempresa']);



//Implementamos las celdas de la tabla con los registros a mostrar
$pdf->SetWidths(array(25,20,15,60,20,15,15,15));

while($reg2=$rspta2->fetch_object())
{

	

	$numerofac=$reg2->numerobol;
	$fechaemision=$reg2->fechaemision;
	$codigo=$reg2->codigo;
	$nombre=$reg2->nombre;
	$cantidad=$reg2->cantidad;
	$subtotal=$reg2->subtotal;
	$igv=$reg2->igv;
	$total=$reg2->total;
	$unidad_medida=$reg2->unidad_medida;

	$pdf->SetFont('courier','',8);
	$pdf->Row(array($numerofac, utf8_decode($fechaemision), utf8_decode($codigo), utf8_decode($nombre) , utf8_decode($cantidad)." ".$unidad_medida,$subtotal,$igv, $total));

}
// $um="";
// $um=$unidad_medida;

$pdf->Ln(2);

$pdf->SetFillColor(232,232,232); 
$pdf->SetFont('courier','b',8);
$pdf->Cell(180,6,utf8_decode('XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX'),0,0,'C',0) ; 
$pdf->Ln(5);

while($reg3=$rspta3->fetch_object())
{
	$subtotal=$reg3->subtotal;
	$igv=$reg3->igv;
	$total=$reg3->total;
	//$totalcanti=$reg3->tcantidad;
}

while($reg4=$rspta4->fetch_object())
{
	$totalcanti=$reg4->tcantidad;
}

//Creamos las celdas para los títulos de cada columna y le asignamos un fondo gris y el tipo de letra
$pdf->SetFillColor(232,232,232); 
$pdf->SetFont('arial','b',9);
$pdf->Cell(115,6,utf8_decode('TOTAL S/: '),0,0,'C',0) ; 
$pdf->Cell(20,6,utf8_decode('').number_format($totalcanti,2)." ".$um,0,0,'C',0) ; 
$pdf->Cell(15,6,utf8_decode('').number_format($subtotal,2),0,0,'C',0) ; 
$pdf->Cell(15,6,utf8_decode('').number_format($igv,2),0,0,'C',0) ; 
$pdf->Cell(15,6,utf8_decode('').number_format($total ,2),0,0,'C',0) ; 


}

//mostramos el documento pdf
$pdf->Output();
//}
//else
//{
//	echo 'No tiene permiso de visualizar este reporte';
//}

}
ob_end_flush();
?>




