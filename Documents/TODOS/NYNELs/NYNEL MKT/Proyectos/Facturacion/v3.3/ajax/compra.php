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
$idalmacen = isset($_POST["idalmacen"]) && $_POST["idalmacen"] !== "" ? limpiarCadena($_POST["idalmacen"]) : null;


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

// ========== CAMPOS SUNAT CABECERA ==========
$ruc_emisor = isset($_POST["ruc_emisor"]) ? limpiarCadena($_POST["ruc_emisor"]) : "";
$descripcion_compra = isset($_POST["descripcion_compra"]) ? limpiarCadena($_POST["descripcion_compra"]) : "";
// ===========================================

// ========== CAMPOS SUNAT DETALLE ==========
$codigo_producto = isset($_POST["codigo_producto"]) ? $_POST["codigo_producto"] : [];
$descripcion_producto = isset($_POST["descripcion_producto"]) ? $_POST["descripcion_producto"] : [];
$unidad_medida_sunat = isset($_POST["unidad_medida_sunat"]) ? $_POST["unidad_medida_sunat"] : [];
// ==========================================


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
                    $idempresa,
                    $idalmacen,
                    $ruc_emisor,
                    $descripcion_compra,
                    $codigo_producto,
                    $descripcion_producto,
                    $unidad_medida_sunat
                );

                if ($rspta) {
                    // ========== AUDITORÍA: Registrar creación de compra normal ==========
                    registrarOperacionCreate('compra', $serie_comprobante . '-' . $num_comprobante, [
                        'idproveedor' => $idproveedor,
                        'ruc_emisor' => $ruc_emisor,
                        'tipo_comprobante' => $tipo_comprobante,
                        'subtotal' => $subtotal_compra,
                        'igv' => $total_igv,
                        'total_compra' => $total_compra,
                        'moneda' => $moneda,
                        'tipo_cambio' => $tcambio,
                        'items' => count($_POST["idarticulo"]),
                        'guia_remision' => $guia,
                        'descripcion' => $descripcion_compra
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
                    $factorc,
                    $idalmacen,
                    $ruc_emisor,
                    $descripcion_compra,
                    $codigo_producto,
                    $descripcion_producto,
                    $unidad_medida_sunat
                );

                if ($rspta) {
                    // ========== AUDITORÍA: Registrar creación de compra con subartículos ==========
                    registrarOperacionCreate('compra', $serie_comprobante . '-' . $num_comprobante, [
                        'idproveedor' => $idproveedor,
                        'ruc_emisor' => $ruc_emisor,
                        'tipo_comprobante' => $tipo_comprobante,
                        'subtotal' => $subtotal_compra,
                        'igv' => $total_igv,
                        'total_compra' => $total_compra,
                        'moneda' => $moneda,
                        'tipo_cambio' => $tcambio,
                        'items' => count($_POST["idarticulo"]),
                        'con_subarticulos' => true,
                        'cantidad_total_subarticulos' => $totalcantidad,
                        'guia_remision' => $guia,
                        'descripcion' => $descripcion_compra
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

    case 'listarUnidadesSUNAT':
        // Listar todas las unidades de medida SUNAT del Catálogo 03
        global $conexion;
        $sql = "SELECT codigo_sunat, descripcion FROM umedida_sunat WHERE estado = 1 ORDER BY descripcion ASC";
        $resultado = $conexion->query($sql);

        $unidades = array();
        if ($resultado) {
            while ($row = $resultado->fetch_object()) {
                $unidades[] = array(
                    'codigo' => $row->codigo_sunat,
                    'descripcion' => $row->descripcion
                );
            }
        }

        echo json_encode($unidades);
        break;

    case 'registrarCompraRapida':
        // SEGURIDAD: Validar token CSRF
        if (!validarCSRFAjax()) {
            echo json_encode(array(
                "success" => false,
                "message" => "Error: Token de seguridad inválido. Por favor, recarga la página e intenta nuevamente."
            ));
            exit();
        }

        // Obtener datos del formulario del modal
        $fecha_compra = isset($_POST["fecha_compra"]) ? limpiarCadena($_POST["fecha_compra"]) : date('Y-m-d');
        $tipo_comprobante_modal = isset($_POST["tipo_comprobante"]) ? limpiarCadena($_POST["tipo_comprobante"]) : "01";
        $serie_modal = isset($_POST["serie"]) ? limpiarCadena($_POST["serie"]) : "";
        $numero_modal = isset($_POST["numero"]) ? limpiarCadena($_POST["numero"]) : "";
        $moneda_modal = isset($_POST["moneda"]) ? limpiarCadena($_POST["moneda"]) : "PEN";
        $codigo_barra = isset($_POST["codigo_barra"]) ? limpiarCadena($_POST["codigo_barra"]) : "";
        $unidad_medida_modal = isset($_POST["unidad_medida"]) ? limpiarCadena($_POST["unidad_medida"]) : "NIU";
        $nombre_articulo = isset($_POST["nombre_articulo"]) ? limpiarCadena($_POST["nombre_articulo"]) : "";
        $cantidad_modal = isset($_POST["cantidad"]) && $_POST["cantidad"] !== "" ? floatval($_POST["cantidad"]) : 1;
        $base_imponible = isset($_POST["base_imponible"]) && $_POST["base_imponible"] !== "" ? floatval($_POST["base_imponible"]) : 0;
        $igv_modal = isset($_POST["igv"]) && $_POST["igv"] !== "" ? floatval($_POST["igv"]) : 0;
        $importe_total = isset($_POST["importe_total"]) && $_POST["importe_total"] !== "" ? floatval($_POST["importe_total"]) : 0;

        // Validaciones básicas
        if (empty($serie_modal) || empty($numero_modal) || empty($nombre_articulo) || $cantidad_modal <= 0 || $importe_total <= 0) {
            echo json_encode(array(
                "success" => false,
                "message" => "Error: Datos incompletos. Por favor, complete todos los campos obligatorios."
            ));
            exit();
        }

        // Obtener o crear artículo
        require_once "../modelos/Articulo.php";
        $articulo_obj = new Articulo();

        // Buscar si el artículo ya existe por nombre o código de barra
        global $conexion;
        $buscar_articulo = "";
        if (!empty($codigo_barra)) {
            $buscar_articulo = "SELECT idarticulo, codigo FROM articulo
                               WHERE (nombre = ? OR codigo = ?) AND idempresa = ? AND estado = 1 LIMIT 1";
            $stmt = $conexion->prepare($buscar_articulo);
            $stmt->bind_param("ssi", $nombre_articulo, $codigo_barra, $_SESSION['idempresa']);
        } else {
            $buscar_articulo = "SELECT idarticulo, codigo FROM articulo
                               WHERE nombre = ? AND idempresa = ? AND estado = 1 LIMIT 1";
            $stmt = $conexion->prepare($buscar_articulo);
            $stmt->bind_param("si", $nombre_articulo, $_SESSION['idempresa']);
        }

        $stmt->execute();
        $resultado_articulo = $stmt->get_result();

        $idarticulo = null;
        $codigo_articulo = "";

        if ($resultado_articulo->num_rows > 0) {
            // Artículo ya existe
            $row = $resultado_articulo->fetch_object();
            $idarticulo = $row->idarticulo;
            $codigo_articulo = $row->codigo;
        } else {
            // Crear nuevo artículo
            // Generar código único si no tiene código de barra
            $codigo_nuevo = !empty($codigo_barra) ? $codigo_barra : "ART" . time();

            // Obtener ID de unidad de medida del sistema (buscar equivalencia con SUNAT)
            $sql_um = "SELECT idunidad FROM umedida WHERE codigo_sunat = ? LIMIT 1";
            $stmt_um = $conexion->prepare($sql_um);
            $stmt_um->bind_param("s", $unidad_medida_modal);
            $stmt_um->execute();
            $resultado_um = $stmt_um->get_result();

            $idunidad = 58; // NIU por defecto
            if ($resultado_um->num_rows > 0) {
                $row_um = $resultado_um->fetch_object();
                $idunidad = $row_um->idunidad;
            }

            // Calcular precio unitario
            $precio_unitario = $cantidad_modal > 0 ? ($base_imponible / $cantidad_modal) : 0;

            // Insertar artículo
            $sql_insert_art = "INSERT INTO articulo (
                codigo, nombre, unidad_medida, stock, precio_venta,
                costo_compra, idempresa, estado, codigo_sunat,
                tipo_afectacion_igv, created_at
            ) VALUES (?, ?, ?, 0, ?, ?, ?, 1, ?, '10', NOW())";

            $stmt_insert = $conexion->prepare($sql_insert_art);
            $codigo_sunat_art = "00000000"; // Genérico
            $stmt_insert->bind_param(
                "ssiidis",
                $codigo_nuevo,
                $nombre_articulo,
                $idunidad,
                $precio_unitario,
                $precio_unitario,
                $_SESSION['idempresa'],
                $codigo_sunat_art
            );

            if ($stmt_insert->execute()) {
                $idarticulo = $conexion->insert_id;
                $codigo_articulo = $codigo_nuevo;
            } else {
                echo json_encode(array(
                    "success" => false,
                    "message" => "Error al crear el artículo: " . $conexion->error
                ));
                exit();
            }
        }

        // Preparar datos para la compra
        $idusuario = $_SESSION["idusuario"];
        $idproveedor = 1; // Proveedor genérico o el primer proveedor (ajustar según necesidad)
        $fecha_emision = $fecha_compra;
        $tipo_comprobante = $tipo_comprobante_modal;
        $serie_comprobante = $serie_modal;
        $num_comprobante = $numero_modal;
        $guia = "";
        $subtotal_compra = $base_imponible;
        $total_igv = $igv_modal;
        $total_compra = $importe_total;
        $tcambio = ($moneda_modal == "USD") ? 1.00 : 1.00; // Ajustar según necesidad
        $hora = date('H:i:s');
        $moneda = $moneda_modal;
        $idempresa = $_SESSION['idempresa'];
        $idalmacen = 1; // Almacén principal por defecto (ajustar según necesidad)

        // Arrays para el detalle (un solo artículo)
        $idarticulos_array = array($idarticulo);
        $valor_unitario_array = array($precio_unitario);
        $cantidad_array = array($cantidad_modal);
        $subtotal_array = array($importe_total);
        $codigo_array = array($codigo_articulo);
        $unidad_medida_array = array($idunidad);

        // Campos SUNAT
        $ruc_emisor = "";
        $descripcion_compra = "Compra rápida: " . $nombre_articulo;
        $codigo_producto_array = array($codigo_articulo);
        $descripcion_producto_array = array($nombre_articulo);
        $unidad_medida_sunat_array = array($unidad_medida_modal);

        // Insertar compra usando el método existente
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
            $idarticulos_array,
            $valor_unitario_array,
            $cantidad_array,
            $subtotal_array,
            $codigo_array,
            $unidad_medida_array,
            $tcambio,
            $hora,
            $moneda,
            $idempresa,
            $idalmacen,
            $ruc_emisor,
            $descripcion_compra,
            $codigo_producto_array,
            $descripcion_producto_array,
            $unidad_medida_sunat_array
        );

        if ($rspta) {
            // ========== AUDITORÍA: Registrar creación de compra rápida ==========
            registrarOperacionCreate('compra', $serie_comprobante . '-' . $num_comprobante, [
                'tipo' => 'compra_rapida',
                'tipo_comprobante' => $tipo_comprobante,
                'subtotal' => $subtotal_compra,
                'igv' => $total_igv,
                'total_compra' => $total_compra,
                'moneda' => $moneda,
                'articulo' => $nombre_articulo,
                'cantidad' => $cantidad_modal,
                'unidad_medida_sunat' => $unidad_medida_modal
            ], "Compra rápida {$tipo_comprobante} {$serie_comprobante}-{$num_comprobante} registrada exitosamente por valor de {$moneda} {$total_compra}");

            echo json_encode(array(
                "success" => true,
                "message" => "Compra registrada exitosamente",
                "idcompra" => $rspta,
                "serie" => $serie_comprobante,
                "numero" => $num_comprobante
            ));
        } else {
            // ========== AUDITORÍA: Registrar intento fallido ==========
            registrarAuditoria('CREATE', 'compra', [
                'descripcion' => "Intento fallido de registrar compra rápida {$tipo_comprobante} {$serie_comprobante}-{$num_comprobante}",
                'resultado' => 'FALLIDO',
                'codigo_error' => 'ERROR_INSERTAR_COMPRA_RAPIDA',
                'mensaje_error' => 'No se pudo registrar la compra rápida',
                'metadata' => [
                    'articulo' => $nombre_articulo,
                    'total' => $total_compra
                ]
            ]);

            echo json_encode(array(
                "success" => false,
                "message" => "Error al registrar la compra. Por favor, revise los datos e intente nuevamente."
            ));
        }
        break;
}


?>