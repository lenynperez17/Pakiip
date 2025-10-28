<?php
//recibimos los parametros
$ano=$_POST['ano'];
$fechas=$_POST['fechas'];
$codigoInterno=$_POST['codigoInterno'];

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
if ($_SESSION['almacen']==1)
{
 
//Comenzamos a crear las filas de los registros según la consulta mysql
require_once "../modelos/Articulo.php";
$articulo = new Articulo();
//$rspta = $articulo->kardexArticulo($ano,$fechas,$codigoInterno);
$rspta2 = $articulo->kardexArticulo($ano,$fechas,$codigoInterno, $_SESSION['idempresa']);

$saldoini=0;

$saldofin=0;
$saldofinal_v=0;
$saldofin2=0; 
$saldofin3=0; 
$valorfin_p=0;
$valorfin4=0;
$saldofin4=0; 
$saldofin5=0;
$saldofinal_nc=0;
$costo2=0;
$idarticulo='';

$costoi=0;
$valori=0;
$saldofinalglobal=0;
$sw=0;

while($reg= $rspta2->fetch_object())
{  

    $fecha = $reg->fecha;
    $tipodoc=$reg->descripcion;
    $docum=$reg->numero_doc;
    $transac=$reg->transaccion;
    $tcambio=$reg->tcambio;
    $cantidad=$reg->cantidad;
    $idarticulo=$reg->idarticulo;

    $costoi=$reg->costo_compra;
    
     if ($reg->moneda=='USD') {
            $costo1=($reg->costo_1 * 1.18) * $tcambio ;
             }else{
            $costo1=$reg->costo_1;
            }
    
    $unimed=$reg->unidad_medida;

    $saldoini=$reg->saldo_iniu;
    $valorfinal=$reg->valor_iniu;
    $saldofinal=$reg->saldo_finu;
    $totalventa=$reg->ventast;

    $valori=$reg->valor_iniu;

if ($transac=="COMPRA") {
    //Calculo para saldo final======================================
    $saldofin = $saldoini + $reg->cantidad  + $saldofin2 + $saldofin5 - $saldofin4;
    //$saldofin = $saldofinal + $reg->cantidad  + $saldofin2 + $saldofin5 - $saldofin4;
    //Calculo para saldo final======================================

    //Calculo para costo 2======================================
    //VALFIN+(COSTO*CANTI))/(SALINI+CANTI)
    if ($sw==0){
    $costo2 = round(($valorfinal + ($costo1 * $cantidad)) / ($saldoini + $cantidad + $saldofin3),2);
    }
    else
    {
    $costo2 = round(($vf + ($costo1 * $cantidad)) / ($saldoini + $cantidad + $saldofin3),2);
    }
    //Calculo para costo 2======================================

    //Calculo para Valor final======================================
    $valorfin_p=$saldofin * $costo2 ;
    //Calculo para Valor final======================================

    $saldofin2 = $saldofin2 + $reg->cantidad;
    $vf = $valorfin_p;
    $saldofin3 = $saldofin3 + $reg->cantidad;

    $sw=$sw + 1;

    $saldofinalglobal=$saldofin;

//==================================================================================
        
   }else if ($transac=="VENTA") {

    if ($saldofinalglobal==0) { $saldofinalglobal=$reg->saldo_iniu; }
    
    //Calculo para saldo final======================================
    $saldofinal_v = $saldofinalglobal  - $reg->cantidad;// - $saldofin4 ;
    //$saldofin4 =  $saldofin4 + $reg->cantidad;
    //Calculo para saldo final======================================

    //Calculo para costo 2======================================
    if ($costo2==0){$costo2=$reg->costo_compra;}else{
    $costo2=$costo2;
    }
    //Calculo para costo 2======================================

    //Calculo para Valor final======================================
    $valorfin_p = $saldofinal_v * $costo2 ;
    //Calculo para Valor final======================================

    $vf= $valorfin_p;
    $saldofin4= $saldofin4 + $reg->cantidad;
    
      $sw=$sw + 1;

    $saldofinalglobal= $saldofinal_v ;


}else if ($transac=="O.SERVI.") {
    if ($saldofinalglobal==0) { $saldofinalglobal=$reg->saldo_iniu; }
    //Calculo para saldo final======================================
    $saldofinal_v = $saldofinalglobal - $reg->cantidad;// - $saldofin4 ;
    //Calculo para costo 2======================================
    if ($costo2==0){$costo2=$reg->costo_compra;}else{
    $costo2=$costo2;
    }
    //Calculo para costo 2======================================
    //Calculo para Valor final======================================
    $valorfin_p = $saldofinal_v * $costo2 ;
    //Calculo para Valor final======================================
    $vf= $valorfin_p;
    $saldofin4= $saldofin4 + $reg->cantidad;
    
    $sw=$sw + 1;
    $saldofinalglobal= $saldofinal_v ;

    
//====================================================================================
    }else if ($transac=="NOTAC" || $transac=="ANULADO" ) {

    //$tipodoc="NOTAC";
    //Calculo para saldo final======================================
    $saldofinal_nc = $saldofinalglobal + $reg->cantidad;// + $saldofin5 ;
    //$saldofin5 =  $saldofin5 + $reg->cantidad;
    //Calculo para saldo final======================================

    //Calculo para costo 2======================================
    $costo2 = $costo2;
    //Calculo para costo 2======================================

    //Calculo para Valor final======================================
    $valorfin_p = $saldofinal_nc * $costo2 ;
    //Calculo para Valor final======================================

    $saldofin5 = $saldofin5 + $reg->cantidad;
    
    $saldofinalglobal= $saldofinal_nc;
    }else if ($transac=="COMPRA ANULADA"){

if ($saldofinalglobal==0) { $saldofinalglobal=$reg->saldo_iniu; }
    
    //Calculo para saldo final======================================
    $saldofinal_v = $saldofinalglobal  - $reg->cantidad;// - $saldofin4 ;
    //$saldofin4 =  $saldofin4 + $reg->cantidad;
    //Calculo para saldo final======================================

    //Calculo para costo 2======================================
    if ($costo2==0){$costo2=$reg->costo_compra;}else{
    $costo2=$costo2;
    }
    //Calculo para costo 2======================================

    //Calculo para Valor final======================================
    $valorfin_p = $saldofinal_v * $costo2 ;
    //Calculo para Valor final======================================

    $vf= $valorfin_p;
    $saldofin4= $saldofin4 + $reg->cantidad;
    
      $sw=$sw + 1;

    $saldofinalglobal= $saldofinal_v ;
    }


} // Fin while

$rspta3 = $articulo->valoresiniciales($codigoInterno);
    while($valini= $rspta3->fetch_object())
{  
    $idarticulo=$valini->idarticulo;
}

$articulo->insertarkardexArticulo($_SESSION['idempresa'], $idarticulo, $codigoInterno , $ano, $costoi, $saldoini, $valori, $costo2, $saldofinalglobal, $valorfin_p );

}
else
{
  echo 'No tiene permiso para visualizar el reporte';
}
 
}
ob_end_flush();
?>