<?php 
if (strlen(session_id()) < 1) 
  session_start();
 
require_once "../modelos/Guiaremision.php";
$gremision=new Guiaremision();
require_once "../modelos/Numeracion.php";
$numeracion = new Numeracion();

//Guia de R.

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

switch ($_GET["op"]){

case 'guardaryeditarGuia':

    if (empty($idguia)){
        $rspta=$gremision->insertar($idguia,$serie,$numero, $pllegada, $destinatario, $nruc, $ppartida, $fechat, $ncomprobante, $ocompra, $motivo, $idcomprobante, $idserie);
                
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

        $rspta=$gremision->buscarComprobante();
        //Vamos a declarar un array
        $data= Array();
 
        while ($reg=$rspta->fetch_object()){
            $data[]=array(
                "0"=>'<button class="btn btn-warning" onclick="agregarComprobante('.$reg->idfactura.',\''.$reg->tdcliente.'\',\''.$reg->ndcliente.'\',\''.$reg->rzcliente.'\',\''.$reg->domcliente.'\', \''.$reg->tipocomp.'\',\''.$reg->numerodoc.'\',\''.$reg->subtotal.'\',\''.$reg->igv.'\',\''.$reg->total.'\')"><span class="fa fa-plus"></span></button>',

                "1"=>$reg->ndcliente,
                "2"=>$reg->rzcliente,
                "3"=>$reg->domcliente,
                "4"=>$reg->numerodoc,
                "5"=>$reg->subtotal,
                "6"=>$reg->igv,
                "7"=>$reg->total
                );
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

        $rsptaf=$gremision->buscarComprobanteId($idcomp);
        
        $data= Array();
        $item=1;
        echo '<thead style="background-color:#35770c; color: #fff;">
                                    <th >CANT.</th>
                                    <th >CÓDIGO</th>
                                    <th >DESCRIPCIÓN</th>
                                    <th >U. MED.</th>
                                    <th >PESO TOTAL</th>

                      </thead>';
        while ($reg = $rsptaf->fetch_object())
                {
                    $sw=in_array($reg->idfactura, $data);
       
echo ' <tr class="filas" id="fila">
    
<td><input type="text" class="form-control"  name="cantidad" id="cantidad" value="'.$reg->cantidad.'" disabled="true" ></td>
<td><input type="text" name="codigo" id="codigo"  value="'.$reg->codigo.'" class="form-control" disabled="true" ></td>
<td><input type="text" class="form-control"  name="descripcion" id="descripcion" value="'.$reg->descripcion.'" size="20" disabled="true" disabled="true"></td>

<td><input type="text" class="form-control"  name="unidad_medida" id="unidad_medida" value="'.$reg->unidad_medida.'" disabled="true" ></td>
<td><input type="text" class="form-control"  name="peso" id="peso" value="" disabled="true" ></td>
        </tr>';
        $item=$item + 1;
                }
    break;




case 'listar':
        $rspta=$gremision->listar();
        //Vamos a declarar un array
        $data= Array();
 
        while ($reg=$rspta->fetch_object()){
            
                $url='../reportes/exGuia.php?id=';

            $data[]=array(
                "0"=>(($reg->estado=='1')?' <button class="btn btn-danger" onclick="anular('.$reg->idguia.')"><i class="fa fa-close"></i></button>'.
                    '<a target="_blank" href="'.$url.$reg->idguia.'"><button
                class="btn btn-info"><i class="fa fa-file"></i></button></a>':''),
                    //'<button class="btn btn-warning" onclick="mostrar('.$reg->idfactura.')"><i class="fa fa-eye"></i></button>').
                
                "1"=>$reg->fechat,
                "2"=>$reg->snumero,
                "3"=>$reg->destinatario,
                "4"=>$reg->ncomprobante,
                "5"=>($reg->estado=='1')?'<span class="label bg-green">Emitida</span>':
                '<span class="label bg-red">Anulada</span>'
                );
        }

        $results = array(
            "sEcho"=>1, //Información para el datatables
            "iTotalRecords"=>count($data), //enviamos el total registros al datatable
            "iTotalDisplayRecords"=>count($data), //enviamos el total registros a visualizar
            "aaData"=>$data);

        echo json_encode($results);
 
    break;

    
}

?>