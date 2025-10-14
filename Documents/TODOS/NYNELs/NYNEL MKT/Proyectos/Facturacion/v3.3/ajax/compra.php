<?php
// SEGURIDAD: Usar sesión segura y helpers de validación
require_once "../config/Conexion.php";
require_once "../config/ajax_helper.php";
iniciarSesionSegura();

require_once "../modelos/Compra.php";

$compra = new Compra();

$idcompra = isset($_POST["idcompra"]) ? limpiarCadena($_POST["idcompra"]) : "";
$idusuario = $_SESSION["idusuario"];
$idproveedor = isset($_POST["idproveedor"]) ? limpiarCadena($_POST["idproveedor"]) : "";
$fecha_emision = isset($_POST["fecha_emision"]) ? limpiarCadena($_POST["fecha_emision"]) : "";
$tipo_comprobante = isset($_POST["tipo_comprobante"]) ? limpiarCadena($_POST["tipo_comprobante"]) : "";
$serie_comprobante = isset($_POST["serie_comprobante"]) ? limpiarCadena($_POST["serie_comprobante"]) : "";
$num_comprobante = isset($_POST["num_comprobante"]) ? limpiarCadena($_POST["num_comprobante"]) : "";
$guia = isset($_POST["guia"]) ? limpiarCadena($_POST["guia"]) : "";
$idempresa = isset($_POST["idempresa"]) ? limpiarCadena($_POST["idempresa"]) : "";


$subtotal_compra = isset($_POST["subtotal_compra"]) && $_POST["subtotal_compra"] !== "" ? limpiarCadena($_POST["subtotal_compra"]) : "0";
$total_igv = isset($_POST["total_igv"]) && $_POST["total_igv"] !== "" ? limpiarCadena($_POST["total_igv"]) : "0";
$total_compra = isset($_POST["total_final"]) && $_POST["total_final"] !== "" ? limpiarCadena($_POST["total_final"]) : "0";

$tcambio = isset($_POST["tcambio"]) && $_POST["tcambio"] !== "" ? limpiarCadena($_POST["tcambio"]) : "1";
$hora = isset($_POST["hora"]) ? limpiarCadena($_POST["hora"]) : "";
$moneda = isset($_POST["moneda"]) ? limpiarCadena($_POST["moneda"]) : "";

$subarticulo = isset($_POST["subarticulo"]) ? limpiarCadena($_POST["subarticulo"]) : "";

$idarticulonarti = isset($_POST["idarticulonarti"]) ? limpiarCadena($_POST["idarticulonarti"]) : "";
$totalcantidad = isset($_POST["totalcantidad"]) && $_POST["totalcantidad"] !== "" ? limpiarCadena($_POST["totalcantidad"]) : "0";
$totalcostounitario = isset($_POST["totalcostounitario"]) && $_POST["totalcostounitario"] !== "" ? limpiarCadena($_POST["totalcostounitario"]) : "0";

$factorc = isset($_POST["factorc"]) && $_POST["factorc"] !== "" ? limpiarCadena($_POST["factorc"]) : "1";


$vunitario = isset($_POST["vunitario"]) && $_POST["vunitario"] !== "" ? limpiarCadena($_POST["vunitario"]) : "0";




switch ($_GET["op"]) {
    case 'guardaryeditar':
        // SEGURIDAD: Validar token CSRF
        if (!validarCSRFAjax()) {
            echo "Error: Token de seguridad inválido. Por favor, recarga la página e intenta nuevamente.";
            exit();
        }

        if ($subarticulo == '0') {

            if (empty($idcompra)) {

                $rspta = $compra->insertar(
                    $idusuario,
                    $idproveedor,
                    $fecha_emision,
                    $tipo_comprobante,
                    $serie_comprobante,
                    $num_comprobante,
                    $guia,
                    $subtotal_compra,
                    $total_igv,
                    $total_compra,
                    $_POST["idarticulo"],
                    $_POST["valor_unitario"],
                    $_POST["cantidad"],
                    $_POST["subtotalBD"],
                    $_POST["codigo"],
                    $_POST["unidad_medida"],
                    $tcambio,
                    $hora,
                    $moneda,
                    $idempresa
                );

                if ($rspta) {
                    // ========== AUDITORÍA: Registrar creación de compra normal ==========
                    registrarOperacionCreate('compra', $serie_comprobante . '-' . $num_comprobante, [
                        'idproveedor' => $idproveedor,
                        'tipo_comprobante' => $tipo_comprobante,
                        'subtotal' => $subtotal_compra,
                        'igv' => $total_igv,
                        'total_compra' => $total_compra,
                        'moneda' => $moneda,
                        'tipo_cambio' => $tcambio,
                        'items' => count($_POST["idarticulo"]),
                        'guia_remision' => $guia
                    ], "Compra {$tipo_comprobante} {$serie_comprobante}-{$num_comprobante} registrada exitosamente por valor de {$moneda} {$total_compra}");

                    echo "Compra registrada";
                } else {
                    // ========== AUDITORÍA: Registrar intento fallido ==========
                    registrarAuditoria('CREATE', 'compra', [
                        'descripcion' => "Intento fallido de registrar compra {$tipo_comprobante} {$serie_comprobante}-{$num_comprobante}",
                        'resultado' => 'FALLIDO',
                        'codigo_error' => 'ERROR_INSERTAR_COMPRA',
                        'mensaje_error' => 'No se pudo registrar la compra',
                        'metadata' => [
                            'proveedor_id' => $idproveedor,
                            'total' => $total_compra
                        ]
                    ]);

                    echo "Problema al registrar la compra, revise con el la base de datos";
                }
            }

        } else {

            if (empty($idcompra)) {

                $rspta = $compra->insertarsubarticulo(
                    $idusuario,
                    $idproveedor,
                    $fecha_emision,
                    $tipo_comprobante,
                    $serie_comprobante,
                    $num_comprobante,
                    $guia,
                    $subtotal_compra,
                    $total_igv,
                    $total_compra,
                    $_POST["idarticulo"],
                    $_POST["valor_unitario"],
                    $_POST["cantidad"],
                    $_POST["subtotalBD"],
                    $_POST["codigo"],
                    $_POST["unidad_medida"],
                    $tcambio,
                    $hora,
                    $moneda,
                    $idempresa,
                    $_POST["codigobarra"],
                    $idarticulonarti,
                    $totalcantidad,
                    $totalcostounitario,
                    $vunitario,
                    $factorc
                );

                if ($rspta) {
                    // ========== AUDITORÍA: Registrar creación de compra con subartículos ==========
                    registrarOperacionCreate('compra', $serie_comprobante . '-' . $num_comprobante, [
                        'idproveedor' => $idproveedor,
                        'tipo_comprobante' => $tipo_comprobante,
                        'subtotal' => $subtotal_compra,
                        'igv' => $total_igv,
                        'total_compra' => $total_compra,
                        'moneda' => $moneda,
                        'tipo_cambio' => $tcambio,
                        'items' => count($_POST["idarticulo"]),
                        'con_subarticulos' => true,
                        'cantidad_total_subarticulos' => $totalcantidad,
                        'guia_remision' => $guia
                    ], "Compra {$tipo_comprobante} {$serie_comprobante}-{$num_comprobante} con subartículos registrada exitosamente por valor de {$moneda} {$total_compra}");

                    echo "Compra registrada con subarticulos";
                } else {
                    // ========== AUDITORÍA: Registrar intento fallido ==========
                    registrarAuditoria('CREATE', 'compra', [
                        'descripcion' => "Intento fallido de registrar compra con subartículos {$tipo_comprobante} {$serie_comprobante}-{$num_comprobante}",
                        'resultado' => 'FALLIDO',
                        'codigo_error' => 'ERROR_INSERTAR_COMPRA_SUBARTICULOS',
                        'mensaje_error' => 'No se pudo registrar la compra con subartículos',
                        'metadata' => [
                            'proveedor_id' => $idproveedor,
                            'total' => $total_compra,
                            'con_subarticulos' => true
                        ]
                    ]);

                    echo "Problema al registrar la compra, revise con el la base de datos";
                }
            }

        }


        break;

    // case 'anular':
    //     $rspta=$compra->anular($idcompra);
    //     echo $rspta ? "Ingreso anulado" : "Ingreso no se puede anular";
    // break;

    case 'mostrar':
        $rspta = $compra->mostrar($idcompra);
        //Codificar el resultado utilizando json
        echo json_encode($rspta);
        break;

    case 'eliminarcompra':
        // SEGURIDAD: Validar token CSRF
        if (!validarCSRFAjax()) {
            echo "Error: Token de seguridad inválido. Por favor, recarga la página e intenta nuevamente.";
            exit();
        }

        date_default_timezone_set('America/Lima');
        //$hoy=date('Y/m/d');
        $hoy = date("Y-m-d");
        $rspta = $compra->AnularCompra($idcompra, $hoy, $_SESSION['idempresa']);

        if ($rspta) {
            // ========== AUDITORÍA: Registrar anulación de compra ==========
            registrarOperacionAnular('compra', $idcompra, [
                'fecha_anulacion' => $hoy,
                'metodo' => 'AnularCompra'
            ], "Compra #{$idcompra} anulada exitosamente");

            echo "Compra eliminada";
        } else {
            // ========== AUDITORÍA: Registrar intento fallido de anulación ==========
            registrarAuditoria('ANULAR', 'compra', [
                'registro_id' => $idcompra,
                'descripcion' => "Intento fallido de anular compra #{$idcompra}",
                'resultado' => 'FALLIDO',
                'codigo_error' => 'ERROR_ANULAR_COMPRA',
                'mensaje_error' => 'No se pudo anular la compra',
                'metadata' => [
                    'fecha_intento' => $hoy
                ]
            ]);

            echo "Problema al eliminar la compra, revise con el la base de datos";
        }
        break;






    case 'listarDetalle':
        //Recibimos el idingreso
        $id = $_GET['id'];

        $rspta = $compra->listarDetalle($id);
        $subt = 0;
        $igv = 0;
        $total = 0;
        echo '<thead style="background-color:#A9D0F5">
                                    <th>ARTÍCULO</th>
                                    <th>CANTIDAD</th>
                                    <th>PRECIO COMPRA</th>
                                    <th>Subtotal</th>
                                </thead>';

        while ($reg = $rspta->fetch_object()) {
            echo '<tr class="filass"> <td>' . $reg->nombre . '</td><td>' . $reg->cantidad . '</td><td>' . $reg->costo_compra . '</td><td>' . $reg->subtotal . '</td></tr>';

            $subt = $subt + ($reg->subtotal);
            $igv = $igv + ($reg->subtotal * 0.18);
            $total = $subt + $igv;
        }
        echo ' <tfoot style="vertical-align: center;">

                                <!--SUBTOTAL-->
                                    <tr>
                          <td><td></td><td></td><td></td><td></td><td><td>

                                    <th style="font-weight: bold; vertical-align: center; background-color:#A5E393;">SUBTOTAL (S/.)</th>

                                    <th style="font-weight: bold; background-color:#A5E393;">

                                      <h4 id="subtotal" style="font-weight: bold; vertical-align: center; background-color:#A5E393;">' . $subt . '</h4></th>
                                    </td>
                                    </tr>

                                <!--IGV-->
                          <tr><td><td></td><td></td><td></td><td></td><td><td>

                                    <th  style="font-weight: bold; vertical-align: center; background-color:#A5E393;"> IGV  18% (S/.)</th>

                                    <th style="font-weight: bold; background-color:#A5E393; vertical-align: center;">

                                      <h4 id="igv_" style="vertical-align: right; font-weight: bold; background-color:#A5E393;">' . $igv . '</h4>

                                    </th>
                                    </td>
                                    </tr>



                                    <tr><td><td></td><td></td><td></td><td></td><td><td>
                                    <th style="font-weight: bold; vertical-align: center; background-color:#FFB887;">TOTAL (S/.)</th> <!--Datos de impuestos-->  <!--IGV-->
                                    <th style="font-weight: bold; background-color:#FFB887;">

                                      <h4 id="total" style="font-weight: bold; vertical-align: center; background-color:#FFB887;">' . $total . '</h4>


                                    </th><!--Datos de impuestos-->  <!--TOTAL-->
                                    </td>
                                    </tr>


                                </tfoot>';
        break;





    case 'listar':
        $idempre = $_GET['idempresa'];
        $rspta = $compra->listar($_SESSION['idempresa']);
        //Vamos a declarar un array
        $data = array();

        $st = "";

        while ($reg = $rspta->fetch_object()) {
            if ($reg->estado == '3') {
                $st = 'none';
            } else {
                $st = '';
            }
            $data[] = array(
                

                
                "0" => $reg->fecha,
                "1" => $reg->proveedor,
                "2" => $reg->usuario,
                "3" => $reg->descripcion,
                "4" => $reg->serie . '-' . $reg->numero,
                "5" => $reg->total,
                "6" => ($reg->estado == '1') ? '<span class="badge bg-success-transparent"><i class="ri-check-fill align-middle me-1"></i>Ingresado</span>' : '<span class="badge bg-danger-transparent"><i class="ri-close-fill align-middle me-1"></i>Anualdo</span>',
                "7" =>
                    '
                <div class="dropdown"> 
                <button type="button" class="btn btn-wave waves-effect waves-light btn-sm btn-primary-light btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
									Opciones
				</button>
				<div class="dropdown-menu" style="">
				
				<a class="dropdown-item" href="../reportes/compraReporte.php?idcompra=' . 
                $reg->idcompra . '">Imprmir</a>
                <a class="dropdown-item" onclick="eliminarcompra('.$reg->idcompra.')">Anular compra</a> </div>

                '

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

    case 'selectProveedor':
        require_once "../modelos/Persona.php";
        $persona = new Persona();

        $rspta = $persona->listarp();

        while ($reg = $rspta->fetch_object()) {
            echo '<option value=' . $reg->idpersona . '>' . $reg->razon_social . '</option>';
        }
        break;

    case 'listarArticulos':
        $subarticu = $_GET['subarti'];
        require_once "../modelos/Articulo.php";
        $articulo = new Articulo();

        $rspta = $articulo->listarActivos($_SESSION['idempresa']);
        $data = array();

        while ($reg = $rspta->fetch_object()) {

            $data[] = array(
                
                "0" => $reg->codigo,
                "1" => $reg->nombre,
                "2" => $reg->codigo_proveedor,
                "3" => $reg->nombreum,
                "4" => $reg->stock,
                "5" => $reg->precio_final_kardex,
                "6" => '<button class="btn btn-primary btn-sm btn-wave waves-effect 
                waves-light" onclick="agregarDetalle(' . $reg->idarticulo . ',\'' . $reg->nombre . '\',\'' . 
                $reg->codigo_proveedor . '\',\'' . $reg->codigo . '\',\'' . $reg->nombre . '\',\'' . 
                $reg->precio_venta . '\',\'' . $reg->stock . '\',\'' . $reg->abre . '\' ,\'' . 
                $reg->precio_unitario . '\', \'' . $reg->costo_compra . '\', \'' . $reg->factorc . '\' , \'' . $reg->nombreum . '\')">Agregar</button>'
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


    case 'mostrarumventa':
        $ida = $_GET['idarti'];
        require_once "../modelos/Articulo.php";
        $articulo = new Articulo();
        $rspta2 = $articulo->listarActivosumventa($ida);
        echo json_encode($rspta2);
        break;



    case 'listarArticuloscompraxcodigo':
        require_once "../modelos/Articulo.php";
        $articulo = new Articulo();
        $codigob = $_GET['codigob'];
        $rspta = $articulo->listarActivosVentaxCodigo($codigob, $_SESSION['idempresa']);
        echo json_encode($rspta);
        break;

    case 'buscarProveedorPorRUC':
        require_once "../modelos/Persona.php";
        $persona = new Persona();
        $ruc = isset($_POST["ruc"]) ? limpiarCadena($_POST["ruc"]) : "";

        // Buscar proveedor por RUC en la tabla persona donde tipo_persona='Proveedor'
        $rspta = $persona->buscarPorRUC($ruc);

        if ($rspta) {
            echo json_encode(array(
                "encontrado" => true,
                "idpersona" => $rspta['idpersona'],
                "nombre" => $rspta['nombre']
            ));
        } else {
            echo json_encode(array(
                "encontrado" => false
            ));
        }
        break;
}


?>