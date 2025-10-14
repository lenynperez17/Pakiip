<?php
if (strlen(session_id()) < 1)
    session_start();

require_once "../modelos/Venta.php";
require_once "../modelos/Factura.php";
$venta = new Venta();
$factura = new Factura();

$idcomprobante = isset($_POST["idcomprobante"]) ? limpiarCadena($_POST["idcomprobante"]) : "";
$tipo_documento_07 = isset($_POST["tipo_documento_07"]) ? limpiarCadena($_POST["tipo_documento_07"]) : "";

$idnotificacion = isset($_POST["idnotificacion"]) ? limpiarCadena($_POST["idnotificacion"]) : "";
$codigonotificacion = isset($_POST["codigonotificacion"]) ? limpiarCadena($_POST["codigonotificacion"]) : "";
$nombrenotificacion = isset($_POST["nombrenotificacion"]) ? limpiarCadena($_POST["nombrenotificacion"]) : "";
$fechacreacion = isset($_POST["fechacreacion"]) ? limpiarCadena($_POST["fechacreacion"]) : "";
$fechaaviso = isset($_POST["fechaaviso"]) ? limpiarCadena($_POST["fechaaviso"]) : "";
$continuo = isset($_POST["selconti"]) ? limpiarCadena($_POST["selconti"]) : "";
$estado = isset($_POST["selestado"]) ? limpiarCadena($_POST["selestado"]) : "";
$tipocomprobante = isset($_POST["tipo_documento_noti"]) ? limpiarCadena($_POST["tipo_documento_noti"]) : "";
$idcliente = isset($_POST["idclientenoti"]) ? limpiarCadena($_POST["idclientenoti"]) : "";


switch ($_GET["op"]) {

    case 'guardaryeditarnotificacion':
        if (empty($idnotificacion)) {
            $rspta = $venta->insertarnotificacion($codigonotificacion, $nombrenotificacion, $fechacreacion, $fechaaviso, $continuo, $tipocomprobante, $idcliente, $estado);
            echo $rspta ? "Registro correcto" : "No se pudo registrar";
        }
        break;


    case 'editarnotificacion':
        $rspta = $venta->editarnotificacion();
        echo $rspta ? "Fechas cambiadas" : "No se pudo actualizar";

        break;



    case 'ventasVendedorTotalxMesUni':
        $vendedor = $_GET['vendedor'];
        $idempresa = $_GET['idempresa'];
        $ano = $_GET['ano'];
        $mes = $_GET['mes'];
        $rsptaf = $venta->ventasVendedorTotalxMes($vendedor, $ano, $mes, $idempresa);
        $data = array();
        while ($regf = $rsptaf->fetch_object()) {
            $data[] = array(
                'Vendedor' => $regf->Vendedor,
                'TotalFactura' => $regf->totalFactura,
                'TotalBoleta' => $regf->totalBoleta,
                'TotalNota' => $regf->totalNota
            );
        }
        header('Content-Type: application/json');
        echo json_encode($data);
        break;



    case 'ventaVendedorFactura':
        $vendedor = $_GET['vendedor'];
        $idempresa = $_GET['idempresa'];
        $ano = $_GET['ano'];
        $mes = $_GET['mes'];
        $rsptaf = $venta->ventasVendedorFactura($vendedor, $ano, $mes, $idempresa);
        while ($regf = $rsptaf->fetch_object()) {
            $dataFactura = $regf->totalFactura;
            echo $dataFactura;
        }
        break;

    case 'ventaVendedorFacturadia':
        $vendedor = $_GET['vendedor'];
        $idempresa = $_GET['idempresa'];
        $ano = $_GET['ano'];
        $mes = $_GET['mes'];
        $dia = $_GET['dia'];
        $rsptaf = $venta->ventasVendedorFacturaxdia($vendedor, $ano, $mes, $dia, $idempresa);
        while ($regf = $rsptaf->fetch_object()) {
            $dataFactura = $regf->totalFactura;
            echo $dataFactura;
        }
        break;

    case 'ventaVendedorBoleta':
        $vendedor = $_GET['vendedor'];
        $ano = $_GET['ano'];
        $mes = $_GET['mes'];
        $idempresa = $_GET['idempresa'];
        $rsptab = $venta->ventasVendedorBoleta($vendedor, $ano, $mes, $idempresa);
        while ($regb = $rsptab->fetch_object()) {
            $dataBoleta = $regb->totalBoleta;
            echo $dataBoleta;
        }
        break;

    case 'ventaVendedorBoletadia':
        $vendedor = $_GET['vendedor'];
        $ano = $_GET['ano'];
        $mes = $_GET['mes'];
        $dia = $_GET['dia'];
        $idempresa = $_GET['idempresa'];
        $rsptab = $venta->ventasVendedorBoletaxdia($vendedor, $ano, $mes, $dia, $idempresa);
        while ($regb = $rsptab->fetch_object()) {
            $dataBoleta = $regb->totalBoleta;
            echo $dataBoleta;
        }
        break;

    case 'ventaVendedorBoletaAno':
        $vendedor = $_GET['vendedor'];
        $ano = $_GET['ano'];
        $idempresa = $_GET['idempresa'];
        $rsptab = $venta->ventasVendedorBoletaAno($vendedor, $ano, $idempresa);
        while ($regb = $rsptab->fetch_object()) {
            $dataBoleta = $regb->totalBoleta;
            echo $dataBoleta;
        }
        break;

    case 'ventaVendedorFacturaAno':
        $vendedor = $_GET['vendedor'];
        $ano = $_GET['ano'];
        $idempresa = $_GET['idempresa'];

        $rsptaf = $venta->ventasVendedorFacturaAno($vendedor, $ano, $idempresa);
        while ($regf = $rsptaf->fetch_object()) {
            $dataFactura = $regf->totalFactura;
            echo $dataFactura;
        }
        break;

    //codigo aoa
    //===================
    case 'ventaVendedorNota':
        $vendedor = $_GET['vendedor'];
        $ano = $_GET['ano'];
        $mes = $_GET['mes'];
        $idempresa = $_GET['idempresa'];
        $rsptab = $venta->ventasVendedorNota($vendedor, $ano, $mes, $idempresa);
        while ($regb = $rsptab->fetch_object()) {
            $dataNota = $regb->totalNota;
            echo $dataNota;
        }
        break;

    case 'ventaVendedorNotaxdia':
        $vendedor = $_GET['vendedor'];
        $ano = $_GET['ano'];
        $mes = $_GET['mes'];
        $dia = $_GET['dia'];
        $idempresa = $_GET['idempresa'];
        $rsptab = $venta->ventasVendedorNotaxdia($vendedor, $ano, $mes, $dia, $idempresa);
        while ($regb = $rsptab->fetch_object()) {
            $dataNota = $regb->totalNota;
            echo $dataNota;
        }
        break;


    case 'compraVendedor':
        $vendedor = $_GET['vendedor'];
        $ano = $_GET['ano'];
        $mes = $_GET['mes'];
        $idempresa = $_GET['idempresa'];
        $rsptab = $venta->comprasVendedor($vendedor, $ano, $mes, $idempresa);
        while ($regb = $rsptab->fetch_object()) {
            $dataCompra = $regb->totalCompra;
            echo $dataCompra;
        }
        break;

    case 'gastoVendedor':
        $vendedor = $_GET['vendedor'];
        $ano = $_GET['ano'];
        $mes = $_GET['mes'];
        $idempresa = $_GET['idempresa'];
        $rsptab = $venta->gastosVendedor($vendedor, $ano, $mes, $idempresa);
        while ($regb = $rsptab->fetch_object()) {
            $dataGasto = $regb->totalGasto;
            echo $dataGasto;
        }
        break;

    case 'planillaVendedor':
        $vendedor = $_GET['vendedor'];
        $ano = $_GET['ano'];
        $mes = $_GET['mes'];
        $idempresa = $_GET['idempresa'];
        $rsptab = $venta->planillasVendedor($vendedor, $ano, $mes, $idempresa);
        while ($regb = $rsptab->fetch_object()) {
            $dataPlanilla = $regb->totalPlanilla;
            echo $dataPlanilla;
        }
        break;
    //==========================


    case 'listar':
        // $idempresa=$_GET['idempresa'];
        $rspta = $venta->listarEnvioCorreo();
        //Vamos a declarar un array
        $data = array();

        while ($reg = $rspta->fetch_object()) {
            $data[] = array(
                "0" => $reg->numero_documento,
                "1" => $reg->cliente,
                "2" => $reg->correo,
                "3" => $reg->comprobante,
                "4" => $reg->fecha_envio
            );
        }
        $results = array(
            "sEcho" => 1,
            //Información para el datatables
            "iTotalRecords" => count($data),
            //enviamos el total registros al datatable
            "iTotalDisplayRecords" => count($data),
            //enviamos el total registros a visualizar
            "aaData" => $data
        );
        echo json_encode($results);

        break;


    case 'impuestoglobal':
        require_once "../modelos/Consultas.php";
        $consulta = new Consultas();
        $rspta = $consulta->impuestoglobal();
        echo json_encode($rspta);

        break;



    case 'listarValidarComprobantes':

        $st = $_GET['estadoFinal'];
        require_once "../modelos/Rutas.php";
        $rutas = new Rutas();
        $Rrutas = $rutas->mostrar2($_SESSION['idempresa']);
        $Prutas = $Rrutas->fetch_object();
        $rutafirma = $Prutas->rutafirma; // ruta de la carpeta FIRMA
        $rutaenvio = $Prutas->rutaenvio; // ruta de la carpeta ENVIO
        $rutarpta = $Prutas->rutarpta; // ruta de la carpeta RESPUESTA

        //Agregar=====================================================
        // Ruta del directorio donde están los archivos

        $path = $rutaenvio;
        $pathFirma = $rutafirma;
        $pathRpta = $rutarpta;
        // Arreglo con todos los nombres de los archivos
        $files = array_diff(scandir($path), array('.', '..'));
        $filesFirma = array_diff(scandir($pathFirma), array('.', '..'));
        $files2 = array_diff(scandir($pathRpta), array('.', '..'));
        //=============================================================

        $rspta = $venta->listarValidarComprobantes($st);
        //Vamos a declarar un array
        $data = array();

        while ($reg = $rspta->fetch_object()) {
            if ($reg->tipo_documento_07 == '01') {
                $url = '../reportes/exFactura.php?id=';
            } else {
                $url = '../reportes/exBoleta.php?id=';
            }

            //==============Agregar====================================================
            $archivo = $reg->numero_ruc . "-" . $reg->tipo_documento_07 . "-" . $reg->numeracion_08;
            $archivo2 = "R" . $reg->numero_ruc . "-" . $reg->tipo_documento_07 . "-" . $reg->numeracion_08;
            $fileBaja = $reg->numero_ruc . "-RA" . $reg->fechabaja . "-011";

            // if ($reg->tipo_documento_07=='01') { // para facturas


            //     require_once "../modelos/Factura.php";
//     $factura=new Factura();
//     if ($reg->estado=='3'){
//         $st="3";
//         $UpSt=$factura->ActualizarEstado($reg->idcomprobante, $st);
//         }
//         elseif ($reg->estado=='0'){
//         $st="0";
//         $UpSt=$factura->ActualizarEstado($reg->idcomprobante, $st);
//         }else{       
//         //Validar si existe el archivo firmado
//         foreach($files as $file){
//         // Divides en dos el nombre de tu archivo utilizando el . 
//         $dataSt = explode(".", $file);
//         // Nombre del archivo
//         $fileName = $dataSt[0];
//         $st="1";
//         // Extensión del archivo 
//         $fileExtension = $dataSt[1];
//         if($archivo == $fileName){
//             $st="4";
//             $UpSt=$factura->ActualizarEstado($reg->idcomprobante, $st);
//           }
//          }
//             //Validar si existe el archivo aceptado por sunat
//             foreach($files2 as $file2){
//             // Divides en dos el nombre de tu archivo utilizando el . 
//             $dataSt2 = explode(".", $file2);
//             // Nombre del archivo
//             $fileName = $dataSt2[0];
//             // Extensión del archivo 
//             $fileExtension = $dataSt2[1];
//             if($archivo2 == $fileName){
//                 $st="5";
//                 $UpSt=$factura->ActualizarEstado($reg->idcomprobante, $st);
//             }
//             }
//         }//Fin if 

            //         }else{  // SI ES PARA BOLETAS //////////////////////////////

            //             require_once "../modelos/Boleta.php";
//             $boleta=new Boleta();

            // if ($reg->estado=='3') {
//         $st="3";
//         $UpSt=$boleta->ActualizarEstado($reg->idcomprobante, $st);
//     }
//     else
//     {

            //     //Validar si existe el archivo firmado
//     foreach($files as $file){
//     // Divides en dos el nombre de tu archivo utilizando el . 
//     $dataSt = explode(".", $file);
//     // Nombre del archivo
//     $fileName = $dataSt[0];
//     $st="1";
//     // Extensión del archivo 
//     $fileExtension = $dataSt[1];
//     if($archivo == $fileName){
//         $st="4";
//         $UpSt=$boleta->ActualizarEstado($reg->idcomprobante, $st);
//       }
//     }

            //     //Validar si existe el archivo aceptado por sunat
//     foreach($files2 as $file2){
//     // Divides en dos el nombre de tu archivo utilizando el . 
//     $dataSt2 = explode(".", $file2);
//     // Nombre del archivo
//     $fileName = $dataSt2[0];
//     // Extensión del archivo 
//     $fileExtension = $dataSt2[1];
//     if($archivo2 == $fileName){
//         $st="5";
//         $UpSt=$boleta->ActualizarEstado($reg->idcomprobante, $st);
//     }
// }
// }//Fin if 
// }

            $st2 = '';
            $sunatFirma = '';
            $sunatAceptado = 'Class';


            if ($reg->diast > 7) {
                $stt = 'disabled';
            } else {
                $stt = '';
            }

            if ($reg->diast >= 7 && $reg->estado == '1') {
                $st2 = 'Fuera de fecha';
            } else {
                $st2 = '';
            }

            if ($reg->estado == '5') {
                $send = '';
                $stt = '';
                $sunatFirma = 'class';
                $sunatAceptado = 'class';
            } else if ($reg->estado == '4') {
                $send = '';
                $stt = '';
                $sunatFirma = 'class';
                $sunatAceptado = '';
            } else {
                $send = 'none';
            }


            if ($reg->estado == '3') {
                $stt = 'disabled';
                $sunat = '';
            }

            if ($reg->estado == '0') {
                $stt = 'disabled';
                $sunat = '';
            }

            $estadoenvio = '1';


            $data[] = array(
                "0" => ' <div class="dropup">
                <button  class="btn btn-primary btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                 Opciones
                <span class="caret"></span></button>
                <ul class="dropdown-menu pull-center">

                 <li>
                  <a  onclick="bajaComprobante(' . $reg->idcomprobante . ' , ' . $reg->tipo_documento_07 . ')" data-toggle="tooltip" title="Anular y dar de baja" ' . $stt . '">
                  <i  class="fa fa-level-down"></i>
                  Anular boleta
                  </a>
                  </li>

                   <li>
                  <a target="_blank" href="' . $url . $reg->idcomprobante . '" data-toggle="tooltip" title="Imprimir factura"> 
                  <i class="fa fa-print"> </i>
                  Imprimir 2 copias
                   </a>
                   </li>

                   </ul>
                </div>'



                //     <a style="color:red; font-size:20px;">
                //     <i class="fa fa-level-down" onclick="bajaComprobante('.$reg->idcomprobante.' , '.  $reg->tipo_documento_07.')" data-toggle="tooltip" title="Anular y dar de baja" '.$stt.' ></i>
                //     </a>
                //    '.
                // '<a target="_blank" href="'.$url.$reg->idcomprobante.'" data-toggle="tooltip" title="Imprimir Factura" style="color:green; font-size:20px;">
                // <i class="fa fa-print"></i>
                // </a>'
                // '<a onclick="enviarcorreo('.$reg->idcomprobante.','.$_SESSION['idempresa'].')" '.$send.'>
                // <i class="fa fa-send" data-toggle="tooltip" title="Enviar por correo a: '.$reg->email.'" ></i>
                // </a>'
                ,
                "1" => $reg->fecha,
                "2" => $reg->cliente,
                "3" => $reg->vendedorsitio,
                "4" => $reg->numeracion_08,
                "5" => $reg->importe_total_venta_27,

                "6" => ($reg->estado == '1')
                ? '<i class="fa fa-file-text-o" style="font-size: 14px; color:#BA4A00;"><span>' . $reg->DetalleSunat . '</span><i class="fa fa-spinner fa-spin fa-1x fa-fw"></i><span class="sr-only">Loading</span></i>'
                : (($reg->estado == '4') ? '<i class="fa fa-thumbs-up" style="font-size: 14px; color:#239B56;"><span>' . $reg->DetalleSunat . '</span></i>'
                    : (($reg->estado == '3') ? '<i class="fa fa-dot-circle-o" style="font-size: 14px; color:#E59866;"> <span>' . $reg->DetalleSunat . '</span></i>'
                        : (($reg->estado == '0') ? '<i class="fa fa-dot-circle-o" style="font-size: 14px; color:#E59866;"><span>' . $reg->DetalleSunat . '</span></i>'
                            : (($reg->estado == '5') ? '<i class="fa fa-globe" style="font-size: 14px; color:#145A32;"><span>' . $reg->DetalleSunat . '</span></i>'
                                : '<i class="fa fa-newspaper" style="font-size: 14px; color:#239B56;"><span>' . $reg->DetalleSunat . '</span></i>')))) //,
                // "7"=>
                //     '<a><i class="fa fa-download" style="color:#2acc70; font-size:18px;"  data-toggle="tooltip" title="Descargar JSON" onclick="downFtp('.$reg->idcomprobante.')"></i></a>
                //     <a onclick="generarxml('.$reg->idcomprobante.','.  $reg->tipo_documento_07.')" '.$sunatFirma.'="class_a_href"><i class="fa fa-download"  style="color:orange; font-size:14px;" data-toggle="tooltip" title="Generar xml"></i></a>
                //     <a onclick="enviarxmlSUNAT('.$reg->idcomprobante.','.  $reg->tipo_documento_07.')"  '.$sunatAceptado.'="class_a_href" ><i class="fa fa-send"  style="color:red; font-size:14px;" data-toggle="tooltip" title="Enviar a SUNAT" ></i></a>
                //     <a onclick="mostrarxml('.$reg->idcomprobante.','.  $reg->tipo_documento_07.')"><i class="fa fa-check" style="color:orange; font-size:14px;"  data-toggle="tooltip" title="Mostrar XML"></i></a>
                //     <a onclick="mostrarrpta('.$reg->idcomprobante.','. $reg->tipo_documento_07.')"><i class="fa fa-check" style="color:green; font-size:14px;"  data-toggle="tooltip" title="Mostrar respuesta CDR"></i></a>
                //     <a href="https://bit.ly/2JDIPCm" target=_blank><img src="../public/images/sunat.png" style="color:green; font-size:18px;"  data-toggle="tooltip" title="Consulta de validez con SUNAT"></i></a>
                //     <a onclick="generarxml('.$reg->idcomprobante.','.  $reg->tipo_documento_07.')"><i class="fa fa-retweet"  style="color:orange; font-size:14px;" data-toggle="tooltip" title="Regenerar xml"></i></a>'
            );
        }
        $results = array(
            "sEcho" => 1,
            //Información para el datatables
            "iTotalRecords" => count($data),
            //enviamos el total registros al datatable
            "iTotalDisplayRecords" => count($data),
            //enviamos el total registros a visualizar
            "aaData" => $data
        );
        echo json_encode($results);
        break;



    case 'generarxml':

        $rspta = $venta->generarxml($idcomprobante, $tipo_documento_07, $_SESSION['idempresa']);
        echo json_encode($rspta);
        break;

    case 'enviarxmlSUNAT':

        $rspta = $venta->enviarxmlSUNAT($idcomprobante, $tipo_documento_07, $_SESSION['idempresa']);
        echo $rspta;
        break;


    case 'mostrarxml':
        $rspta = $venta->mostrarxml($idcomprobante, $tipo_documento_07, $_SESSION['idempresa']);

        if ($rspta == "") {
            $rspta = "No se ha creado";
        }
        echo json_encode($rspta);
        break;


    case 'mostrarrpta':
        $rspta = $venta->mostrarrpta($idcomprobante, $tipo_documento_07, $_SESSION['idempresa']);
        echo json_encode($rspta);
        break;






    case 'mostrarxmlfactura':
        $rspta = $venta->mostrarxmlfactura($idcomprobante, $_SESSION['idempresa']);

        if ($rspta == "") {
            $rspta = "No se ha creado";
        }
        echo json_encode($rspta);
        break;


    case 'mostrarxmlboleta':
        $rspta = $venta->mostrarxmlboleta($idcomprobante, $_SESSION['idempresa']);
        echo json_encode($rspta);
        break;


    case 'mostrarrptafactura':
        $rspta = $venta->mostrarrptafactura($idcomprobante, $_SESSION['idempresa']);
        echo json_encode($rspta);
        break;

    case 'mostrarrptaboleta':
        $rspta = $venta->mostrarrptaboleta($idcomprobante, $_SESSION['idempresa']);
        echo json_encode($rspta);
        break;


    case 'bajaComprobante':
        $com = $_GET['comentario'];
        $hor = $_GET['hora'];
        $hoy = date("Y-m-d");
        $rspta = $venta->bajaComprobante($idcomprobante, $tipo_documento_07, $hoy, $com, $hor);
        echo $rspta ? "Comprobante de baja" : "Comprobante no se puede dar de baja";
        break;




    case 'listarValidarComprobantesSiempre':

        require_once "../modelos/Rutas.php";
        $rutas = new Rutas();
        $Rrutas = $rutas->mostrar2($_SESSION['idempresa']);
        $Prutas = $Rrutas->fetch_object();
        $rutafirma = $Prutas->rutafirma; // ruta de la carpeta FIRMA
        $rutaenvio = $Prutas->rutaenvio; // ruta de la carpeta ENVIO
        $rutarpta = $Prutas->rutarpta; // ruta de la carpeta RESPUESTA

        //Agregar=====================================================
        // Ruta del directorio donde están los archivos

        $path = $rutaenvio;
        $pathFirma = $rutafirma;
        $pathRpta = $rutarpta;
        // Arreglo con todos los nombres de los archivos
        $files = array_diff(scandir($path), array('.', '..'));
        $filesFirma = array_diff(scandir($pathFirma), array('.', '..'));
        $files2 = array_diff(scandir($pathRpta), array('.', '..'));
        //=============================================================

        $rspta = $venta->listarValidarComprobantesSiempre();
        //Vamos a declarar un array
        $data = array();

        while ($reg = $rspta->fetch_object()) {
            if ($reg->tipo_documento_07 == '01') {
                $url = '../reportes/exFactura.php?id=';
            } else {
                $url = '../reportes/exBoleta.php?id=';
            }

            //==============Agregar====================================================
            $archivo = $reg->numero_ruc . "-" . $reg->tipo_documento_07 . "-" . $reg->numeracion_08;
            $archivo2 = "R" . $reg->numero_ruc . "-" . $reg->tipo_documento_07 . "-" . $reg->numeracion_08;
            $fileBaja = $reg->numero_ruc . "-RA" . $reg->fechabaja . "-011";

            if ($reg->tipo_documento_07 == '01') { // para facturas
                require_once "../modelos/Factura.php";
                $factura = new Factura();

                if ($reg->estado == '3') {
                    $st = "3";
                    $UpSt = $factura->ActualizarEstado($reg->idcomprobante, $st);
                } elseif ($reg->estado == '0') {
                    $st = "0";
                    $UpSt = $factura->ActualizarEstadoBaja($reg->idcomprobante, $st);
                } else {
                    //Validar si existe el archivo firmado
                    foreach ($files as $file) {
                        // Divides en dos el nombre de tu archivo utilizando el . 
                        $dataSt = explode(".", $file);
                        // Nombre del archivo
                        $fileName = $dataSt[0];
                        $st = "1";
                        // Extensión del archivo 
                        $fileExtension = $dataSt[1];
                        if ($archivo == $fileName) {
                            $st = "4";
                            $UpSt = $factura->ActualizarEstado($reg->idcomprobante, $st);
                        }
                    }

                    //Validar si existe el archivo aceptado por sunat
                    foreach ($files2 as $file2) {
                        // Divides en dos el nombre de tu archivo utilizando el . 
                        $dataSt2 = explode(".", $file2);
                        // Nombre del archivo
                        $fileName = $dataSt2[0];
                        // Extensión del archivo 
                        $fileExtension = $dataSt2[1];
                        if ($archivo2 == $fileName) {
                            $st = "5";
                            $UpSt = $factura->ActualizarEstado($reg->idcomprobante, $st);
                        }
                    }
                }


            } else { // SI ES BOLETA



                require_once "../modelos/Boleta.php";
                $boleta = new Boleta();


                if ($reg->estado == '3') {
                    $st = "3";
                    $UpSt = $boleta->ActualizarEstado($reg->idcomprobante, $st);
                } else {

                    //Validar si existe el archivo firmado
                    foreach ($files as $file) {
                        // Divides en dos el nombre de tu archivo utilizando el . 
                        $dataSt = explode(".", $file);
                        // Nombre del archivo
                        $fileName = $dataSt[0];
                        $st = "1";
                        // Extensión del archivo 
                        $fileExtension = $dataSt[1];
                        if ($archivo == $fileName) {
                            $st = "4";
                            $UpSt = $boleta->ActualizarEstado($reg->idcomprobante, $st);
                        }
                    }

                    //Validar si existe el archivo aceptado por sunat
                    foreach ($files2 as $file2) {
                        // Divides en dos el nombre de tu archivo utilizando el . 
                        $dataSt2 = explode(".", $file2);
                        // Nombre del archivo
                        $fileName = $dataSt2[0];
                        // Extensión del archivo 
                        $fileExtension = $dataSt2[1];
                        if ($archivo2 == $fileName) {
                            $st = "5";
                            $UpSt = $boleta->ActualizarEstado($reg->idcomprobante, $st);
                        }
                    }
                } //Fin if 

            } // FIN SI ES FACTURA O BOLETA 

            if ($reg->estado == '1') { //SI ESTADO ES EMITIDO
                require_once "../modelos/Venta.php";
                $venta = new venta();
                $venta->generarxml($reg->idcomprobante, $reg->tipo_documento_07, $_SESSION['idempresa']);
            }

            if ($reg->estado == '5') { // SI ESTADO ES ACEPTADO
                //$factura->enviarxmlSUNAT($reg->idfactura, $_SESSION['idempresa']);
            } else if ($reg->estado == '4') // SI ESTADO ES FIRMADO
            {
                require_once "../modelos/Venta.php";
                $venta = new venta();
                $venta->enviarxmlSUNAT($reg->idcomprobante, $reg->tipo_documento_07, $_SESSION['idempresa']);
            } else {
                $send = 'none';
            }



        }
        break;

    case 'listarventasxruc':
        $nruc = $_GET['nruc'];
        $anor = $_GET['anor'];
        $mesr = $_GET['mesr'];
        $rspta = $venta->ventasxCliente($nruc, $anor, $mesr);
        //Vamos a declarar un array
        $data = array();

        while ($reg = $rspta->fetch_object()) {
            $data[] = array(
                "0" => $reg->numerofac,
                "1" => $reg->fechaemision,
                "2" => $reg->codigo,
                "3" => $reg->nombre,
                "4" => $reg->cantidad,
                "5" => $reg->subtotal,
                "6" => $reg->igv,
                "7" => $reg->total,
                "8" => ($reg->estado == '1') ? '<i class="fa fa-file-text-o" style="font-size: 10px; color:#BA4A00;"><span> EMITIDO</span></i>'
                : (($reg->estado == '4') ? '<i class="fa fa-thumbs-up" style="font-size: 10px; color:#239B56;"><span> FIRMADO</span> </i>'
                    : (($reg->estado == '3') ? '<i class="fa fa-dot-circle-o" style="font-size: 10px; color:#E59866;"><span> DE BAJA</span></i>'
                        : (($reg->estado == '0') ? '<i class="fa fa-dot-circle-o" style="font-size: 10px; color:#E59866;"><span> ANULADO</span></i>'
                            : (($reg->estado == '5') ? '<i class="fa fa-globe" style="font-size: 10px; color:#145A32;"><span> ACEPTADO SUNAT</span></i> '
                                : '<i class="fa fa-newspaper" style="font-size: 10px; color:#239B56;"> <span>----</span></i> ')))),
            );
        }
        $results = array(
            "sEcho" => 1,
            //Información para el datatables
            "iTotalRecords" => count($data),
            //enviamos el total registros al datatable
            "iTotalDisplayRecords" => count($data),
            //enviamos el total registros a visualizar
            "aaData" => $data
        );
        echo json_encode($results);

        break;

    case 'enviarcorreo':
        $rspta = $factura->enviarcorreo($idfactura, $_SESSION['idempresa']);
        echo $rspta;
        break;


    case 'consultatcambio':
        $token = 'apis-token-1.aTSI1U7KEuT-6bbbCguH-4Y8TI6KS73N';
        $ftccc = $_GET['fechatc'];

        // Iniciar llamada a API
        $curl = curl_init();

        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => 'https://api.apis.net.pe/v1/tipo-cambio-sunat?fecha=' . $ftccc,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 2,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Referer: https://apis.net.pe/tipo-de-cambio-sunat-api',
                    'Authorization: Bearer ' . $token
                ),
            )
        );

        $response = curl_exec($curl);

        curl_close($curl);
        // Datos listos para usar
        $tipoCambioSunat = json_decode($response);
        echo json_encode($tipoCambioSunat);
        break;



    case 'listarcomprobantes':

        $fec1 = $_GET['fc1'];
        $fec2 = $_GET['fc2'];
        $tipocte = $_GET['tcomp'];
        $Estd = $_GET['estad'];


        $rspta = $venta->listarcomprobantes($fec1, $fec2, $tipocte, $Estd);
        //Vamos a declarar un array
        $urlT = '../reportes/exTicketFactura.php?id=';
        $urlF = '../reportes/exFactura.php?id=';
        $urlC = '../reportes/exFacturaCompleto.php?id=';
        //==============Agregar====================================================
        $data = array();

        $stt = '';
        $vs = '';
        $sunatFirma = '';
        $sunatAceptado = 'Class';

        $estadoenvio = '1';

        $mon = "";
        while ($reg = $rspta->fetch_object()) {


            if ($reg->moneda == "USD") {
                $mon = '<i style="color:green;" data-toggle="tooltip" title="Por T.C. ' . $reg->tcambio . ' = ' . $reg->valordolsol . ' PEN">$</i>';
            } else {
                $mon = "";
            }




            //=====================================================================================
            ///"0" =>
            //     '<div class="btn-group mb-1">
            //     <div class="dropdown">
            //         <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            //             Impresiones
            //         </button>
            //         <div class="dropdown-menu" style="">

            //             <a  class="dropdown-item"onclick="prea42copias2(' . $reg->idcomprobante . ', ' . $reg->tipo_documento_07 . ')"><i class="fa  fa-print"  data-toggle="tooltip" title="Imprimir Ticket"> </i> Imprimir formato 2 copias</a>


            //         </div>
            //     </div>
            // </div>',

            //$client=substr($reg->cliente,0,10);
            $data[] = array(
                "0" => $reg->fecha,
                "1" => $reg->cliente,
                "2" => $reg->vendedorsitio,
                "3" => $reg->numeracion_08,
                "4" => $reg->importe_total_venta_27 . " " . $mon,


                //Actualizado ===============================================
                "5" => ($reg->estado == '1') ? '<img   src="../public/images/sambar.jpg" data-toggle="tooltip" title="' . $reg->DetalleSunat . '" width="30px">' //si esta emitido
                : (($reg->estado == '4') ? '<img   src="../public/images/srojo.png" data-toggle="tooltip" title="' . $reg->DetalleSunat . '" width="30px">' //si esta firmado

                    : (($reg->estado == '3') ? '<img   src="../public/images/srojo.png" data-toggle="tooltip" title="' . $reg->DetalleSunat . '" width="30px">' // si esta de baja

                        : (($reg->estado == '0') ? '<img   src="../public/images/srojo.png" data-toggle="tooltip" title="' . $reg->DetalleSunat . '" width="30px">' //si esta firmado

                            : (($reg->estado == '5') ? '<img   src="../public/images/sverde.png" data-toggle="tooltip" title="' . $reg->DetalleSunat . '" width="30px">' // Si esta aceptado por SUNAT

                                : '<span style="color:#239B56;">' . $reg->DetalleSunat . '</span>')))),

                //Opciones de envio
                "6" =>

                '<div class="btn-group mb-1">
            <div class="dropdown">
                <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Opciones
                </button>
                <div class="dropdown-menu" style="">
                    
                    <a  class="dropdown-item" onclick="mostrarxml(' . $reg->idcomprobante . ', ' . $reg->tipo_documento_07 . ')"><i class="fa fa-check" style="color:orange;" data-toggle="tooltip" title="Mostrar XML"> </i> Mostrar XML</a>
                    <a  class="dropdown-item" nclick="mostrarrpta(' . $reg->idcomprobante . ', ' . $reg->tipo_documento_07 . ')"><i class="fa fa-check" style="color:green; data-toggle="tooltip" title="Mostrar CDR"> </i> Mostrar CDR</a>
                   
                    
                </div>
            </div>
        </div>',

                "7" => ($reg->tarjetadc == '1') ? '<img src="../files/articulos/tarjetadc.png" width="20px" 
                data-toggle="tooltip" title="TARJETA ' . $reg->montotarjetadc . '">' : '',
                "8
                " => ($reg->transferencia == '1') ? '<img src="../files/articulos/transferencia.png" width="20px" data-toggle="tooltip" title="BANCO ' . $reg->montotransferencia . '">' : '',
            );
        } //Fin While

        $results = array(
            "sEcho" => 1,
            //Información para el datatables
            "iTotalRecords" => count($data),
            //enviamos el total registros al datatable
            "iTotalDisplayRecords" => count($data),
            //enviamos el total registros a visualizar
            "aaData" => $data
        );
        echo json_encode($results);
        break;


    case 'avanzar':
        $rspta = $venta->avanzar($idnotificacion);
        echo $rspta ? "Actualizado" : "Error al actualizar";
        break;



    case 'notificaciones':
        $fecnot = $_GET["fechanoti"];
        $rspta = $venta->notificaciones($fecnot);
        // echo json_encode($rspta) ;

        $data = array();
        while ($reg = $rspta->fetch_object()) {
            $data[] = array(
                "0" => $reg->nombrenotificacion,
                "1" => $reg->tipocomprobante,
                "2" => $reg->nombre_comercial,
                "3" => $reg->proxfechaaviso . '<button onclick="nextM(' . $reg->idnotificacion . ')" class="btn btn-primary btn-sm" data-toggle="tooltip" title="Pasar al siguiente mes"><span class="fa fa-chevron-circle-right"  ></span></button>',
                "4" => '<img src="../files/iconos/alarm.gif" width="300"/>'
            );
        }
        $results = array(
            "sEcho" => 1,
            "iTotalRecords" => count($data),
            "iTotalDisplayRecords" => count($data),
            "aaData" => $data
        );
        echo json_encode($results);
        break;





    case 'ComprobantesPendientes':
        $rspta = $venta->ComprobantesPendientesA();



        $data = array();
        while ($reg = $rspta->fetch_object()) {
            $data[] = array(
                "0" => $reg->fechaC,
                "1" => $reg->estadoC,
                "2" => $reg->sumaC,
                "3" => $reg->tdocu
            );
        }
        $results = array(
            "sEcho" => 1,
            "iTotalRecords" => count($data),
            "iTotalDisplayRecords" => count($data),
            "aaData" => $data
        );

        if ($data) {
            $venta->enviarcorreopendientes();
        }
        echo json_encode($results);


        break;






}

?>