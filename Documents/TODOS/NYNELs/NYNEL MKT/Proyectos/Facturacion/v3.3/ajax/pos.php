<?php
// SEGURIDAD: Iniciar sesión y conexión
// Nota: Conexion.php ya llama a iniciarSesionSegura() automáticamente
require_once "../config/Conexion.php";

require_once "../modelos/PosModelo.php";
$posmodelo = new PosModelo();
//Primeros productos
// NOTA: $idarticulo puede ser array en Nota de Crédito/Débito, NO aplicar limpiarCadena aquí
$idarticulo = isset($_POST["idarticulo"]) ? $_POST["idarticulo"] : "";
$idfamilia = isset($_POST["idfamilia"]) ? limpiarCadena($_POST["idfamilia"]) : "";
$codigo_proveedor = isset($_POST["codigo_proveedor"]) ? limpiarCadena($_POST["codigo_proveedor"]) : "";
$codigo = isset($_POST["codigo"]) ? limpiarCadena($_POST["codigo"]) : "";
$familia = isset($_POST["familia"]) ? limpiarCadena($_POST["familia"]) : "";
$nombre = isset($_POST["nombre"]) ? limpiarCadena($_POST["nombre"]) : "";
$stock = isset($_POST["stock"]) ? limpiarCadena($_POST["stock"]) : "";
$precio = isset($_POST["precio"]) ? limpiarCadena($_POST["precio"]) : "";
$costo_compra = isset($_POST["costo_compra"]) ? limpiarCadena($_POST["costo_compra"]) : "";
$imagen = isset($_POST["imagen"]) ? limpiarCadena($_POST["imagen"]) : "";
$precio_final_kardex = isset($_POST["precio_final_kardex"]) ? limpiarCadena($_POST["precio_final_kardex"]) : "";
$precio2 = isset($_POST["precio2"]) ? limpiarCadena($_POST["precio2"]) : "";
$precio3 = isset($_POST["precio3"]) ? limpiarCadena($_POST["precio3"]) : "";
$unidad_medida = isset($_POST["unidad_medida"]) ? limpiarCadena($_POST["unidad_medida"]) : "";
$ccontable = isset($_POST["ccontable"]) ? limpiarCadena($_POST["ccontable"]) : "";
$nombreum = isset($_POST["nombreum"]) ? limpiarCadena($_POST["nombreum"]) : "";
$fechavencimiento = isset($_POST["fechavencimiento"]) ? limpiarCadena($_POST["fechavencimiento"]) : "";
$nombreal = isset($_POST["nombreal"]) ? limpiarCadena($_POST["nombreal"]) : "";

//comprobantes:
$fechaDesde = isset($_POST["fechaDesde"]) ? limpiarCadena($_POST["fechaDesde"]) : "";
$fechaHasta = isset($_POST["fechaHasta"]) ? limpiarCadena($_POST["fechaHasta"]) : "";
$tipoComprobante = isset($_POST["tipoComprobante"]) ? limpiarCadena($_POST["tipoComprobante"]) : "";


//personas
$tipo_documento = isset($_POST["tipo_documento"]) ? limpiarCadena($_POST["tipo_documento"]) : "";
$numero_documento = isset($_POST["numero_documento"]) ? limpiarCadena($_POST["numero_documento"]) : "";
$razon_social = isset($_POST["razon_social"]) ? limpiarCadena($_POST["razon_social"]) : "";
$domicilio_fiscal = isset($_POST["domicilio_fiscal"]) ? limpiarCadena($_POST["domicilio_fiscal"]) : "";



//Limpiar Familia 

$idfamilia = isset($_POST["idfamilia"]) ? limpiarcadena($_POST["idfamilia"]) : null;
$idfamilia = isset($_GET["idfamilia"]) ? limpiarcadena($_GET["idfamilia"]) : null;
$busqueda = isset($_GET["busqueda"]) ? limpiarcadena($_GET["busqueda"]) : null;




require_once "../modelos/Rutas.php";
$rutas = new Rutas();
$Rrutas = $rutas->mostrar2("1");
$Prutas = $Rrutas->fetch_object();
$rutaimagen = $Prutas->rutaarticulos; // ruta de la imagen


if (isset($_GET['action'])) {
    $action = $_GET['action'];
} else {
    $action = '';
}

if ($action == 'listarProducto') {
    $rspta = $posmodelo->listarProducto(1, $idfamilia, $busqueda);
    $data = array();

    // Obtiene la URL base
    $baseURL = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
    $currentDir = dirname($_SERVER['REQUEST_URI']); // Obtiene el directorio actual sin el script
    $baseURL = $baseURL . $currentDir; // Concatena el host con el directorio
    $baseURL = preg_replace('#/ajax$#', '', $baseURL); // Elimina la parte "/ajax" si existe

    while ($reg = $rspta->fetch_object()) {
        $imagenURL = $baseURL . '/files/articulos/' . $reg->imagen;

        $data[] = array(
            'idarticulo' => $reg->idarticulo,
            'idfamilia' => $reg->idfamilia,
            'codigo_proveedor' => $reg->codigo_proveedor,
            'codigo' => $reg->codigo,
            'familia' => $reg->familia,
            'nombre' => $reg->nombre,
            'stock' => $reg->stock,
            'precio' => $reg->precio,
            'costo_compra' => $reg->costo_compra,
            'precio_unitario' => $reg->precio_unitario,
            'cicbper' => $reg->cicbper,
            'mticbperu' => $reg->mticbperu,
            // 'factorconversion' => $reg->factorconversion,
            //(a.factorc * a.stock) as factorconversion,
            'factorc' => $reg->factorc,
            'descrip' => $reg->descrip,
            'tipoitem' => $reg->tipoitem,
            'imagen' => $imagenURL,
            // Utilizar la URL completa de la imagen
            'precio_final_kardex' => $reg->precio_final_kardex,
            'precio2' => $reg->precio2,
            'precio3' => $reg->precio3,
            'unidad_medida' => $reg->unidad_medida,
            'ccontable' => $reg->ccontable,
            'st2' => $reg->st2,
            'nombreum' => $reg->nombreum,
            'abre' => $reg->abre,
            'fechavencimiento' => $reg->fechavencimiento,
            'nombreal' => $reg->nombreal
        );
    }
    $results = array(
        "ListaProductos" => $data
    );

    header('Content-type: application/json');
    echo json_encode($results);
}


//Listar Categorias : 

if ($action == 'listarCategorias') {
    $rspta = $posmodelo->listarcategorias();
    $data = array();

    while ($reg = $rspta->fetch_object()) {
        $data[] = array(
            'idfamilia' => $reg->idfamilia,
            'familia' => $reg->familia,
            'estado' => $reg->estado
        );
    }
    $results = array(
        "ListaCategorias" => $data
    );

    header('Content-type: application/json');
    echo json_encode($results);
}



//Comprobantes boleta, factura y nota de venta

$data = json_decode(file_get_contents("php://input"), true);
if ($data) { // Verificamos si se ha enviado algo en formato JSON
    $idempresa = isset($data['idempresa']) ? $data['idempresa'] : "";
    $fechainicio = isset($data['fechainicio']) ? $data['fechainicio'] : "";
    $fechafinal = isset($data['fechafinal']) ? $data['fechafinal'] : "";
    $tipocomprobante = isset($data['tipocomprobante']) ? $data['tipocomprobante'] : "";
}

if ($action == 'listarComprobantesVarios') {
    $rspta = $posmodelo->listarComprobantesVarios($idempresa, $fechainicio, $fechafinal, $tipocomprobante);
    $data = array();

    while ($reg = $rspta->fetch_object()) {
        $data[] = array(
            'id' => $reg->id,
            'fecha' => $reg->fecha,
            'cliente' => $reg->cliente,
            'estado' => $reg->estado,
            'tipo_comprobante' => $reg->tipo_comprobante,
            'producto' => $reg->producto,
            'unidades_vendidas' => $reg->unidades_vendidas,
            'total' => $reg->total
        );
    }

    $results = array(
        "ListaComprobantes" => $data
    );

    header('Content-type: application/json');
    echo json_encode($results);
}


//insertar personas  - clientes : 



if ($action == 'insertarClientePOS') {


    // Primero verifica si el cliente ya existe.
    if ($persona->clienteExiste($numero_documento)) {
        echo json_encode(['status' => 'error', 'message' => 'El cliente ya existe.']);
    } else {
        // Si el cliente no existe, inserta el nuevo cliente.
        if ($persona->insertarClientePOS($tipo_documento, $numero_documento, $razon_social, $domicilio_fiscal)) {
            echo json_encode(['status' => 'success', 'message' => 'Cliente insertado correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al insertar el cliente.']);
        }
    }
}

// ============================================================================
// OPERACIONES PARA NOTA DE CRÉDITO
// ============================================================================

/**
 * Listar comprobantes disponibles para Nota de Crédito
 * Retorna Facturas y Boletas que pueden ser acreditadas
 */
if (isset($_GET['op']) && $_GET['op'] == 'listarComprobantesParaNC') {
    // Validar que la sesión tenga idempresa
    if (!isset($_SESSION['idempresa']) || empty($_SESSION['idempresa'])) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Sesión inválida. Por favor, inicie sesión nuevamente.']);
        exit;
    }

    $tipo = isset($_POST['tipo']) ? limpiarCadena($_POST['tipo']) : "";
    $fecha_desde = isset($_POST['fecha_desde']) ? limpiarCadena($_POST['fecha_desde']) : "";
    $fecha_hasta = isset($_POST['fecha_hasta']) ? limpiarCadena($_POST['fecha_hasta']) : "";

    // Consulta unificada para Facturas y Boletas
    $sql = "SELECT
        'factura' AS origen,
        f.idfactura AS idboleta,
        f.idfactura,
        '01' AS tipo_comprobante,
        LEFT(f.numeracion_08, 4) AS serie,
        RIGHT(f.numeracion_08, 8) AS numero,
        DATE_FORMAT(f.fecha_emision_01, '%Y-%m-%d') AS fecha_emision,
        p.razon_social AS cliente,
        p.numero_documento AS num_documento,
        f.importe_total_venta_27 AS total,
        f.estado,
        f.idcliente,
        p.tipo_documento AS tipo_doc_cliente,
        p.domicilio_fiscal
    FROM factura f
    INNER JOIN persona p ON f.idcliente = p.idpersona
    WHERE f.estado != 'Anulado'
      AND f.idempresa = '{$_SESSION['idempresa']}'";

    // Agregar filtro por tipo si se especifica (01=Factura, 03=Boleta)
    if ($tipo != "" && $tipo != "01") {
        // Si el filtro no es '01' (Factura), excluir todas las facturas
        $sql .= " AND 1=0";
    }

    // Agregar filtro por rango de fechas
    if ($fecha_desde != "" && $fecha_hasta != "") {
        $sql .= " AND DATE(f.fecha_emision_01) BETWEEN '$fecha_desde' AND '$fecha_hasta'";
    }

    // Unir con boletas
    $sql .= " UNION ALL
    SELECT
        'boleta' AS origen,
        b.idboleta,
        NULL AS idfactura,
        '03' AS tipo_comprobante,
        LEFT(b.numeracion_07, 4) AS serie,
        RIGHT(b.numeracion_07, 8) AS numero,
        DATE_FORMAT(b.fecha_emision_01, '%Y-%m-%d') AS fecha_emision,
        p.razon_social AS cliente,
        p.numero_documento AS num_documento,
        b.importe_total_23 AS total,
        b.estado,
        b.idcliente,
        p.tipo_documento AS tipo_doc_cliente,
        p.domicilio_fiscal
    FROM boleta b
    INNER JOIN persona p ON b.idcliente = p.idpersona
    WHERE b.estado != 'Anulado'
      AND b.idempresa = '{$_SESSION['idempresa']}'";

    // Aplicar mismo filtro para boletas
    if ($tipo != "" && $tipo != "03") {
        // Si el filtro no es '03' (Boleta), excluir todas las boletas
        $sql .= " AND 1=0";
    }

    if ($fecha_desde != "" && $fecha_hasta != "") {
        $sql .= " AND DATE(b.fecha_emision_01) BETWEEN '$fecha_desde' AND '$fecha_hasta'";
    }

    // Ordenar por fecha descendente
    $sql .= " ORDER BY fecha_emision DESC LIMIT 50";

    try {
        $resultado = ejecutarConsulta($sql);

        if (!$resultado) {
            throw new Exception("Error al ejecutar la consulta de comprobantes para NC");
        }

        $data = array();
        while ($reg = $resultado->fetch_object()) {
            $data[] = array(
                'origen' => $reg->origen,
                'idboleta' => $reg->idboleta,
                'idfactura' => $reg->idfactura,
                'tipo_comprobante' => $reg->tipo_comprobante,
                'serie' => $reg->serie,
                'numero' => $reg->numero,
                'fecha_emision' => $reg->fecha_emision,
                'cliente' => $reg->cliente,
                'num_documento' => $reg->num_documento,
                'total' => $reg->total,
                'estado' => $reg->estado,
                'idcliente' => $reg->idcliente,
                'tipo_doc_cliente' => $reg->tipo_doc_cliente,
                'domicilio_fiscal' => $reg->domicilio_fiscal
            );
        }

        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    } catch (Exception $e) {
        error_log("Error en listarComprobantesParaNC: " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Error al obtener comprobantes: ' . $e->getMessage()]);
        exit;
    }
}

/**
 * Obtener datos completos de un comprobante para NC
 * Incluye datos del comprobante, cliente e items
 */
if (isset($_GET['op']) && $_GET['op'] == 'obtenerDatosComprobanteNC') {
    // Validar que la sesión tenga idempresa
    if (!isset($_SESSION['idempresa']) || empty($_SESSION['idempresa'])) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Sesión inválida. Por favor, inicie sesión nuevamente.']);
        exit;
    }

    $idcomprobante = isset($_POST['idcomprobante']) ? limpiarCadena($_POST['idcomprobante']) : "";
    $tipo_comprobante = isset($_POST['tipo_comprobante']) ? limpiarCadena($_POST['tipo_comprobante']) : "";

    if (empty($idcomprobante) || empty($tipo_comprobante)) {
        echo json_encode(['error' => 'Parámetros incompletos']);
        exit;
    }

    // Determinar tabla según tipo de comprobante
    if ($tipo_comprobante == "01") {
        // Factura
        $tabla_comprobante = "factura";
        $tabla_detalle = "detalle_fac_art";
        $campo_id = "idfactura";
        $col_fecha = "fecha_emision_01";
        $col_total = "importe_total_venta_27";
        $col_numeracion = "numeracion_08";
        $col_cantidad = "cantidad_item_12";
        $col_precio = "precio_venta_item_15_2";
        $col_valor_unitario = "valor_uni_item_14";
        $col_afectacion_igv = "afectacion_igv_item_16_1";
    } else {
        // Boleta
        $tabla_comprobante = "boleta";
        $tabla_detalle = "detalle_boleta_producto";
        $campo_id = "idboleta";
        $col_fecha = "fecha_emision_01";
        $col_total = "importe_total_23";
        $col_numeracion = "numeracion_07";
        $col_cantidad = "cantidad_item_12";
        $col_precio = "precio_uni_item_14_2";
        $col_valor_unitario = "valor_uni_item_31";
        $col_afectacion_igv = "afectacion_igv_5";
    }

    // Obtener datos del comprobante y cliente
    $sql_comprobante = "SELECT
        c.$campo_id AS idcomprobante,
        LEFT(c.$col_numeracion, 4) AS serie,
        RIGHT(c.$col_numeracion, 8) AS numero,
        DATE_FORMAT(c.$col_fecha, '%Y-%m-%d') AS fecha_emision,
        c.$col_total AS total_venta,
        c.idcliente,
        p.tipo_documento AS tipo_documento_cliente,
        p.numero_documento,
        p.razon_social,
        p.domicilio_fiscal
    FROM $tabla_comprobante c
    INNER JOIN persona p ON c.idcliente = p.idpersona
    WHERE c.$campo_id = '$idcomprobante'
      AND c.idempresa = '{$_SESSION['idempresa']}'";

    try {
        $resultado_comp = ejecutarConsulta($sql_comprobante);

        if (!$resultado_comp || $resultado_comp->num_rows == 0) {
            echo json_encode(['error' => 'Comprobante no encontrado']);
            exit;
        }

        $comprobante = $resultado_comp->fetch_object();

        // Obtener items del comprobante
        $sql_items = "SELECT
            d.idarticulo,
            d.$col_cantidad AS cantidad,
            d.$col_precio AS precio_unitario,
            d.$col_valor_unitario AS valor_unitario,
            d.$col_afectacion_igv AS codigo_tipo_igv,
            a.codigo,
            a.nombre AS descripcion,
            a.unidad_medida,
            a.imagen
        FROM $tabla_detalle d
        INNER JOIN articulo a ON d.idarticulo = a.idarticulo
        WHERE d.$campo_id = '$idcomprobante'";

        $resultado_items = ejecutarConsulta($sql_items);

        $items = array();
        while ($item = $resultado_items->fetch_object()) {
            $items[] = array(
                'idarticulo' => $item->idarticulo,
                'codigo' => $item->codigo,
                'descripcion' => $item->descripcion,
                'unidad_medida' => $item->unidad_medida,
                'cantidad' => $item->cantidad,
                'precio_unitario' => $item->precio_unitario,
                'valor_unitario' => $item->valor_unitario,
                'codigo_tipo_igv' => $item->codigo_tipo_igv,
                'imagen' => $item->imagen
            );
        }

        // Preparar respuesta completa
        $response = array(
            'idcomprobante' => $comprobante->idcomprobante,
            'tipo_comprobante' => $tipo_comprobante,
            'serie' => $comprobante->serie,
            'numero' => $comprobante->numero,
            'fecha_emision' => $comprobante->fecha_emision,
            'total' => $comprobante->total_venta,
            'idcliente' => $comprobante->idcliente,
            'tipo_documento' => $comprobante->tipo_documento_cliente,
            'numero_documento' => $comprobante->numero_documento,
            'razon_social' => $comprobante->razon_social,
            'domicilio_fiscal' => $comprobante->domicilio_fiscal,
            'items' => $items
        );
    } catch (Exception $e) {
        error_log("Error en obtenerDatosComprobante: " . $e->getMessage());
        echo json_encode(['error' => 'Error al obtener datos del comprobante: ' . $e->getMessage()]);
        exit;
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// ============================================================================
// OPERACIONES PARA NOTA DE DÉBITO
// ============================================================================

/**
 * Listar comprobantes disponibles para Nota de Débito
 * Retorna Facturas y Boletas que pueden ser debitadas
 */
if (isset($_GET['op']) && $_GET['op'] == 'listarComprobantesParaND') {
    // Validar que la sesión tenga idempresa
    if (!isset($_SESSION['idempresa']) || empty($_SESSION['idempresa'])) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Sesión inválida. Por favor, inicie sesión nuevamente.']);
        exit;
    }

    $tipo = isset($_POST['tipo']) ? limpiarCadena($_POST['tipo']) : "";
    $fecha_desde = isset($_POST['fecha_desde']) ? limpiarCadena($_POST['fecha_desde']) : "";
    $fecha_hasta = isset($_POST['fecha_hasta']) ? limpiarCadena($_POST['fecha_hasta']) : "";

    // Consulta unificada para Facturas y Boletas
    $sql = "SELECT
        'factura' AS origen,
        f.idfactura AS idboleta,
        f.idfactura,
        '01' AS tipo_comprobante,
        LEFT(f.numeracion_08, 4) AS serie,
        RIGHT(f.numeracion_08, 8) AS numero,
        DATE_FORMAT(f.fecha_emision_01, '%Y-%m-%d') AS fecha_emision,
        p.razon_social AS cliente,
        p.numero_documento AS num_documento,
        f.importe_total_venta_27 AS total,
        f.estado,
        f.idcliente,
        p.tipo_documento AS tipo_doc_cliente,
        p.domicilio_fiscal
    FROM factura f
    INNER JOIN persona p ON f.idcliente = p.idpersona
    WHERE f.estado != 'Anulado'
      AND f.idempresa = '{$_SESSION['idempresa']}'";

    // Agregar filtro por tipo si se especifica (01=Factura, 03=Boleta)
    if ($tipo != "" && $tipo != "01") {
        // Si el filtro no es '01' (Factura), excluir todas las facturas
        $sql .= " AND 1=0";
    }

    // Agregar filtro por rango de fechas
    if ($fecha_desde != "" && $fecha_hasta != "") {
        $sql .= " AND DATE(f.fecha_emision_01) BETWEEN '$fecha_desde' AND '$fecha_hasta'";
    }

    // Unir con boletas
    $sql .= " UNION ALL
    SELECT
        'boleta' AS origen,
        b.idboleta,
        NULL AS idfactura,
        '03' AS tipo_comprobante,
        LEFT(b.numeracion_07, 4) AS serie,
        RIGHT(b.numeracion_07, 8) AS numero,
        DATE_FORMAT(b.fecha_emision_01, '%Y-%m-%d') AS fecha_emision,
        p.razon_social AS cliente,
        p.numero_documento AS num_documento,
        b.importe_total_23 AS total,
        b.estado,
        b.idcliente,
        p.tipo_documento AS tipo_doc_cliente,
        p.domicilio_fiscal
    FROM boleta b
    INNER JOIN persona p ON b.idcliente = p.idpersona
    WHERE b.estado != 'Anulado'
      AND b.idempresa = '{$_SESSION['idempresa']}'";

    // Aplicar mismo filtro para boletas
    if ($tipo != "" && $tipo != "03") {
        // Si el filtro no es '03' (Boleta), excluir todas las boletas
        $sql .= " AND 1=0";
    }

    if ($fecha_desde != "" && $fecha_hasta != "") {
        $sql .= " AND DATE(b.fecha_emision_01) BETWEEN '$fecha_desde' AND '$fecha_hasta'";
    }

    // Ordenar por fecha descendente
    $sql .= " ORDER BY fecha_emision DESC LIMIT 50";

    try {
        $resultado = ejecutarConsulta($sql);

        if (!$resultado) {
            throw new Exception("Error al ejecutar la consulta de comprobantes para ND");
        }

        $data = array();
        while ($reg = $resultado->fetch_object()) {
            $data[] = array(
                'origen' => $reg->origen,
                'idboleta' => $reg->idboleta,
                'idfactura' => $reg->idfactura,
                'tipo_comprobante' => $reg->tipo_comprobante,
                'serie' => $reg->serie,
                'numero' => $reg->numero,
                'fecha_emision' => $reg->fecha_emision,
                'cliente' => $reg->cliente,
                'num_documento' => $reg->num_documento,
                'total' => $reg->total,
                'estado' => $reg->estado,
                'idcliente' => $reg->idcliente,
                'tipo_doc_cliente' => $reg->tipo_doc_cliente,
                'domicilio_fiscal' => $reg->domicilio_fiscal
            );
        }

        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    } catch (Exception $e) {
        error_log("Error en listarComprobantesParaND: " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Error al obtener comprobantes: ' . $e->getMessage()]);
        exit;
    }
}

/**
 * Obtener datos completos de un comprobante para ND
 * Incluye datos del comprobante, cliente e items
 */
if (isset($_GET['op']) && $_GET['op'] == 'obtenerDatosComprobanteND') {
    // Validar que la sesión tenga idempresa
    if (!isset($_SESSION['idempresa']) || empty($_SESSION['idempresa'])) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Sesión inválida. Por favor, inicie sesión nuevamente.']);
        exit;
    }

    $idcomprobante = isset($_POST['idcomprobante']) ? limpiarCadena($_POST['idcomprobante']) : "";
    $tipo_comprobante = isset($_POST['tipo_comprobante']) ? limpiarCadena($_POST['tipo_comprobante']) : "";

    if (empty($idcomprobante) || empty($tipo_comprobante)) {
        echo json_encode(['error' => 'Parámetros incompletos']);
        exit;
    }

    // Determinar tabla según tipo de comprobante
    if ($tipo_comprobante == "01") {
        // Factura
        $tabla_comprobante = "factura";
        $tabla_detalle = "detalle_fac_art";
        $campo_id = "idfactura";
        $col_fecha = "fecha_emision_01";
        $col_total = "importe_total_venta_27";
        $col_numeracion = "numeracion_08";
        $col_cantidad = "cantidad_item_12";
        $col_precio = "precio_venta_item_15_2";
        $col_valor_unitario = "valor_uni_item_14";
        $col_afectacion_igv = "afectacion_igv_item_16_1";
    } else {
        // Boleta
        $tabla_comprobante = "boleta";
        $tabla_detalle = "detalle_boleta_producto";
        $campo_id = "idboleta";
        $col_fecha = "fecha_emision_01";
        $col_total = "importe_total_23";
        $col_numeracion = "numeracion_07";
        $col_cantidad = "cantidad_item_12";
        $col_precio = "precio_uni_item_14_2";
        $col_valor_unitario = "valor_uni_item_31";
        $col_afectacion_igv = "afectacion_igv_5";
    }

    // Obtener datos del comprobante y cliente
    $sql_comprobante = "SELECT
        c.$campo_id AS idcomprobante,
        LEFT(c.$col_numeracion, 4) AS serie,
        RIGHT(c.$col_numeracion, 8) AS numero,
        DATE_FORMAT(c.$col_fecha, '%Y-%m-%d') AS fecha_emision,
        c.$col_total AS total_venta,
        c.idcliente,
        p.tipo_documento AS tipo_documento_cliente,
        p.numero_documento,
        p.razon_social,
        p.domicilio_fiscal
    FROM $tabla_comprobante c
    INNER JOIN persona p ON c.idcliente = p.idpersona
    WHERE c.$campo_id = '$idcomprobante'
      AND c.idempresa = '{$_SESSION['idempresa']}'";

    try {
        $resultado_comp = ejecutarConsulta($sql_comprobante);

        if (!$resultado_comp || $resultado_comp->num_rows == 0) {
            echo json_encode(['error' => 'Comprobante no encontrado']);
            exit;
        }

        $comprobante = $resultado_comp->fetch_object();

        // Obtener items del comprobante
        $sql_items = "SELECT
            d.idarticulo,
            d.$col_cantidad AS cantidad,
            d.$col_precio AS precio_unitario,
            d.$col_valor_unitario AS valor_unitario,
            d.$col_afectacion_igv AS codigo_tipo_igv,
            a.codigo,
            a.nombre AS descripcion,
            a.unidad_medida,
            a.imagen
        FROM $tabla_detalle d
        INNER JOIN articulo a ON d.idarticulo = a.idarticulo
        WHERE d.$campo_id = '$idcomprobante'";

        $resultado_items = ejecutarConsulta($sql_items);

        $items = array();
        while ($item = $resultado_items->fetch_object()) {
            $items[] = array(
                'idarticulo' => $item->idarticulo,
                'codigo' => $item->codigo,
                'descripcion' => $item->descripcion,
                'unidad_medida' => $item->unidad_medida,
                'cantidad' => $item->cantidad,
                'precio_unitario' => $item->precio_unitario,
                'valor_unitario' => $item->valor_unitario,
                'codigo_tipo_igv' => $item->codigo_tipo_igv,
                'imagen' => $item->imagen
            );
        }

        // Preparar respuesta completa
        $response = array(
            'idcomprobante' => $comprobante->idcomprobante,
            'tipo_comprobante' => $tipo_comprobante,
            'serie' => $comprobante->serie,
            'numero' => $comprobante->numero,
            'fecha_emision' => $comprobante->fecha_emision,
            'total' => $comprobante->total_venta,
            'idcliente' => $comprobante->idcliente,
            'tipo_documento' => $comprobante->tipo_documento_cliente,
            'numero_documento' => $comprobante->numero_documento,
            'razon_social' => $comprobante->razon_social,
            'domicilio_fiscal' => $comprobante->domicilio_fiscal,
            'items' => $items
        );
    } catch (Exception $e) {
        error_log("Error en obtenerDatosComprobante: " . $e->getMessage());
        echo json_encode(['error' => 'Error al obtener datos del comprobante: ' . $e->getMessage()]);
        exit;
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// ============================================================================
// GUARDAR NOTA DE CRÉDITO
// ============================================================================
if (isset($_GET['op']) && $_GET['op'] == 'guardarNotaCredito') {
    // LOG INMEDIATO para verificar que llega aquí
    file_put_contents(__DIR__ . '/nc_debug.txt', date('Y-m-d H:i:s') . " - INICIO guardarNotaCredito\n", FILE_APPEND);

    // Habilitar reporte de errores para debugging
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    require_once "../modelos/NotaCredito.php";
    $notacredito = new NotaCredito();

    try {
        // Log para debug
        error_log("=== INICIO guardarNotaCredito ===");
        error_log("POST data: " . print_r($_POST, true));

        // Validar sesión
        if (!isset($_SESSION['idempresa']) || !isset($_SESSION['idusuario'])) {
            throw new Exception('Sesión inválida');
        }

        error_log("Sesión válida - idempresa: " . $_SESSION['idempresa'] . ", idusuario: " . $_SESSION['idusuario']);

        // Recibir datos del formulario (nombres según JavaScript)
        $idcomprobante_afectado = isset($_POST['idcomprobante_afectado']) ? limpiarCadena($_POST['idcomprobante_afectado']) : "";
        $tipo_comprobante_afectado = isset($_POST['tipo_comprobante_afectado']) ? limpiarCadena($_POST['tipo_comprobante_afectado']) : "";
        $motivo_nota = isset($_POST['motivo_nota']) ? limpiarCadena($_POST['motivo_nota']) : "";
        $codigo_motivo = isset($_POST['codigo_motivo']) ? limpiarCadena($_POST['codigo_motivo']) : "01";

        error_log("Datos básicos - Comprobante: $idcomprobante_afectado, Tipo: $tipo_comprobante_afectado");

        // Arrays de items (nombres según JavaScript)
        $idarticulo = isset($_POST['idarticulo']) ? $_POST['idarticulo'] : [];
        $cantidad = isset($_POST['cantidad_item_12']) ? $_POST['cantidad_item_12'] : [];
        $valor_unitario = isset($_POST['valor_uni_item_14']) ? $_POST['valor_uni_item_14'] : [];
        $precio_venta = isset($_POST['precio_venta_item_15_2']) ? $_POST['precio_venta_item_15_2'] : [];
        $afectacion_igv = isset($_POST['afectacion_igv_item_16_1']) ? $_POST['afectacion_igv_item_16_1'] : [];
        $valor_venta = isset($_POST['valor_venta_item_32']) ? $_POST['valor_venta_item_32'] : [];
        $igv_item = isset($_POST['igv_item']) ? $_POST['igv_item'] : [];
        $total_item = isset($_POST['total_item']) ? $_POST['total_item'] : [];
        $unidad_medida = isset($_POST['unidad_medida_item_13']) ? $_POST['unidad_medida_item_13'] : [];
        $codigo_producto = isset($_POST['codigo_producto']) ? $_POST['codigo_producto'] : [];
        $descripcion_item = isset($_POST['descripcion_item']) ? $_POST['descripcion_item'] : [];
        error_log("Arrays recibidos - Items: " . count($idarticulo));

        // Validar datos básicos
        if (empty($idcomprobante_afectado) || empty($tipo_comprobante_afectado)) {
            throw new Exception('Datos del comprobante afectado incompletos');
        }

        if (empty($idarticulo) || count($idarticulo) == 0) {
            throw new Exception('No hay items para registrar');
        }
        error_log("Validación inicial OK");

        // Obtener datos del comprobante afectado
        if ($tipo_comprobante_afectado == "01") {
            // Factura
            $sql_comp = "SELECT
                LEFT(numeracion_08, 4) AS serie,
                RIGHT(numeracion_08, 8) AS numero,
                fecha_emision_01 AS fecha,
                idcliente
            FROM factura
            WHERE idfactura = '$idcomprobante_afectado'
              AND idempresa = '{$_SESSION['idempresa']}'";
        } else {
            // Boleta
            $sql_comp = "SELECT
                LEFT(numeracion_07, 4) AS serie,
                RIGHT(numeracion_07, 8) AS numero,
                fecha_emision_01 AS fecha,
                idcliente
            FROM boleta
            WHERE idboleta = '$idcomprobante_afectado'
              AND idempresa = '{$_SESSION['idempresa']}'";
        }
        error_log("SQL comprobante: $sql_comp");

        $comp_data = ejecutarConsultaSimpleFila($sql_comp);
        error_log("Resultado consulta comprobante: " . print_r($comp_data, true));

        if (!$comp_data) {
            throw new Exception('Comprobante afectado no encontrado');
        }

        $serie_afectado = $comp_data['serie'];
        $numero_afectado = $comp_data['numero'];
        $fecha_afectado = $comp_data['fecha'];
        $idcliente = $comp_data['idcliente'];
        error_log("Datos comprobante extraídos - Serie: $serie_afectado, Numero: $numero_afectado, Cliente: $idcliente");

        // Generar numeración de NC
        $sql_ultima_nc = "SELECT MAX(CAST(RIGHT(numeroserienota, 8) AS UNSIGNED)) AS ultimo_numero
            FROM notacd
            WHERE idempresa = '{$_SESSION['idempresa']}'
              AND LEFT(numeroserienota, 4) = 'NC01'";

        $result_nc = ejecutarConsultaSimpleFila($sql_ultima_nc);
        $ultimo_numero = $result_nc && $result_nc['ultimo_numero'] ? intval($result_nc['ultimo_numero']) : 0;
        $nuevo_numero = $ultimo_numero + 1;
        $numeracion_nc = 'NC01-' . str_pad($nuevo_numero, 8, '0', STR_PAD_LEFT);

        // Fecha de emisión de la NC
        $fecha_emision_nc = date('Y-m-d');

        // Calcular totales a partir de los arrays recibidos
        $total_operaciones_gravadas = 0;
        $sumatoria_igv = 0;
        $importe_total = 0;

        foreach ($idarticulo as $index => $id_art) {
            $valor_venta_item = floatval($valor_venta[$index]);
            $igv_item_val = floatval($igv_item[$index]);
            $total_item_val = floatval($total_item[$index]);

            $total_operaciones_gravadas += $valor_venta_item;
            $sumatoria_igv += $igv_item_val;
            $importe_total += $total_item_val;
        }

        // Determinar si es factura o boleta
        $idfactura_afectada = ($tipo_comprobante_afectado == "01") ? $idcomprobante_afectado : null;
        $idboleta_afectada = ($tipo_comprobante_afectado == "03") ? $idcomprobante_afectado : null;
        error_log("Totales calculados - Op Grav: $total_operaciones_gravadas, IGV: $sumatoria_igv, Total: $importe_total");
        error_log("Numeración generada: $numeracion_nc");
        error_log("Llamando a NotaCredito->insertar()...");

        // Insertar Nota de Crédito
        $idnota_credito = $notacredito->insertar(
            $_SESSION['idempresa'],
            $idfactura_afectada,
            $idboleta_afectada,
            $tipo_comprobante_afectado,
            $numeracion_nc,
            $fecha_emision_nc,
            $motivo_nota,
            $codigo_motivo,
            $idcliente,
            $total_operaciones_gravadas,
            $sumatoria_igv,
            $importe_total,
            $serie_afectado,
            $numero_afectado,
            $fecha_afectado,
            $_SESSION['idusuario'],
            $idarticulo,
            $cantidad,
            $valor_unitario,
            $precio_venta,
            $afectacion_igv,
            $valor_venta,
            $igv_item,
            $total_item,
            $unidad_medida,
            $codigo_producto,
            $descripcion_item
        );

        if ($idnota_credito) {
            echo json_encode([
                'success' => true,
                'message' => 'Nota de Crédito registrada correctamente',
                'idnota_credito' => $idnota_credito,
                'numeracion' => $numeracion_nc
            ]);
        } else {
            throw new Exception('Error al insertar la Nota de Crédito en la base de datos');
        }
    } catch (Exception $e) {
        error_log("Error en guardarNotaCredito: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());

        // Mostrar error en pantalla para debugging
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
    } catch (Error $e) {
        // Capturar errores fatales de PHP 7+
        error_log("Error FATAL en guardarNotaCredito: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());

        echo json_encode([
            'success' => false,
            'message' => 'Error FATAL: ' . $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
    }
    exit;
}

// ============================================================================
// GUARDAR NOTA DE DÉBITO
// ============================================================================
if (isset($_GET['op']) && $_GET['op'] == 'guardarNotaDebito') {
    require_once "../modelos/NotaDebito.php";
    $notadebito = new NotaDebito();

    try {
        // Validar sesión
        if (!isset($_SESSION['idempresa']) || !isset($_SESSION['idusuario'])) {
            throw new Exception('Sesión inválida');
        }

        // Recibir datos del formulario (nombres según JavaScript)
        $idcomprobante_afectado = isset($_POST['idcomprobante_afectado']) ? limpiarCadena($_POST['idcomprobante_afectado']) : "";
        $tipo_comprobante_afectado = isset($_POST['tipo_comprobante_afectado']) ? limpiarCadena($_POST['tipo_comprobante_afectado']) : "";
        $motivo_nota = isset($_POST['motivo_nota']) ? limpiarCadena($_POST['motivo_nota']) : "";
        $codigo_motivo = isset($_POST['codigo_motivo']) ? limpiarCadena($_POST['codigo_motivo']) : "01";

        // Arrays de items (nombres según JavaScript)
        $idarticulo = isset($_POST['idarticulo']) ? $_POST['idarticulo'] : [];
        $cantidad = isset($_POST['cantidad_item_12']) ? $_POST['cantidad_item_12'] : [];
        $valor_unitario = isset($_POST['valor_uni_item_14']) ? $_POST['valor_uni_item_14'] : [];
        $precio_venta = isset($_POST['precio_venta_item_15_2']) ? $_POST['precio_venta_item_15_2'] : [];
        $afectacion_igv = isset($_POST['afectacion_igv_item_16_1']) ? $_POST['afectacion_igv_item_16_1'] : [];
        $valor_venta = isset($_POST['valor_venta_item_32']) ? $_POST['valor_venta_item_32'] : [];
        $igv_item = isset($_POST['igv_item']) ? $_POST['igv_item'] : [];
        $total_item = isset($_POST['total_item']) ? $_POST['total_item'] : [];
        $unidad_medida = isset($_POST['unidad_medida_item_13']) ? $_POST['unidad_medida_item_13'] : [];
        $codigo_producto = isset($_POST['codigo_producto']) ? $_POST['codigo_producto'] : [];
        $descripcion_item = isset($_POST['descripcion_item']) ? $_POST['descripcion_item'] : [];

        // Validar datos básicos
        if (empty($idcomprobante_afectado) || empty($tipo_comprobante_afectado)) {
            throw new Exception('Datos del comprobante afectado incompletos');
        }

        if (empty($idarticulo) || count($idarticulo) == 0) {
            throw new Exception('No hay items para registrar');
        }

        // Obtener datos del comprobante afectado
        if ($tipo_comprobante_afectado == "01") {
            // Factura
            $sql_comp = "SELECT
                LEFT(numeracion_08, 4) AS serie,
                RIGHT(numeracion_08, 8) AS numero,
                fecha_emision_01 AS fecha,
                idcliente
            FROM factura
            WHERE idfactura = '$idcomprobante_afectado'
              AND idempresa = '{$_SESSION['idempresa']}'";
        } else {
            // Boleta
            $sql_comp = "SELECT
                LEFT(numeracion_07, 4) AS serie,
                RIGHT(numeracion_07, 8) AS numero,
                fecha_emision_01 AS fecha,
                idcliente
            FROM boleta
            WHERE idboleta = '$idcomprobante_afectado'
              AND idempresa = '{$_SESSION['idempresa']}'";
        }

        $comp_data = ejecutarConsultaSimpleFila($sql_comp);

        if (!$comp_data) {
            throw new Exception('Comprobante afectado no encontrado');
        }

        $serie_afectado = $comp_data['serie'];
        $numero_afectado = $comp_data['numero'];
        $fecha_afectado = $comp_data['fecha'];
        $idcliente = $comp_data['idcliente'];

        // Generar numeración de ND
        $sql_ultima_nd = "SELECT MAX(CAST(RIGHT(numeracion_08, 8) AS UNSIGNED)) AS ultimo_numero
            FROM nota_debito
            WHERE idempresa = '{$_SESSION['idempresa']}'
              AND LEFT(numeracion_08, 4) = 'ND01'";

        $result_nd = ejecutarConsultaSimpleFila($sql_ultima_nd);
        $ultimo_numero = $result_nd && $result_nd['ultimo_numero'] ? intval($result_nd['ultimo_numero']) : 0;
        $nuevo_numero = $ultimo_numero + 1;
        $numeracion_nd = 'ND01-' . str_pad($nuevo_numero, 8, '0', STR_PAD_LEFT);

        // Fecha de emisión de la ND
        $fecha_emision_nd = date('Y-m-d');

        // Calcular totales a partir de los arrays recibidos
        $total_operaciones_gravadas = 0;
        $sumatoria_igv = 0;
        $importe_total = 0;

        foreach ($idarticulo as $index => $id_art) {
            $valor_venta_item = floatval($valor_venta[$index]);
            $igv_item_val = floatval($igv_item[$index]);
            $total_item_val = floatval($total_item[$index]);

            $total_operaciones_gravadas += $valor_venta_item;
            $sumatoria_igv += $igv_item_val;
            $importe_total += $total_item_val;
        }

        // Determinar si es factura o boleta
        $idfactura_afectada = ($tipo_comprobante_afectado == "01") ? $idcomprobante_afectado : null;
        $idboleta_afectada = ($tipo_comprobante_afectado == "03") ? $idcomprobante_afectado : null;

        // Insertar Nota de Débito
        $idnota_debito = $notadebito->insertar(
            $_SESSION['idempresa'],
            $idfactura_afectada,
            $idboleta_afectada,
            $tipo_comprobante_afectado,
            $numeracion_nd,
            $fecha_emision_nd,
            $motivo_nota,
            $codigo_motivo,
            $idcliente,
            $total_operaciones_gravadas,
            $sumatoria_igv,
            $importe_total,
            $serie_afectado,
            $numero_afectado,
            $fecha_afectado,
            $_SESSION['idusuario'],
            $idarticulo,
            $cantidad,
            $valor_unitario,
            $precio_venta,
            $afectacion_igv,
            $valor_venta,
            $igv_item,
            $total_item,
            $unidad_medida,
            $codigo_producto,
            $descripcion_item
        );

        if ($idnota_debito) {
            echo json_encode([
                'success' => true,
                'message' => 'Nota de Débito registrada correctamente',
                'idnota_debito' => $idnota_debito,
                'numeracion' => $numeracion_nd
            ]);
        } else {
            throw new Exception('Error al insertar la Nota de Débito en la base de datos');
        }
    } catch (Exception $e) {
        error_log("Error en guardarNotaDebito: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
    exit;
}

?>