<?php
/**
 * SISTEMA DE RESPUESTAS API ESTANDARIZADAS
 * Proporciona formato consistente para todas las respuestas AJAX/API
 *
 * CARACTERÍSTICAS:
 * - Formato JSON consistente para éxito y error
 * - Códigos HTTP apropiados
 * - Mensajes descriptivos en español
 * - Datos adicionales estructurados
 * - Soporte para paginación
 * - Soporte para metadatos
 * - Logging automático de errores
 * - Headers CORS configurables
 *
 * @version 1.0
 * @author Sistema Facturación v3.3
 */

class ApiResponse {
    /**
     * @var int Código HTTP de respuesta
     */
    private $httpCode = 200;

    /**
     * @var bool Indica si la operación fue exitosa
     */
    private $success = true;

    /**
     * @var string Mensaje principal
     */
    private $message = '';

    /**
     * @var mixed Datos de respuesta
     */
    private $data = null;

    /**
     * @var array Errores de validación o lógica
     */
    private $errors = [];

    /**
     * @var array Metadatos adicionales
     */
    private $meta = [];

    /**
     * @var bool Indica si ya se envió la respuesta
     */
    private static $sent = false;

    /**
     * Constructor privado
     */
    private function __construct() {}

    /**
     * Crear respuesta exitosa
     *
     * @param mixed $data Datos a retornar
     * @param string $message Mensaje opcional
     * @param int $httpCode Código HTTP (default: 200)
     * @return ApiResponse
     */
    public static function success($data = null, $message = 'Operación exitosa', $httpCode = 200) {
        $response = new self();
        $response->success = true;
        $response->data = $data;
        $response->message = $message;
        $response->httpCode = $httpCode;

        return $response;
    }

    /**
     * Crear respuesta de error
     *
     * @param string $message Mensaje de error
     * @param array $errors Errores detallados
     * @param int $httpCode Código HTTP (default: 400)
     * @return ApiResponse
     */
    public static function error($message = 'Error en la operación', $errors = [], $httpCode = 400) {
        $response = new self();
        $response->success = false;
        $response->message = $message;
        $response->errors = $errors;
        $response->httpCode = $httpCode;

        // Log automático de errores
        error_log("API ERROR [{$httpCode}]: {$message} | " . json_encode($errors));

        return $response;
    }

    /**
     * Respuesta de validación fallida
     *
     * @param array $validationErrors Errores de validación
     * @param string $message Mensaje opcional
     * @return ApiResponse
     */
    public static function validationError($validationErrors, $message = 'Error de validación') {
        return self::error($message, $validationErrors, 422);
    }

    /**
     * Respuesta de no autorizado
     *
     * @param string $message Mensaje opcional
     * @return ApiResponse
     */
    public static function unauthorized($message = 'No autorizado') {
        return self::error($message, [], 401);
    }

    /**
     * Respuesta de prohibido
     *
     * @param string $message Mensaje opcional
     * @return ApiResponse
     */
    public static function forbidden($message = 'Acceso prohibido') {
        return self::error($message, [], 403);
    }

    /**
     * Respuesta de no encontrado
     *
     * @param string $message Mensaje opcional
     * @return ApiResponse
     */
    public static function notFound($message = 'Recurso no encontrado') {
        return self::error($message, [], 404);
    }

    /**
     * Respuesta de conflicto
     *
     * @param string $message Mensaje opcional
     * @return ApiResponse
     */
    public static function conflict($message = 'Conflicto con el estado actual') {
        return self::error($message, [], 409);
    }

    /**
     * Respuesta de error interno del servidor
     *
     * @param string $message Mensaje opcional
     * @param Exception $exception Excepción opcional
     * @return ApiResponse
     */
    public static function serverError($message = 'Error interno del servidor', $exception = null) {
        $errors = [];

        if ($exception) {
            error_log("SERVER ERROR EXCEPTION: " . $exception->getMessage() . "\n" . $exception->getTraceAsString());

            // Solo incluir detalles de excepción en desarrollo
            if (defined('APP_ENV') && APP_ENV === 'development') {
                $errors = [
                    'exception' => get_class($exception),
                    'message' => $exception->getMessage(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine()
                ];
            }
        }

        return self::error($message, $errors, 500);
    }

    /**
     * Respuesta de recurso creado
     *
     * @param mixed $data Datos del recurso creado
     * @param string $message Mensaje opcional
     * @return ApiResponse
     */
    public static function created($data = null, $message = 'Recurso creado exitosamente') {
        return self::success($data, $message, 201);
    }

    /**
     * Respuesta de recurso actualizado
     *
     * @param mixed $data Datos actualizados
     * @param string $message Mensaje opcional
     * @return ApiResponse
     */
    public static function updated($data = null, $message = 'Recurso actualizado exitosamente') {
        return self::success($data, $message, 200);
    }

    /**
     * Respuesta de recurso eliminado
     *
     * @param string $message Mensaje opcional
     * @return ApiResponse
     */
    public static function deleted($message = 'Recurso eliminado exitosamente') {
        return self::success(null, $message, 200);
    }

    /**
     * Respuesta sin contenido
     *
     * @return ApiResponse
     */
    public static function noContent() {
        $response = new self();
        $response->httpCode = 204;
        return $response;
    }

    /**
     * Respuesta con paginación
     *
     * @param array $items Items de la página actual
     * @param int $total Total de items
     * @param int $page Página actual
     * @param int $perPage Items por página
     * @param string $message Mensaje opcional
     * @return ApiResponse
     */
    public static function paginated($items, $total, $page, $perPage, $message = 'Datos obtenidos exitosamente') {
        $totalPages = ceil($total / $perPage);

        $response = self::success($items, $message);
        $response->meta([
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'total_pages' => $totalPages,
                'from' => (($page - 1) * $perPage) + 1,
                'to' => min($page * $perPage, $total),
                'has_more' => $page < $totalPages
            ]
        ]);

        return $response;
    }

    // ============================================
    // MÉTODOS FLUENT PARA CONSTRUCCIÓN
    // ============================================

    /**
     * Agregar metadatos
     *
     * @param array $meta Metadatos
     * @return $this
     */
    public function meta($meta) {
        $this->meta = array_merge($this->meta, $meta);
        return $this;
    }

    /**
     * Agregar datos adicionales
     *
     * @param mixed $data Datos
     * @return $this
     */
    public function withData($data) {
        $this->data = $data;
        return $this;
    }

    /**
     * Agregar mensaje
     *
     * @param string $message Mensaje
     * @return $this
     */
    public function withMessage($message) {
        $this->message = $message;
        return $this;
    }

    /**
     * Agregar errores
     *
     * @param array $errors Errores
     * @return $this
     */
    public function withErrors($errors) {
        $this->errors = array_merge($this->errors, $errors);
        return $this;
    }

    /**
     * Establecer código HTTP
     *
     * @param int $code Código HTTP
     * @return $this
     */
    public function httpCode($code) {
        $this->httpCode = $code;
        return $this;
    }

    // ============================================
    // MÉTODOS DE ENVÍO
    // ============================================

    /**
     * Construir array de respuesta
     *
     * @return array Respuesta estructurada
     */
    public function toArray() {
        $response = [
            'success' => $this->success,
            'message' => $this->message
        ];

        // Agregar datos solo si existen
        if ($this->data !== null) {
            $response['data'] = $this->data;
        }

        // Agregar errores solo si existen
        if (!empty($this->errors)) {
            $response['errors'] = $this->errors;
        }

        // Agregar meta solo si existe
        if (!empty($this->meta)) {
            $response['meta'] = $this->meta;
        }

        // Agregar timestamp
        $response['timestamp'] = date('Y-m-d H:i:s');

        return $response;
    }

    /**
     * Convertir a JSON
     *
     * @return string JSON string
     */
    public function toJson() {
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Enviar respuesta JSON y terminar ejecución
     *
     * @param bool $exit Terminar ejecución (default: true)
     * @return void
     */
    public function send($exit = true) {
        // Prevenir envío duplicado
        if (self::$sent) {
            return;
        }

        // Establecer headers
        $this->setHeaders();

        // Establecer código HTTP
        http_response_code($this->httpCode);

        // Enviar JSON
        echo $this->toJson();

        // Marcar como enviado
        self::$sent = true;

        // Terminar ejecución si se solicita
        if ($exit) {
            exit;
        }
    }

    /**
     * Establecer headers HTTP
     */
    private function setHeaders() {
        // Limpiar cualquier output previo
        if (ob_get_level()) {
            ob_clean();
        }

        // Headers básicos
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');

        // CORS (configurar según necesidad)
        if (defined('API_CORS_ENABLED') && API_CORS_ENABLED) {
            $allowedOrigin = defined('API_CORS_ORIGIN') ? API_CORS_ORIGIN : '*';
            header("Access-Control-Allow-Origin: {$allowedOrigin}");
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
            header('Access-Control-Allow-Credentials: true');
        }

        // Cache control
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    }

    // ============================================
    // MÉTODOS DE UTILIDAD
    // ============================================

    /**
     * Verificar si es una petición AJAX
     *
     * @return bool true si es AJAX
     */
    public static function isAjax() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Verificar método HTTP
     *
     * @param string $method Método esperado (GET, POST, PUT, DELETE)
     * @return bool true si coincide
     */
    public static function isMethod($method) {
        return strtoupper($_SERVER['REQUEST_METHOD']) === strtoupper($method);
    }

    /**
     * Obtener método HTTP actual
     *
     * @return string Método HTTP
     */
    public static function getMethod() {
        return strtoupper($_SERVER['REQUEST_METHOD']);
    }

    /**
     * Validar que sea petición AJAX y método correcto
     *
     * @param string $method Método esperado
     * @return ApiResponse|null null si válido, ApiResponse de error si inválido
     */
    public static function validateRequest($method = null) {
        if (!self::isAjax()) {
            return self::error('Solo se permiten peticiones AJAX', [], 400);
        }

        if ($method !== null && !self::isMethod($method)) {
            return self::error("Método HTTP inválido. Se esperaba {$method}", [], 405);
        }

        return null;
    }

    /**
     * Manejar excepciones y convertir a respuesta de error
     *
     * @param Exception $e Excepción
     * @return ApiResponse Respuesta de error
     */
    public static function fromException($e) {
        // Determinar código HTTP desde la excepción si está disponible
        $httpCode = 500;
        if (method_exists($e, 'getStatusCode')) {
            $httpCode = $e->getStatusCode();
        }

        return self::serverError($e->getMessage(), $e)->httpCode($httpCode);
    }

    /**
     * Crear respuesta desde Validator
     *
     * @param Validator $validator Instancia de Validator
     * @return ApiResponse Respuesta de error de validación
     */
    public static function fromValidator($validator) {
        if ($validator->passes()) {
            return self::success(null, 'Validación exitosa');
        }

        return self::validationError($validator->errors(), 'Los datos proporcionados no son válidos');
    }
}

/**
 * FUNCIONES HELPER GLOBALES
 */

/**
 * Respuesta de éxito (helper global)
 */
function api_success($data = null, $message = 'Operación exitosa', $httpCode = 200) {
    return ApiResponse::success($data, $message, $httpCode);
}

/**
 * Respuesta de error (helper global)
 */
function api_error($message = 'Error en la operación', $errors = [], $httpCode = 400) {
    return ApiResponse::error($message, $errors, $httpCode);
}

/**
 * Respuesta de validación fallida (helper global)
 */
function api_validation_error($validationErrors, $message = 'Error de validación') {
    return ApiResponse::validationError($validationErrors, $message);
}

/**
 * Respuesta de recurso creado (helper global)
 */
function api_created($data = null, $message = 'Recurso creado exitosamente') {
    return ApiResponse::created($data, $message);
}

/**
 * Respuesta de recurso actualizado (helper global)
 */
function api_updated($data = null, $message = 'Recurso actualizado exitosamente') {
    return ApiResponse::updated($data, $message);
}

/**
 * Respuesta de recurso eliminado (helper global)
 */
function api_deleted($message = 'Recurso eliminado exitosamente') {
    return ApiResponse::deleted($message);
}

/**
 * Respuesta de no autorizado (helper global)
 */
function api_unauthorized($message = 'No autorizado') {
    return ApiResponse::unauthorized($message);
}

/**
 * Respuesta de no encontrado (helper global)
 */
function api_not_found($message = 'Recurso no encontrado') {
    return ApiResponse::notFound($message);
}

/**
 * Respuesta de error interno (helper global)
 */
function api_server_error($message = 'Error interno del servidor', $exception = null) {
    return ApiResponse::serverError($message, $exception);
}
