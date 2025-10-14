<?php 
if (strlen(session_id()) < 1) 
  session_start();
 
require_once "../modelos/Subirarchivos.php";

$subirarchivos=new Subirarchivos();


$ano=isset($_POST["ano"])? limpiarCadena($_POST["ano"]):"";




switch ($_GET["op"]){
    //Registro de compras 
    case 'regcompras':

        $ano=$_GET['ano'];
        $mes=$_GET['mes'];
        $moneda=$_GET['moneda'];

        $rspta=$compra->regcompra($ano, $mes, $moneda);
        
        //Vamos a declarar un array
        $data= Array();
 
        while ($reg=$rspta->fetch_object()){
            
            $data[]=array(
                
                "0"=>$reg->fecha,
                "1"=>$reg->tipo_documento,
                "2"=>$reg->serie,
                "3"=>$reg->numero,
                "4"=>$reg->numero_documento,
                "5"=>$reg->razon_social,
                "6"=>$reg->subtotal,
                "7"=>$reg->igv,
                "8"=>$reg->total,
                );
        }

        $results = array(
            "sEcho"=>1, //Información para el datatables
            "iTotalRecords"=>count($data), //enviamos el total registros al datatable
            "iTotalDisplayRecords"=>count($data), //enviamos el total registros a visualizar
            "aaData"=>$data);

        echo json_encode($results);
    break;



    //Registro de ventas de facturas y boletas
    case 'regventas':

       


 case 'resetearvalores':

        
    break;


    case 'descargarcomprobante':
   
    break;


   }
?>