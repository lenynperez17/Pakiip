<?php
//recibimos los parametros
$ano=$_POST['ano'];
$mes=$_POST['mes'];
$idarticulo=$_POST['codigoInterno'];

$opcion=$_POST['opcion1'];

   
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
//if ($_SESSION['almacen']==1)
//{
 
//Inlcuímos a la clase PDF_MC_Table
require('MargenGanancia.php');

//Comenzamos a crear las filas de los registros según la consulta mysql
require_once "../modelos/Articulo.php";
$articulo = new Articulo();

$codigo="";
$nombre="";
$ventapromedio="";
$costopromedio="";
$ganancia="";
$mgporce="";
$signo="";



if ($opcion=='xcodigo')

{
//PARA VALORES UNITARIOS
$rspta = $articulo->obtenerdatosmargengindividual($ano, $mes);
//Instanciamos la clase para generar el documento pdf
$pdf=new PDF_MC_Table();
//Agregamos la primera página al documento pdf
$pdf->AddPage();
$pdf->SetWidths(array(25,45, 35, 30, 22, 15));
while($reg= $rspta->fetch_object())
{  
    $codigo=$reg->codigo;
    $nombre=$reg->nombre;
    $ventapromedio=$reg->totalventas;
    $costopromedio=$reg->totalcompras;
    $ganancia=$reg->ganancia;
    $mgporce=$reg->porcentaje;
}

$pdf->SetFont('arial','',8);

if ($mgporce > 0) {
    $signo="↑";
}
$pdf->Row(array($codigo, $nombre, $ventapromedio , $costopromedio ,$ganancia, $mgporce." ".$signo));
//$pdf->row(array($sumacostoventa));



}else{





$rspta = $articulo->obtenerdatosmargeng($ano, $mes);

//Instanciamos la clase para generar el documento pdf
$pdf=new PDF_MC_Table();
//Agregamos la primera página al documento pdf
$pdf->AddPage();
$pdf->SetWidths(array(25,45, 35, 30, 22, 15));

$pdf->SetFont('arial','',8);
while($reg= $rspta->fetch_object())
{  
    $codigo=$reg->codigo;
    $nombre=$reg->nombre;
    $ventapromedio=$reg->totalventas;
    $costopromedio=$reg->totalcompras;
    $ganancia=$reg->ganancia;
    $mgporce=$reg->porcentaje;

    if ($mgporce > 0) {
    //$pdf->SetTextColor(194,8,8);
    $signo=htmlspecialchars_decode("+");
    }
    elseif ($mgporce < 0   )
    {
        $signo=htmlspecialchars_decode("-");
    }
    else
    {
        $signo=htmlspecialchars_decode("<>");    
    }
    

    
    $pdf->Row(array($codigo, $nombre, $ventapromedio , $costopromedio ,$ganancia, $mgporce." ".$signo));
}



//$pdf->Row(array($codigo, $nombre, $ventapromedio , $costopromedio ,$ganancia, $mgporce));


}




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