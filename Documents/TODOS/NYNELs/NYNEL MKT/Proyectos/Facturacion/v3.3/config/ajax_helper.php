<?php
/**
 * HELPER PARA ENDPOINTS AJAX
 * Funciones auxiliares para validación y seguridad en controladores AJAX
 */

require_once __DIR__ . "/Conexion.php";

/**
 * Valida token CSRF en request AJAX
 * Llama a esta función al inicio de operaciones POST críticas
 * @param string $redirect_url URL a donde redirigir si falla (opcional)
 * @return bool True si el token es válido, false si no (o redirect si se especificó)
 */
function validarCSRFAjax($redirect_url = null) {
    $csrf_token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';

    if (!validarTokenCSRF($csrf_token)) {
        error_log("CSRF Attack detectado en AJAX - IP: " . $_SERVER['REMOTE_ADDR'] . " - URL: " . $_SERVER['REQUEST_URI']);

        if ($redirect_url) {
            header("Location: $redirect_url?error=csrf");
            exit();
        }

        return false;
    }

    return true;
}

/**
 * Valida y sanitiza inputs de artículo
 * @param array $data Array con datos del POST
 * @return array|false Array con datos validados o false si hay errores
 */
function validarInputsArticulo($data) {
    $errores = [];
    $validado = [];

    // IDs (opcional para nuevo artículo)
    if (isset($data['idarticulo']) && !empty($data['idarticulo'])) {
        $validado['idarticulo'] = validarEntero($data['idarticulo'], 1);
        if ($validado['idarticulo'] === false) {
            $errores[] = "ID de artículo inválido";
        }
    }

    // ID Familia (requerido)
    $validado['idfamilia'] = validarEntero($data['idfamilia'] ?? 0, 1);
    if ($validado['idfamilia'] === false) {
        $errores[] = "ID de familia inválido";
    }

    // ID Almacén (requerido)
    $validado['idalmacen'] = validarEntero($data['idalmacen'] ?? 0, 1);
    if ($validado['idalmacen'] === false) {
        $errores[] = "ID de almacén inválido";
    }

    // Códigos y strings
    $validado['codigo'] = sanitizarString($data['codigo'] ?? '', 50);
    $validado['codigo_proveedor'] = sanitizarString($data['codigo_proveedor'] ?? '', 50);
    $validado['nombre'] = sanitizarString($data['nombre'] ?? '', 200);

    if (empty($validado['nombre'])) {
        $errores[] = "Nombre del artículo es requerido";
    }

    // Unidad de medida (whitelist)
    $unidades_permitidas = ['NIU', 'ZZ', 'KGM', 'LTR', 'MTR', 'SET', 'UND', 'DZN', 'GRM', 'MLT', 'CMT', 'BX', 'PK', 'CA'];
    $validado['unidad_medida'] = validarWhitelist($data['unidad_medida'] ?? '', $unidades_permitidas);
    if ($validado['unidad_medida'] === false) {
        $validado['unidad_medida'] = 'NIU'; // Default
    }

    // Precios y valores (decimales positivos)
    $validado['costo_compra'] = validarDecimal($data['costo_compra'] ?? 0, 0);
    $validado['valor_venta'] = validarDecimal($data['valor_venta'] ?? 0, 0);
    $validado['precio2'] = validarDecimal($data['precio2'] ?? 0, 0);
    $validado['precio3'] = validarDecimal($data['precio3'] ?? 0, 0);

    if ($validado['costo_compra'] === false) $validado['costo_compra'] = 0;
    if ($validado['valor_venta'] === false) $validado['valor_venta'] = 0;
    if ($validado['precio2'] === false) $validado['precio2'] = 0;
    if ($validado['precio3'] === false) $validado['precio3'] = 0;

    // Stock y cantidades (enteros positivos o cero)
    $validado['stock'] = validarDecimal($data['stock'] ?? 0, 0);
    $validado['saldo_iniu'] = validarDecimal($data['saldo_iniu'] ?? 0, 0);
    $validado['saldo_finu'] = validarDecimal($data['saldo_finu'] ?? 0, 0);
    $validado['limitestock'] = validarDecimal($data['limitestock'] ?? 0, 0);

    if ($validado['stock'] === false) $validado['stock'] = 0;
    if ($validado['saldo_iniu'] === false) $validado['saldo_iniu'] = 0;
    if ($validado['saldo_finu'] === false) $validado['saldo_finu'] = 0;
    if ($validado['limitestock'] === false) $validado['limitestock'] = 0;

    // Fechas (opcional)
    if (!empty($data['fechafabricacion'])) {
        $validado['fechafabricacion'] = validarFecha($data['fechafabricacion']);
        if ($validado['fechafabricacion'] === false) {
            $validado['fechafabricacion'] = 'NULL';
        }
    } else {
        $validado['fechafabricacion'] = 'NULL';
    }

    if (!empty($data['fechavencimiento'])) {
        $validado['fechavencimiento'] = validarFecha($data['fechavencimiento']);
        if ($validado['fechavencimiento'] === false) {
            $validado['fechavencimiento'] = 'NULL';
        }
    } else {
        $validado['fechavencimiento'] = 'NULL';
    }

    // Tipo de item (whitelist)
    $tipos_permitidos = ['P', 'S']; // P=Producto, S=Servicio
    $validado['tipoitem'] = validarWhitelist($data['tipoitem'] ?? 'P', $tipos_permitidos);
    if ($validado['tipoitem'] === false) {
        $validado['tipoitem'] = 'P';
    }

    // Otros campos opcionales
    $validado['descripcion'] = sanitizarString($data['descripcion'] ?? '', 500);
    $validado['marca'] = sanitizarString($data['marca'] ?? '', 100);
    $validado['lote'] = sanitizarString($data['lote'] ?? '', 50);
    $validado['procedencia'] = sanitizarString($data['procedencia'] ?? '', 100);
    $validado['fabricante'] = sanitizarString($data['fabricante'] ?? '', 100);
    $validado['registrosanitario'] = sanitizarString($data['registrosanitario'] ?? '', 50);

    // Si hay errores críticos, retornar false
    if (!empty($errores)) {
        error_log("Errores de validación en artículo: " . implode(", ", $errores));
        return false;
    }

    return $validado;
}

/**
 * Valida y sanitiza inputs de persona (cliente/proveedor)
 * @param array $data Array con datos del POST
 * @return array|false Array con datos validados o false si hay errores
 */
function validarInputsPersona($data) {
    $errores = [];
    $validado = [];

    // Tipo de persona (whitelist)
    $tipos_permitidos = ['Cliente', 'Proveedor'];
    $validado['tipo_persona'] = validarWhitelist($data['tipo_persona'] ?? '', $tipos_permitidos);
    if ($validado['tipo_persona'] === false) {
        $errores[] = "Tipo de persona inválido";
    }

    // Nombre/Razón social (requerido)
    $validado['nombre'] = sanitizarString($data['nombre'] ?? '', 200);
    if (empty($validado['nombre'])) {
        $errores[] = "Nombre es requerido";
    }

    // Tipo de documento (whitelist)
    $docs_permitidos = ['DNI', 'RUC', 'CE', 'PASAPORTE', 'OTRO'];
    $validado['tipo_documento'] = validarWhitelist($data['tipo_documento'] ?? '', $docs_permitidos);
    if ($validado['tipo_documento'] === false) {
        $validado['tipo_documento'] = 'DNI';
    }

    // Número de documento
    $num_doc = $data['num_documento'] ?? '';

    if ($validado['tipo_documento'] === 'DNI') {
        $validado['num_documento'] = validarDNI($num_doc);
        if ($validado['num_documento'] === false) {
            $errores[] = "DNI inválido (debe tener 8 dígitos)";
        }
    } elseif ($validado['tipo_documento'] === 'RUC') {
        $validado['num_documento'] = validarRUC($num_doc);
        if ($validado['num_documento'] === false) {
            $errores[] = "RUC inválido (debe tener 11 dígitos)";
        }
    } else {
        $validado['num_documento'] = sanitizarString($num_doc, 20);
    }

    // Email (opcional pero debe ser válido si se proporciona)
    if (!empty($data['email'])) {
        $validado['email'] = validarEmail($data['email']);
        if ($validado['email'] === false) {
            $errores[] = "Email inválido";
        }
    } else {
        $validado['email'] = '';
    }

    // Teléfono
    $validado['telefono'] = sanitizarString($data['telefono'] ?? '', 20);

    // Dirección
    $validado['direccion'] = sanitizarString($data['direccion'] ?? '', 200);

    if (!empty($errores)) {
        error_log("Errores de validación en persona: " . implode(", ", $errores));
        return false;
    }

    return $validado;
}

/**
 * Valida subida de imagen
 * @param array $file Array $_FILES['campo']
 * @param array $extensiones_permitidas Extensiones permitidas (default: jpg, jpeg, png)
 * @param int $tamano_max Tamaño máximo en bytes (default: 5MB)
 * @return array|false Info del archivo validado o false si es inválido
 */
function validarImagenSubida($file, $extensiones_permitidas = ['jpg', 'jpeg', 'png'], $tamano_max = 5242880) {
    // Verificar si hay archivo
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return false;
    }

    // Verificar errores de subida
    if ($file['error'] !== UPLOAD_ERR_OK) {
        error_log("Error al subir archivo: " . $file['error']);
        return false;
    }

    // Validar tamaño
    if ($file['size'] > $tamano_max) {
        error_log("Archivo muy grande: " . $file['size'] . " bytes (máximo: $tamano_max bytes)");
        return false;
    }

    // Validar tipo MIME
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $mimes_permitidos = [
        'image/jpeg' => 'jpg',
        'image/jpg' => 'jpg',
        'image/png' => 'png'
    ];

    if (!isset($mimes_permitidos[$mime_type])) {
        error_log("Tipo MIME no permitido: $mime_type");
        return false;
    }

    // Validar extensión
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $extensiones_permitidas)) {
        error_log("Extensión no permitida: $ext");
        return false;
    }

    // Generar nombre seguro
    $nombre_seguro = uniqid() . '_' . time() . '.' . $ext;

    return [
        'nombre_original' => $file['name'],
        'nombre_seguro' => $nombre_seguro,
        'extension' => $ext,
        'mime_type' => $mime_type,
        'tamano' => $file['size'],
        'tmp_name' => $file['tmp_name']
    ];
}

/**
 * Valida operación requiere autenticación
 * @param string $redirect_url URL a donde redirigir si no está autenticado
 * @return bool True si está autenticado
 */
function validarAutenticacion($redirect_url = '../vistas/login.php') {
    if (!isset($_SESSION['idusuario'])) {
        error_log("Intento de acceso no autenticado - IP: " . $_SERVER['REMOTE_ADDR'] . " - URL: " . $_SERVER['REQUEST_URI']);
        header("Location: $redirect_url");
        exit();
    }
    return true;
}

/**
 * Valida permiso de usuario para operación
 * @param string $permiso Nombre del permiso requerido (Dashboard, Ventas, etc.)
 * @return bool True si tiene permiso
 */
function validarPermiso($permiso) {
    if (!isset($_SESSION[$permiso]) || $_SESSION[$permiso] != 1) {
        error_log("Acceso denegado - Usuario: " . ($_SESSION['nombre'] ?? 'Desconocido') . " - Permiso requerido: $permiso");
        return false;
    }
    return true;
}

/**
 * Respuesta JSON estandarizada para AJAX
 * @param bool $exito Si la operación fue exitosa
 * @param string $mensaje Mensaje descriptivo
 * @param array $datos Datos adicionales (opcional)
 */
function respuestaJSON($exito, $mensaje, $datos = []) {
    header('Content-Type: application/json');
    echo json_encode([
        'exito' => $exito,
        'mensaje' => $mensaje,
        'datos' => $datos,
        'timestamp' => time()
    ]);
    exit();
}

/**
 * Valida Rate Limiting en endpoints AJAX
 * Previene flooding y ataques DoS
 * @param string $contexto Contexto de la operación (ej: "factura", "boleta", "compra")
 * @param int $maxRequests Número máximo de requests permitidos (default: 100)
 * @param int $windowSeconds Ventana de tiempo en segundos (default: 60)
 * @return bool True si está dentro del límite, False si se excedió (y retorna JSON de error)
 */
function rateLimitAjax($contexto = 'api', $maxRequests = 100, $windowSeconds = 60) {
    // Generar identificador único basado en IP + contexto
    $rate_limit_id = generarIdentificadorRateLimit($contexto);

    // Verificar límite
    $rate_limit = rateLimitCheck($rate_limit_id, $maxRequests, $windowSeconds);

    if (!$rate_limit['permitido']) {
        $segundos_espera = $rate_limit['tiempo_espera'];

        error_log("RATE LIMIT EXCEDIDO en AJAX - Contexto: $contexto - IP: " . $_SERVER['REMOTE_ADDR'] .
                  " - Intentos: " . $rate_limit['intentos'] .
                  " - Tiempo de espera: " . $segundos_espera . "s");

        // Retornar error JSON y terminar ejecución
        respuestaJSON(false,
            "Demasiadas solicitudes. Has excedido el límite de {$rate_limit['max_intentos']} requests en $windowSeconds segundos. " .
            "Por favor, espera $segundos_espera segundos antes de intentar nuevamente.",
            [
                'rate_limit_excedido' => true,
                'intentos' => $rate_limit['intentos'],
                'max_intentos' => $rate_limit['max_intentos'],
                'tiempo_espera_segundos' => $segundos_espera
            ]
        );

        return false; // Nunca se ejecuta debido a exit() en respuestaJSON, pero por claridad
    }

    return true;
}
?>
