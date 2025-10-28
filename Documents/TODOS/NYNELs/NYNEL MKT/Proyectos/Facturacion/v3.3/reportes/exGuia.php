<?php
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
if ($_SESSION['idempresa'])
{
//Incluímos el archivo Factura.php
require('Guia.php');
 
require_once "../modelos/Factura.php";
$factura = new Factura();
$rsptav = $factura->ventacabecera($_GET["id"], $_SESSION['idempresa']);
$datos = $factura->datosemp($_SESSION['idempresa']);
$datose = $datos->fetch_object();

$logo = "../files/logo/".$datose->logo;
$ext_logo = substr($datose->logo, strpos($datose->logo,'.'),-4);
 
//Obtenemos los datos de la cabecera de la venta actual
require_once "../modelos/Guiaremision.php";
$guia = new Guiaremision();
$rsptav = $guia->cabecera($_GET["id"]);
//Recorremos todos los valores obtenidos
$regv = $rsptav->fetch_object();
 
//Establecemos la configuración de la factura
$pdf = new PDF_Invoice( 'P', 'mm', 'A4' );
$pdf->AddPage();


$pdf->addSocietenombre(htmlspecialchars_decode(utf8_decode($datose->nombre_comercial)), $datose->textolibre); //Nuevo

//Enviamos los datos de la empresa al método addSociete de la clase Factura
$pdf->addSociete(utf8_decode("Teléfono: ").$datose->telefono1." - ".$datose->telefono2,
    "Email: ".$datose->correo,
    htmlspecialchars_decode(utf8_decode("Dirección: ").$datose->domicilio_fiscal),  $logo, $ext_logo);

//$pdf->addSocietedireccion($datose->domicilio_fiscal);

//Datos de la empresa
$pdf->numGuia("$regv->snumero", $datose->numero_ruc);

$pdf->fechaemision("$regv->fechat");
$pdf->fechatraslado("$regv->fechatraslado");


$pdf->temporaire( "" );
 
// //Enviamos los datos del cliente al método addClientAdresse de la clase Factura
 $pdf->addClientAdresse(
  $regv->pllegada, 
  $regv->destinatario,
  $regv->nruc, 
  $regv->ppartida, 
  $regv->fechat, 
  $regv->ncomprobante, 
  $regv->motivo,
            $regv->fechatraslado,
            $regv->rsocialtransportista,
            $regv->ructran,
            $regv->placa,
            $regv->marca,
            $regv->cinc,
            $regv->container,
            $regv->nlicencia,
            $regv->ncoductor,
            $regv->npedido,
            $regv->vendedor,
            $regv->costmt,
            $regv->fechacomprobante,
            $regv->ocompra,
            $regv->comprobante,
            $regv->observaciones,
            $regv->pesobruto,
            $regv->abre
            
          );
 
 //Establecemos las columnas que va a tener la sección donde mostramos los detalles de la venta
 $cols=array( 
                "IT"=>7,
              "CT"=>10,
              utf8_decode("CODIGO")=>20,
              utf8_decode("DESCRIPCION")=>140,
              "U. MED."=>16
              
             );
 $pdf->addCols($cols);
 $cols=array( "IT"=>"L",
              "CT"=>"L",
              utf8_decode("CODIGO")=>"L",
              "DESCRIPCION"=>"L",
              "U. MED."=>"L"
              );
 $pdf->addLineFormat($cols);
 //$pdf->addLineFormat($cols);

 //Actualizamos el valor de la coordenada "y", que será la ubicación desde donde empezaremos a mostrar los datos
 $y= 97;
 
 //Obtenemos todos los detalles de la venta actual
 $rsptad = $guia->ventadetalle($_GET["id"], $_SESSION['idempresa'], $regv->comprobante);
 
while ($regd = $rsptad->fetch_object()) {
  $line = array( "IT"=> "$regd->norden",
                "CT"=> utf8_decode("$regd->cantidad_item_12"),
                utf8_decode("CODIGO")=> "$regd->codigo",
                utf8_decode("DESCRIPCION")=>  utf8_decode(htmlspecialchars_decode("$regd->articulo"." - "."$regd->descdet")),
                "U. MED." => "$regd->unidad_medida");
            $size = $pdf->addLine( $y, $line );
            $y   += $size + 1;
}

$pdf->AutoPrint();

$pdf->Output('Reporte de Venta','I');


}
else
{
  echo 'No tiene permiso para visualizar el reporte';
}
 
}
ob_end_flush();
?>