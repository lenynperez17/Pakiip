<?php
//Recibimos el número de documento
//$idcompra=$_POST['idcompra'];


$numero="";
$fecha="";
$proveedor="";
$usuario="";
$tdocumento="";
$estado="";

$codigoprove="";
$descripcion="";
$vunitario="";
$cantidad="";
$subtotal="";
//Activamos el almacenamiento en el buffer

ob_start();
if(strlen(session_id())<1)
session_start();
if (!isset($_SESSION["nombre"]))
{
  echo 'Debe ingresar al sistema correctamente para visualizar el documento';
}
else
{
//if ($_SESSION['compras']==1)
//{

//incluimos a la clase PDF
	require('PDF_MC_Table_Compra.php');

//Creamos las filas de los registros 
	require_once "../modelos/Compra.php";
	$compra= new Compra();
    $datos = $compra->datosemp($_SESSION['idempresa']);
    $datose = $datos->fetch_object();

	$rspta= $compra->compraReporte1($_GET["idcompra"]);
	$rspta2= $compra->compraReporte1($_GET["idcompra"]);;
	$rspta3= $compra->compraReporte2($_GET["idcompra"]);;



$logo = "../files/logo/".$datose->logo;
$ext_logo = substr($datose->logo, strpos($datose->logo,'.'),-4);

	//Instanciamos la clase para generar el documento pdf
	$pdf=new PDF_MC_Table_Compra();

	//Agregamos la primera pagina al documento pdf
	$pdf->AddPage();

#Establecemos los márgenes izquierda, arriba y derecha: 
$pdf->SetMargins(10, 10 , 10); 
#Establecemos el margen inferior: 
$pdf->SetAutoPageBreak(true,10); 

	//Seteamos el inicio del margen superior en 25 pixeles
	$y_axis_initial=25;


//Seteamos el tipo de letra y creamos el título de la página. No es un encabezado no se repetirá
$pdf->SetFont('courier','B',10);
$pdf->Cell(40,6,'',0,0,'C');
$pdf->Cell(-40,2,utf8_decode($datose->nombre_razon_social),0,0,'C'); 
$pdf->Cell(42,10,'EJERCICIO '.date("Y"),0,0,'C'); 


$pdf->SetFont('courier','B',10);
$pdf->Cell(40,6,'',0,0,'C');
$pdf->Cell(180,2,'RUC: '.$datose->numero_ruc,0,0,'C'); 
$pdf->Ln(10);

$pdf->SetFont('Arial','B',12);
$pdf->Cell(40,6,'',0,0,'C');
$pdf->Cell(100,2,'COMPROBANTE DE COMPRA',0,0,'C'); 
$pdf->Ln(6);


while($reg=$rspta->fetch_object())
{
	$numero=$reg->numero;
	$fecha=$reg->fecha;
	$proveedor=$reg->proveedor;
	$usuario=$reg->usuario;
	$tdocumento=$reg->tdocumento;
	$estado=$reg->estado;
	$mon=$reg->moneda;
	$tc=$reg->tcambio;
}

$pdf->RotatedText($estado, 35,190,'COMPRA ANULADA',45);
$pdf->temporaire( "" );


//Creamos las celdas para los títulos de cada columna y le asignamos un fondo gris y el tipo de letra
$pdf->SetFillColor(232,232,232); 
$pdf->SetFont('courier','',9);
$pdf->Cell(0,6,utf8_decode('Serie/Número: ').$numero."  /  Fecha: ".$fecha."  /  Proveedor: ".$proveedor,0,0,'C',0) ; 
$pdf->Cell(-190,18,utf8_decode('Usuario: ').$usuario."  /  Documento: ".$tdocumento ."  /  Moneda: ".$mon ."  /  T. cambio: ".$tc,0,0,'C',0) ; 
//$pdf->Cell(0,0,utf8_decode('Fecha: ').$fecha,1,0,'C',0) ; 
//$pdf->Cell(115,22,utf8_decode('Proveedor: ').utf8_decode($proveedor),0,0,'C',0) ; 
//$pdf->Cell(50 ,30,utf8_decode('Usuario: ').$usuario,0,0,'C',0) ; 
//$pdf->Cell(50,38,utf8_decode('Documento: ').$tdocumento,0,0,'C',0) ; 

$pdf->Ln(1);

//Titulos
$pdf->SetFont('courier','B',10);
$pdf->Cell(20 ,45,utf8_decode('CÓDIGO.'),0,0,'C',0) ;  
$pdf->Cell(60	,45,utf8_decode('DESCRIPCIÓN'),0,0,'C',0) ;  
$pdf->Cell(60,45,utf8_decode('V.U.'),0,0,'C',0) ;  
$pdf->Cell(-10,45,utf8_decode('CANTIDAD'),0,0,'C',0) ;  
$pdf->Cell(65,45,utf8_decode('SUBTOTAL'),0,0,'C',0) ;  

$pdf->Ln(1);
$pdf->SetFont('courier','B',8);
$pdf->Cell(172,48,utf8_decode('----------------------------------------------------------------------------------------------------------'),0,0,'C',0) ;  

$pdf->Ln(25);

//Implementamos las celdas de la tabla con los registros a mostrar
$pdf->SetWidths(array(30,75,25,25,25));

while($reg2=$rspta2->fetch_object())
{
	$codigoprove=$reg2->codigo;
	$descripcion=$reg2->nombre;
	$vunitario=$reg2->vunitario;
	$cantidad=$reg2->cantidad;
	$stotal=$reg2->stotal;
	$umedida=$reg2->nombreum;
	

	$pdf->SetFont('courier','',8);
	$pdf->Row(array($codigoprove, utf8_decode($descripcion), utf8_decode($vunitario), utf8_decode($cantidad)." ".$umedida , utf8_decode($stotal)));
}



while($reg3=$rspta3->fetch_object())
{
	$sbt=$reg3->sbt;
	$igv_=$reg3->igv_;
	$ttl=$reg3->ttl;
}
//Creamos las celdas para los títulos de cada columna y le asignamos un fondo gris y el tipo de letra
$pdf->SetFillColor(232,240,200); 
$pdf->SetFont('courier','b',10);
$pdf->Cell(300,8,utf8_decode('SUBTOTAL S/: ').$sbt,0,0,'C',0) ; 

$pdf->SetFillColor(232,232,232); 
$pdf->SetFont('courier','b',10);
$pdf->Cell(-292,22,utf8_decode('IGV S/: ').$igv_,0,0,'C',0) ; 

//$pdf->SetFillColor(232,232,232); 
//$pdf->SetFont('courier','b',10);
$pdf->Cell(290,36,utf8_decode('TOTAL S/: ').$ttl,0,0,'C',0) ; 

$pdf->Ln(1);
$pdf->SetFont('courier','B',8);
$pdf->Cell(172,1,utf8_decode('----------------------------------------------------------------------------------------------------------'),0,0,'C',0) ;  
$pdf->Ln(1);
$pdf->SetFont('courier','B',8);
$pdf->Cell(300,40,utf8_decode('------------------------------'),0,0,'C',0) ;  

//mostramos el documento pdf
$pdf->Output();
//}
//else
//{
	//echo 'No tiene permiso de visualizar este documento';
//}

}
ob_end_flush();
?>








