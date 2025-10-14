<?php
if (strlen(session_id()) < 1) 
  session_start();
 
require_once "../modelos/Guiaremision.php";
$gremision=new Guiaremision();
require_once "../modelos/Numeracion.php";
$numeracion = new Numeracion();

//Guia de R.
$idempresa=$_SESSION['idempresa'];
$idguia=isset($_POST["idguia"])? limpiarCadena($_POST["idguia"]):"";
$serie=isset($_POST["SerieReal"])? limpiarCadena($_POST["SerieReal"]):"";
$numero=isset($_POST["numero_guia"])? limpiarCadena($_POST["numero_guia"]):"";
$pllegada=isset($_POST["pllegada"])? limpiarCadena($_POST["pllegada"]):"";
$destinatario=isset($_POST["destinatario"])? limpiarCadena($_POST["destinatario"]):"";
$nruc=isset($_POST["nruc"])? limpiarCadena($_POST["nruc"]):"";
$ppartida=isset($_POST["ppartida"])? limpiarCadena($_POST["ppartida"]):"";
$fechat=isset($_POST["fecha_emision"])? limpiarCadena($_POST["fecha_emision"]):"";
$ncomprobante=isset($_POST["numero_comprobante"])? limpiarCadena($_POST["numero_comprobante"]):"";
$ocompra=isset($_POST["ocompra"])? limpiarCadena($_POST["ocompra"]):"";
$motivo=isset($_POST["motivo"])? limpiarCadena($_POST["motivo"]):"";
$idcomprobante=isset($_POST["idcomprobante"])? limpiarCadena($_POST["idcomprobante"]):"";
$idserie=isset($_POST["serie"])? limpiarCadena($_POST["serie"]):"";

$fechatraslado=isset($_POST["fechatraslado"])? limpiarCadena($_POST["fechatraslado"]):"";
$rsocialtransportista=isset($_POST["rsocialtransportista"])? limpiarCadena($_POST["rsocialtransportista"]):"";
$ructran=isset($_POST["ructran"])? limpiarCadena($_POST["ructran"]):"";
$placa=isset($_POST["placa"])? limpiarCadena($_POST["placa"]):"";
$marca=isset($_POST["marca"])? limpiarCadena($_POST["marca"]):"";
$cinc=isset($_POST["cinc"])? limpiarCadena($_POST["cinc"]):"";
$container=isset($_POST["container"])? limpiarCadena($_POST["container"]):"";
$nlicencia=isset($_POST["nlicencia"])? limpiarCadena($_POST["nlicencia"]):"";
$ncoductor=isset($_POST["ncoductor"])? limpiarCadena($_POST["ncoductor"]):"";

$npedido=isset($_POST["npedido"])? limpiarCadena($_POST["npedido"]):"";
$vendedor=isset($_POST["vendedor"])? limpiarCadena($_POST["vendedor"]):"";
$costmt=isset($_POST["costmt"])? limpiarCadena($_POST["costmt"]):"";
$fechacomprobante=isset($_POST["fechacomprobante"])? limpiarCadena($_POST["fechacomprobante"]):"";


$observaciones=isset($_POST["observaciones"])? limpiarCadena($_POST["observaciones"]):"";
$pesobruto=isset($_POST["pesobruto"])? limpiarCadena($_POST["pesobruto"]):"";
$umedidapbruto=isset($_POST["umedidapbruto"])? limpiarCadena($_POST["umedidapbruto"]):"";
$codtipotras=isset($_POST["codtipotras"])? limpiarCadena($_POST["codtipotras"]):"";

$tipodoctrans=isset($_POST["tipodoctrans"])? limpiarCadena($_POST["tipodoctrans"]):"";
$dniconduc=isset($_POST["dniconduc"])? limpiarCadena($_POST["dniconduc"]):"";

$tipocomprobante=isset($_POST["tipocomprobante"])? limpiarCadena($_POST["tipocomprobante"]):"";
$idpersona=isset($_POST["idpersona"])? limpiarCadena($_POST["idpersona"]):"";

$ubigeopartida=isset($_POST["ubigeopartida"])? limpiarCadena($_POST["ubigeopartida"]):"";
$ubigeollegada=isset($_POST["ubigeollegada"])? limpiarCadena($_POST["ubigeollegada"]):"";

switch ($_GET["op"]){

case 'guardaryeditarGuia':

    if (empty($idguia)){
        $rspta=$gremision->insertar(
            $idguia,
            $serie,
            $numero, 
            $pllegada, 
            $destinatario, 
            $nruc, 
            $ppartida, 
            $fechat, 
            $ncomprobante, 
            $ocompra, 
            $motivo, 
            $idcomprobante, 
            $idserie, 
            $idempresa, 
            $fechatraslado,
            $rsocialtransportista,
            $ructran,
            $placa,
            $marca,
            $cinc,
            $container,
            $nlicencia,
            $ncoductor,
            $npedido,
            $vendedor,
            $costmt,
            $fechacomprobante,
            $observaciones, 
            $pesobruto,
            $umedidapbruto,
            $codtipotras,
            $tipodoctrans,
            $dniconduc,
            $tipocomprobante,
            $idpersona,
            $ubigeopartida,
            $ubigeollegada

        );
                
            echo $rspta ? "Guía registrada" : "No se pudieron registrar todos los datos de la Guía";
        }
        else{
        }

    
    break;

    case 'selectSerie':
    $rspta = $numeracion->llenarSerieGuia();
 
        while ($reg = $rspta->fetch_object())
                {
                    echo '<option value=' . $reg->idnumeracion . '>' . $reg->serie . '</option>';

                }
    break;

    case 'autonumeracion':
    
    $Ser=$_GET['ser'];
    $rspta=$numeracion->llenarNumeroGuia($Ser);
        while ($reg=$rspta->fetch_object())
        {
        echo $reg->Nnumero;    
        }
    break;






    //Listado de comprobantes para el Modal
    case 'listarComprobante':
        $tipp=$_GET['tip'];

        if ($tipp=='01') {
            $rspta=$gremision->buscarComprobante($_SESSION['idempresa']);

                    //Vamos a declarar un array
        $data= Array();
        while ($reg=$rspta->fetch_object()){
            $data[]=array(
                "0"=>'<button class="btn btn-warning" onclick="agregarComprobante('.$reg->idfactura.',\''.$reg->tdcliente.'\',\''.$reg->ndcliente.'\',\''.$reg->rzcliente.'\',\''.$reg->domcliente.'\', \''.$reg->tipocomp.'\',\''.$reg->numerodoc.'\',\''.$reg->subtotal.'\',\''.$reg->igv.'\',\''.$reg->total.'\', \''.$reg->fechafactura.'\', \''.$reg->idpersona.'\')"><span class="fa fa-plus"></span></button>',

                "1"=>$reg->ndcliente,
                "2"=>$reg->rzcliente,
                "3"=>$reg->domcliente,
                "4"=>$reg->numerodoc,
                "5"=>$reg->subtotal,
                "6"=>$reg->igv,
                "7"=>$reg->total
                );
        }

        }else{
            $rspta=$gremision->buscarComprobanteBoleta($_SESSION['idempresa']);

                    //Vamos a declarar un array
        $data= Array();
        while ($reg=$rspta->fetch_object()){
            $data[]=array(
                "0"=>'<button class="btn btn-warning" onclick="agregarComprobante('.$reg->idboleta.',\''.$reg->tdcliente.'\',\''.$reg->ndcliente.'\',\''.$reg->rzcliente.'\',\''.$reg->domcliente.'\', \''.$reg->tipocomp.'\',\''.$reg->numerodoc.'\',\''.$reg->subtotal.'\',\''.$reg->igv.'\',\''.$reg->total.'\', \''.$reg->fechaboleta.'\' , \''.$reg->idpersona.'\')"><span class="fa fa-plus"></span></button>',

                "1"=>$reg->ndcliente,
                "2"=>$reg->rzcliente,
                "3"=>$reg->domcliente,
                "4"=>$reg->numerodoc,
                "5"=>$reg->subtotal,
                "6"=>$reg->igv,
                "7"=>$reg->total
                );
        }
        




        }
        $results = array( 
            "sEcho"=>1, //Información para el datatables
            "iTotalRecords"=>count($data), //enviamos el total registros al datatable
            "iTotalDisplayRecords"=>count($data), //enviamos el total registros a visualizar
            "aaData"=>$data);

        echo json_encode($results);

   break;








case 'detalle':

    $idcomp=$_GET['id'];
    $tipocom2=$_GET['tipo2'];

if ($tipocom2=='01') {

      $rsptaf=$gremision->buscarComprobanteId($idcomp);
        $data= Array();
        $item=1;
        echo '<thead style="background-color:#35770c; color: #fff; align: center; " >
                                    <th >CANTIDAD</th>
                                    <th >CÓDIGO</th>
                                    <th >DESCRIPCIÓN</th>
                                    <th >UNIDAD MEDIDA</th>
                                    

                      </thead>';
        while ($reg = $rsptaf->fetch_object())
                {
                    $sw=in_array($reg->idfactura, $data);
       
echo ' <tr class="filas" id="fila">
    
<td><input type="hidden" class="form-control"  name="cantidad" id="cantidad" value="'.$reg->cantidad.'" disabled="true" style="visible:hidden;">
      <span> '.$reg->cantidad.' </span></td>
<td><input type="hidden" name="codigo" id="codigo"  value="'.$reg->codigo.'" class="form-control" disabled="true">
<span> '.$reg->codigo.' </span></td></td>

<td><input type="hidden" class="form-control"  name="descripcion" id="descripcion" value="'.$reg->descripcion.'" size="20" disabled="true" disabled="true">
<span> '.$reg->descripcion.' </span></td>

<td><input type="hidden" class="form-control"  name="unidad_medida" id="unidad_medida" value="'.$reg->unidad_medida.'" disabled="true" ><span> '.$reg->unidad_medida.' </span></td>

        </tr>';
        $item=$item + 1;
                }



}else{


     $rsptaf=$gremision->buscarComprobanteIdBoleta($idcomp);
        $data= Array();
        $item=1;
        echo '<thead style="background-color:#35770c; color: #fff; align: center; " >
                                    <th >CANTIDAD</th>
                                    <th >CÓDIGO</th>
                                    <th >DESCRIPCIÓN</th>
                                    <th >UNIDAD MEDIDA</th>
                                    

                      </thead>';
        while ($reg = $rsptaf->fetch_object())
                {
                    $sw=in_array($reg->idboleta, $data);
       
echo ' <tr class="filas" id="fila">
    
<td><input type="hidden" class="form-control"  name="cantidad" id="cantidad" value="'.$reg->cantidad.'" disabled="true" style="visible:hidden;">
      <span> '.$reg->cantidad.' </span></td>
<td><input type="hidden" name="codigo" id="codigo"  value="'.$reg->codigo.'" class="form-control" disabled="true">
<span> '.$reg->codigo.' </span></td></td>

<td><input type="hidden" class="form-control"  name="descripcion" id="descripcion" value="'.$reg->descripcion.'" size="20" disabled="true" disabled="true">
<span> '.$reg->descripcion.' </span></td>

<td><input type="hidden" class="form-control"  name="unidad_medida" id="unidad_medida" value="'.$reg->unidad_medida.'" disabled="true" ><span> '.$reg->unidad_medida.' </span></td>

        </tr>';
        $item=$item + 1;
                }


}


      





    break;




case 'listar':
        $rspta=$gremision->listar($_SESSION['idempresa']);
        //Vamos a declarar un array
        $data= Array();
 
        while ($reg=$rspta->fetch_object()){
            
                $url='../reportes/exGuia.php?id=';

            $data[]=array(
                "0"=> 
                    '<a target="_blank" href="'.$url.$reg->idguia.'"><button
                class="btn btn-info btn-sm"><i class="fa fa-print" ></i></button></a>

                <a  onclick="generarxml('.$reg->idguia.')"><button
                class="btn btn-danger btn-sm"><i >XML</i></button></a>

               <a  onclick="enviarxmlS('.$reg->idguia.')"><button
                class="btn btn-success btn-sm"><i >SUNAT</i></button></a>',
                
                "1"=>$reg->fechat,
                "2"=>$reg->snumero,
                "3"=>$reg->destinatario,
                "4"=>$reg->ncomprobante,
                "5"=>($reg->estado=='1') ? '<span class="label bg-orange" data-toggle="tooltip" title="'.$reg->DetalleSunat.'"><i class="fa fa-check"></i></span>': 
                (($reg->estado=='5') ? '<span class="label bg-green"data-toggle="tooltip" title="'.$reg->DetalleSunat.'"><i class="fa fa-send"></i></span>':
                (($reg->estado=='4') ? '<span class="label bg-brown" data-toggle="tooltip" title="'.$reg->DetalleSunat.'">XML</span>':                    
                '<span class="label bg-red">Anulada</span>'))

            );
        }

        $results = array(
            "sEcho"=>1, //Información para el datatables
            "iTotalRecords"=>count($data), //enviamos el total registros al datatable
            "iTotalDisplayRecords"=>count($data), //enviamos el total registros a visualizar
            "aaData"=>$data);

        echo json_encode($results);
 
    break;


    
    case 'generarxml':
        $rspta=$gremision->generarxml($idguia, $_SESSION['idempresa']);
        echo json_encode($rspta) ;
    break;

    case 'enviarxmlS':
        $rspta=$gremision->enviarSUN($idguia, $_SESSION['idempresa']);
        echo $rspta;
    break;




    case 'selectDepartamento':

        
        $rspta = $gremision->selectD();
        while ($reg = $rspta->fetch_object())
                {
                    echo '<option value=' . $reg->idDepa . '>' . $reg->departamento . '</option>';
                }
    break;


    case 'selectprovinc':
    
        $id=$_GET['idd'];
        $rspta = $gremision->selectP($id);
 
        while ($reg = $rspta->fetch_object())
                {
                    echo '<option value=' . $reg->idProv . '>' . $reg->provincia . '</option>';

                }
    break;


     case 'selectDistrito':
        
        $id=$_GET['idc'];
        $rspta = $gremision->selectDI($id);
 
        while ($reg = $rspta->fetch_object())
                {
                    echo '<option value=' . $reg->idDist . '>' . $reg->distrito . '</option>';

                }
    break;

    
}

?>