<?php
/**
 * HELPERS DE SEGURIDAD CONSOLIDADOS
 * Centraliza funciones de seguridad para protección contra amenazas comunes
 *
 * CARACTERÍSTICAS:
 * - Protección CSRF (Cross-Site Request Forgery)
 * - Protección XSS (Cross-Site Scripting)
 * - Sanitización de entradas
 * - Encriptación de datos sensibles
 * - Rate limiting
 * - IP whitelisting/blacklisting
 * - Password hashing seguro
 * - Generación de tokens seguros
 * - Headers de seguridad HTTP
 *
 * @version 1.0
 * @author Sistema Facturación v3.3
 */

class SecurityHelper {
    /**
     * @var string Nombre de sesión para token CSRF
     */
    const CSRF_TOKEN_NAME = 'csrf_token';

    /**
     * @var string Nombre de header para token CSRF
     */
    const CSRF_HEADER_NAME = 'X-CSRF-Token';

    /**
     * @var int Tiempo de vida del token CSRF en segundos (2 horas)
     */
    const CSRF_TOKEN_LIFETIME = 7200;

    /**
     * @var string Clave de encriptación (debe estar en config)
     */
    private static $encryptionKey = null;

    /**
     * @var string Método de encriptación
     */
    const ENCRYPTION_METHOD = 'AES-256-CBC';

    /**
     * @var array Rate limit storage (en producción usar Redis/Memcached)
     */
    private static $rateLimitStore = [];

    // ============================================
    // PROTECCIÓN CSRF
    // ============================================

    /**
     * Generar token CSRF
     *
     * @return string Token generado
     */
    public static function generateCsrfToken() {
        // Asegurar que la sesión esté iniciada
        self::ensureSession();

        // Generar token seguro
        $token = bin2hex(random_bytes(32));

        // Almacenar en sesión con timestamp
        $_SESSION[self::CSRF_TOKEN_NAME] = [
            'token' => $token,
            'timestamp' => time()
        ];

        return $token;
    }

    /**
     * Obtener token CSRF actual (o generar uno nuevo)
     *
     * @return string Token CSRF
     */
    public static function getCsrfToken() {
        self::ensureSession();

        // Verificar si existe token válido
        if (isset($_SESSION[self::CSRF_TOKEN_NAME])) {
            $tokenData = $_SESSION[self::CSRF_TOKEN_NAME];
            $age = time() - $tokenData['timestamp'];

            // Si no ha expirado, retornar token existente
            if ($age < self::CSRF_TOKEN_LIFETIME) {
                return $tokenData['token'];
            }
        }

        // Generar nuevo token
        return self::generateCsrfToken();
    }

    /**
     * Validar token CSRF
     *
     * @param string $token Token a validar (opcional, se obtiene auto)
     * @return bool true si es válido
     */
    public static function validateCsrfToken($token = null) {
        self::ensureSession();

        // Obtener token de POST, GET o header si no se proporciona
        if ($token === null) {
            $token = $_POST[self::CSRF_TOKEN_NAME] ??
                     $_GET[self::CSRF_TOKEN_NAME] ??
                     self::getHeaderValue(self::CSRF_HEADER_NAME) ??
                     '';
        }

        // Verificar que existe token en sesión
        if (!isset($_SESSION[self::CSRF_TOKEN_NAME])) {
            return false;
        }

        $sessionData = $_SESSION[self::CSRF_TOKEN_NAME];

        // Verificar expiración
        $age = time() - $sessionData['timestamp'];
        if ($age >= self::CSRF_TOKEN_LIFETIME) {
            return false;
        }

        // Comparación segura contra timing attacks
        return hash_equals($sessionData['token'], $token);
    }

    /**
     * Validar CSRF y enviar respuesta de error si falla
     *
     * @param string $token Token opcional
     * @return bool true si válido, false y envía respuesta si inválido
     */
    public static function validateCsrfOrDie($token = null) {
        if (!self::validateCsrfToken($token)) {
            if (class_exists('ApiResponse')) {
                ApiResponse::error('Token de seguridad inválido o expirado', [], 403)->send();
            } else {
                http_response_code(403);
                die(json_encode(['error' => 'Token de seguridad inválido']));
            }
            return false;
        }

        return true;
    }

    /**
     * Renderizar input hidden con token CSRF
     *
     * @return string HTML del input
     */
    public static function csrfField() {
        $token = self::getCsrfToken();
        return '<input type="hidden" name="' . self::CSRF_TOKEN_NAME . '" value="' . $token . '">';
    }

    /**
     * Obtener token CSRF para JavaScript
     *
     * @return string JavaScript snippet
     */
    public static function csrfMeta() {
        $token = self::getCsrfToken();
        return '<meta name="csrf-token" content="' . $token . '">';
    }

    // ============================================
    // PROTECCIÓN XSS
    // ============================================

    /**
     * Escapar output para HTML (protección XSS básica)
     *
     * @param string $value Valor a escapar
     * @return string Valor escapado
     */
    public static function escapeHtml($value) {
        if (is_array($value)) {
            return array_map([self::class, 'escapeHtml'], $value);
        }

        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Escapar para atributos HTML
     *
     * @param string $value Valor
     * @return string Valor escapado
     */
    public static function escapeAttr($value) {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Escapar para JavaScript
     *
     * @param string $value Valor
     * @return string Valor escapado
     */
    public static function escapeJs($value) {
        return json_encode($value, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }

    /**
     * Limpiar HTML manteniendo tags permitidos
     *
     * @param string $html HTML a limpiar
     * @param array $allowedTags Tags permitidos
     * @return string HTML limpio
     */
    public static function cleanHtml($html, $allowedTags = ['p', 'br', 'strong', 'em', 'ul', 'ol', 'li']) {
        $allowed = '<' . implode('><', $allowedTags) . '>';
        return strip_tags($html, $allowed);
    }

    // ============================================
    // SANITIZACIÓN
    // ============================================

    /**
     * Sanitizar string general
     *
     * @param string $value Valor
     * @return string Valor sanitizado
     */
    public static function sanitize($value) {
        if (is_array($value)) {
            return array_map([self::class, 'sanitize'], $value);
        }

        $value = trim($value);
        $value = strip_tags($value);
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

        return $value;
    }

    /**
     * Sanitizar email
     *
     * @param string $email Email
     * @return string Email sanitizado
     */
    public static function sanitizeEmail($email) {
        return filter_var($email, FILTER_SANITIZE_EMAIL);
    }

    /**
     * Sanitizar URL
     *
     * @param string $url URL
     * @return string URL sanitizada
     */
    public static function sanitizeUrl($url) {
        return filter_var($url, FILTER_SANITIZE_URL);
    }

    /**
     * Sanitizar número (solo dígitos y signo)
     *
     * @param string $number Número
     * @return string Número sanitizado
     */
    public static function sanitizeNumber($number) {
        return preg_replace('/[^0-9.-]/', '', $number);
    }

    /**
     * Sanitizar nombre de archivo
     *
     * @param string $filename Nombre de archivo
     * @return string Nombre sanitizado
     */
    public static function sanitizeFilename($filename) {
        // Eliminar caracteres peligrosos
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);

        // Prevenir directory traversal
        $filename = str_replace(['..', '/', '\\'], '', $filename);

        return $filename;
    }

    /**
     * Sanitizar path (prevenir directory traversal)
     *
     * @param string $path Path
     * @return string Path sanitizado
     */
    public static function sanitizePath($path) {
        // Eliminar .. y caracteres peligrosos
        $path = str_replace(['..', "\0"], '', $path);

        // Normalizar separadores
        $path = str_replace('\\', '/', $path);

        // Eliminar múltiples /
        $path = preg_replace('/\/+/', '/', $path);

        return $path;
    }

    // ============================================
    // ENCRIPTACIÓN
    // ============================================

    /**
     * Establecer clave de encriptación
     *
     * @param string $key Clave
     */
    public static function setEncryptionKey($key) {
        self::$encryptionKey = $key;
    }

    /**
     * Obtener clave de encriptación
     *
     * @return string Clave
     */
    private static function getEncryptionKey() {
        if (self::$encryptionKey === null) {
            // Intentar obtener de configuración
            if (defined('ENCRYPTION_KEY')) {
                self::$encryptionKey = ENCRYPTION_KEY;
            } else {
                // Generar clave temporal (INSEGURO, solo para desarrollo)
                self::$encryptionKey = 'DEFAULT_INSECURE_KEY_CHANGE_IN_PRODUCTION';
                trigger_error('ENCRYPTION_KEY no definida. Usando clave por defecto INSEGURA.', E_USER_WARNING);
            }
        }

        return self::$encryptionKey;
    }

    /**
     * Encriptar datos
     *
     * @param string $data Datos a encriptar
     * @return string Datos encriptados (base64)
     */
    public static function encrypt($data) {
        $key = self::getEncryptionKey();
        $iv = random_bytes(openssl_cipher_iv_length(self::ENCRYPTION_METHOD));

        $encrypted = openssl_encrypt($data, self::ENCRYPTION_METHOD, $key, 0, $iv);

        // Concatenar IV y datos encriptados
        return base64_encode($iv . $encrypted);
    }

    /**
     * Desencriptar datos
     *
     * @param string $encryptedData Datos encriptados (base64)
     * @return string|false Datos desencriptados o false si falla
     */
    public static function decrypt($encryptedData) {
        $key = self::getEncryptionKey();
        $data = base64_decode($encryptedData);

        $ivLength = openssl_cipher_iv_length(self::ENCRYPTION_METHOD);
        $iv = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);

        return openssl_decrypt($encrypted, self::ENCRYPTION_METHOD, $key, 0, $iv);
    }

    // ============================================
    // PASSWORDS
    // ============================================

    /**
     * Hash password (bcrypt)
     *
     * @param string $password Password
     * @return string Hash
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * Verificar password contra hash
     *
     * @param string $password Password
     * @param string $hash Hash almacenado
     * @return bool true si coincide
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    /**
     * Verificar si hash necesita rehash (por cambio de algoritmo/costo)
     *
     * @param string $hash Hash actual
     * @return bool true si necesita rehash
     */
    public static function needsRehash($hash) {
        return password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * Generar password aleatorio seguro
     *
     * @param int $length Longitud (default: 16)
     * @return string Password generado
     */
    public static function generatePassword($length = 16) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
        $password = '';

        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return $password;
    }

    // ============================================
    // TOKENS Y CÓDIGOS
    // ============================================

    /**
     * Generar token aleatorio seguro
     *
     * @param int $length Longitud en bytes (default: 32)
     * @return string Token hexadecimal
     */
    public static function generateToken($length = 32) {
        return bin2hex(random_bytes($length));
    }

    /**
     * Generar código numérico aleatorio
     *
     * @param int $digits Número de dígitos (default: 6)
     * @return string Código numérico
     */
    public static function generateCode($digits = 6) {
        $min = pow(10, $digits - 1);
        $max = pow(10, $digits) - 1;

        return (string) random_int($min, $max);
    }

    // ============================================
    // RATE LIMITING
    // ============================================

    /**
     * Verificar rate limit
     *
     * @param string $key Clave única (ej: IP + acción)
     * @param int $maxAttempts Máximos intentos
     * @param int $decaySeconds Ventana de tiempo en segundos
     * @return bool true si está dentro del límite
     */
    public static function checkRateLimit($key, $maxAttempts = 5, $decaySeconds = 60) {
        $now = time();

        // Limpiar intentos expirados
        if (isset(self::$rateLimitStore[$key])) {
            self::$rateLimitStore[$key] = array_filter(
                self::$rateLimitStore[$key],
                function($timestamp) use ($now, $decaySeconds) {
                    return ($now - $timestamp) < $decaySeconds;
                }
            );
        }

        // Contar intentos actuales
        $attempts = isset(self::$rateLimitStore[$key]) ? count(self::$rateLimitStore[$key]) : 0;

        if ($attempts >= $maxAttempts) {
            return false;
        }

        // Registrar intento
        self::$rateLimitStore[$key][] = $now;

        return true;
    }

    /**
     * Resetear rate limit para una clave
     *
     * @param string $key Clave
     */
    public static function resetRateLimit($key) {
        unset(self::$rateLimitStore[$key]);
    }

    // ============================================
    // IP Y HEADERS
    // ============================================

    /**
     * Obtener IP del cliente (considerando proxies)
     *
     * @return string IP
     */
    public static function getClientIp() {
        $headers = [
            'HTTP_CF_CONNECTING_IP',  // Cloudflare
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR'
        ];

        foreach ($headers as $header) {
            if (isset($_SERVER[$header])) {
                $ip = $_SERVER[$header];

                // Si hay múltiples IPs (proxy chain), tomar la primera
                if (strpos($ip, ',') !== false) {
                    $ips = explode(',', $ip);
                    $ip = trim($ips[0]);
                }

                // Validar que sea IP válida
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return '0.0.0.0';
    }

    /**
     * Obtener valor de header
     *
     * @param string $name Nombre del header
     * @return string|null Valor o null
     */
    private static function getHeaderValue($name) {
        $name = 'HTTP_' . str_replace('-', '_', strtoupper($name));
        return $_SERVER[$name] ?? null;
    }

    /**
     * Verificar si IP está en whitelist
     *
     * @param string $ip IP a verificar
     * @param array $whitelist Whitelist de IPs
     * @return bool true si está permitida
     */
    public static function isIpWhitelisted($ip, $whitelist) {
        return in_array($ip, $whitelist);
    }

    /**
     * Verificar si IP está en blacklist
     *
     * @param string $ip IP a verificar
     * @param array $blacklist Blacklist de IPs
     * @return bool true si está bloqueada
     */
    public static function isIpBlacklisted($ip, $blacklist) {
        return in_array($ip, $blacklist);
    }

    // ============================================
    // HEADERS DE SEGURIDAD HTTP
    // ============================================

    /**
     * Establecer headers de seguridad HTTP
     */
    public static function setSecurityHeaders() {
        // Content Security Policy
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline';");

        // X-Frame-Options (protección contra clickjacking)
        header('X-Frame-Options: DENY');

        // X-Content-Type-Options (prevenir MIME sniffing)
        header('X-Content-Type-Options: nosniff');

        // X-XSS-Protection
        header('X-XSS-Protection: 1; mode=block');

        // Referrer Policy
        header('Referrer-Policy: strict-origin-when-cross-origin');

        // Permissions Policy
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

        // HSTS (solo si es HTTPS)
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }
    }

    // ============================================
    // UTILIDADES
    // ============================================

    /**
     * Asegurar que la sesión está iniciada
     */
    private static function ensureSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Verificar si la petición es HTTPS
     *
     * @return bool true si es HTTPS
     */
    public static function isHttps() {
        return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    }

    /**
     * Forzar HTTPS (redirigir si no lo es)
     */
    public static function forceHttps() {
        if (!self::isHttps()) {
            $url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            header("Location: {$url}", true, 301);
            exit;
        }
    }

    /**
     * Prevenir session fixation
     */
    public static function regenerateSession() {
        self::ensureSession();
        session_regenerate_id(true);
    }

    /**
     * Limpiar sesión completamente
     */
    public static function destroySession() {
        self::ensureSession();

        $_SESSION = [];

        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }

        session_destroy();
    }
}
