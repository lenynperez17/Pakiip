<?php
/**
 * SISTEMA DE LOGGING CENTRALIZADO
 * Proporciona logging estructurado con múltiples niveles y destinos
 *
 * CARACTERÍSTICAS:
 * - Niveles de log estándar (PSR-3 compatible)
 * - Rotación automática de archivos por tamaño y fecha
 * - Múltiples canales (app, sql, security, api, etc.)
 * - Formato estructurado con contexto
 * - Filtrado por nivel mínimo
 * - Soporte para logs de auditoría
 * - Integración con error_log nativo
 * - Timestamps precisos
 *
 * @version 1.0
 * @author Sistema Facturación v3.3
 */

class Logger {
    // Niveles de log (PSR-3 compatible)
    const EMERGENCY = 'emergency';  // Sistema no utilizable
    const ALERT     = 'alert';      // Acción requerida inmediatamente
    const CRITICAL  = 'critical';   // Condiciones críticas
    const ERROR     = 'error';      // Errores de runtime que no requieren acción inmediata
    const WARNING   = 'warning';    // Situaciones excepcionales pero no errores
    const NOTICE    = 'notice';     // Eventos normales pero significativos
    const INFO      = 'info';       // Eventos informativos
    const DEBUG     = 'debug';      // Información detallada de debugging

    /**
     * @var array Mapeo de niveles a números para comparación
     */
    private static $levelValues = [
        self::DEBUG     => 100,
        self::INFO      => 200,
        self::NOTICE    => 250,
        self::WARNING   => 300,
        self::ERROR     => 400,
        self::CRITICAL  => 500,
        self::ALERT     => 550,
        self::EMERGENCY => 600,
    ];

    /**
     * @var string Directorio base para logs
     */
    private static $logDir = null;

    /**
     * @var string Canal actual (app, sql, security, etc.)
     */
    private $channel = 'app';

    /**
     * @var string Nivel mínimo de log
     */
    private $minLevel = self::DEBUG;

    /**
     * @var int Tamaño máximo de archivo de log en bytes (5MB default)
     */
    private static $maxFileSize = 5242880;

    /**
     * @var int Número máximo de archivos rotados a mantener
     */
    private static $maxFiles = 10;

    /**
     * @var bool Habilitar logging en consola
     */
    private static $consoleEnabled = true;

    /**
     * @var array Instancias por canal (Singleton por canal)
     */
    private static $instances = [];

    /**
     * Constructor privado
     *
     * @param string $channel Canal de logging
     */
    private function __construct($channel = 'app') {
        $this->channel = $channel;

        // Establecer directorio de logs
        if (self::$logDir === null) {
            self::$logDir = dirname(__DIR__) . '/logs';

            // Crear directorio si no existe
            if (!is_dir(self::$logDir)) {
                @mkdir(self::$logDir, 0755, true);
            }
        }

        // Establecer nivel mínimo desde configuración
        if (defined('LOG_MIN_LEVEL')) {
            $this->minLevel = LOG_MIN_LEVEL;
        }
    }

    /**
     * Obtener instancia de Logger para un canal
     *
     * @param string $channel Canal de logging
     * @return Logger
     */
    public static function channel($channel = 'app') {
        if (!isset(self::$instances[$channel])) {
            self::$instances[$channel] = new self($channel);
        }

        return self::$instances[$channel];
    }

    /**
     * Configurar directorio de logs
     *
     * @param string $dir Directorio
     */
    public static function setLogDir($dir) {
        self::$logDir = $dir;

        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
    }

    /**
     * Configurar tamaño máximo de archivo
     *
     * @param int $bytes Tamaño en bytes
     */
    public static function setMaxFileSize($bytes) {
        self::$maxFileSize = $bytes;
    }

    /**
     * Configurar número máximo de archivos
     *
     * @param int $files Número de archivos
     */
    public static function setMaxFiles($files) {
        self::$maxFiles = $files;
    }

    /**
     * Establecer nivel mínimo de log
     *
     * @param string $level Nivel (debug, info, warning, error, etc.)
     */
    public function setMinLevel($level) {
        $this->minLevel = $level;
    }

    /**
     * Verificar si un nivel debe loggearse
     *
     * @param string $level Nivel a verificar
     * @return bool true si debe loggearse
     */
    private function shouldLog($level) {
        $levelValue = self::$levelValues[$level] ?? 0;
        $minLevelValue = self::$levelValues[$this->minLevel] ?? 0;

        return $levelValue >= $minLevelValue;
    }

    /**
     * Escribir log genérico
     *
     * @param string $level Nivel
     * @param string $message Mensaje
     * @param array $context Contexto adicional
     */
    public function log($level, $message, $context = []) {
        // Verificar si debe loggearse este nivel
        if (!$this->shouldLog($level)) {
            return;
        }

        // Construir entrada de log
        $logEntry = $this->formatLogEntry($level, $message, $context);

        // Escribir a archivo
        $this->writeToFile($logEntry);

        // Escribir a consola si está habilitado
        if (self::$consoleEnabled) {
            $this->writeToConsole($level, $message);
        }
    }

    /**
     * Log nivel EMERGENCY
     */
    public function emergency($message, $context = []) {
        $this->log(self::EMERGENCY, $message, $context);
    }

    /**
     * Log nivel ALERT
     */
    public function alert($message, $context = []) {
        $this->log(self::ALERT, $message, $context);
    }

    /**
     * Log nivel CRITICAL
     */
    public function critical($message, $context = []) {
        $this->log(self::CRITICAL, $message, $context);
    }

    /**
     * Log nivel ERROR
     */
    public function error($message, $context = []) {
        $this->log(self::ERROR, $message, $context);
    }

    /**
     * Log nivel WARNING
     */
    public function warning($message, $context = []) {
        $this->log(self::WARNING, $message, $context);
    }

    /**
     * Log nivel NOTICE
     */
    public function notice($message, $context = []) {
        $this->log(self::NOTICE, $message, $context);
    }

    /**
     * Log nivel INFO
     */
    public function info($message, $context = []) {
        $this->log(self::INFO, $message, $context);
    }

    /**
     * Log nivel DEBUG
     */
    public function debug($message, $context = []) {
        $this->log(self::DEBUG, $message, $context);
    }

    /**
     * Log de query SQL
     *
     * @param string $sql Query SQL
     * @param array $params Parámetros
     * @param float $duration Duración en segundos
     */
    public function sql($sql, $params = [], $duration = 0) {
        $context = [
            'sql' => $sql,
            'params' => $params,
            'duration' => number_format($duration, 4) . 's'
        ];

        $message = "SQL Query executed";
        if ($duration > 1) {
            // Queries lentas como WARNING
            $this->warning($message, $context);
        } else {
            $this->debug($message, $context);
        }
    }

    /**
     * Log de auditoría (siempre se registra)
     *
     * @param string $action Acción realizada
     * @param array $context Contexto
     */
    public function audit($action, $context = []) {
        // Agregar información del usuario si está disponible
        if (isset($_SESSION['idusuario'])) {
            $context['user_id'] = $_SESSION['idusuario'];
            $context['user_name'] = $_SESSION['nombre'] ?? 'Unknown';
        }

        // Agregar IP
        $context['ip'] = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';

        // Agregar timestamp
        $context['timestamp'] = date('Y-m-d H:i:s');

        // Log como NOTICE (siempre se registra)
        $oldMinLevel = $this->minLevel;
        $this->minLevel = self::NOTICE;

        $this->notice("AUDIT: {$action}", $context);

        $this->minLevel = $oldMinLevel;
    }

    /**
     * Log de excepción
     *
     * @param Exception $exception Excepción
     * @param string $message Mensaje adicional
     */
    public function exception($exception, $message = 'Exception caught') {
        $context = [
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ];

        $this->error($message, $context);
    }

    /**
     * Formatear entrada de log
     *
     * @param string $level Nivel
     * @param string $message Mensaje
     * @param array $context Contexto
     * @return string Entrada formateada
     */
    private function formatLogEntry($level, $message, $context) {
        $timestamp = date('Y-m-d H:i:s.u');
        $levelUpper = strtoupper($level);

        // Construir línea base
        $logLine = "[{$timestamp}] {$this->channel}.{$levelUpper}: {$message}";

        // Agregar contexto si existe
        if (!empty($context)) {
            $contextJson = json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $logLine .= " | Context: {$contextJson}";
        }

        // Agregar información de request si está disponible
        if (isset($_SERVER['REQUEST_METHOD'])) {
            $method = $_SERVER['REQUEST_METHOD'];
            $uri = $_SERVER['REQUEST_URI'] ?? '/';
            $logLine .= " | Request: {$method} {$uri}";
        }

        return $logLine . PHP_EOL;
    }

    /**
     * Escribir a archivo de log
     *
     * @param string $logEntry Entrada de log
     */
    private function writeToFile($logEntry) {
        $filename = $this->getLogFilename();

        // Verificar rotación de archivo
        $this->rotateIfNeeded($filename);

        // Escribir a archivo
        @file_put_contents($filename, $logEntry, FILE_APPEND | LOCK_EX);
    }

    /**
     * Escribir a consola (error_log)
     *
     * @param string $level Nivel
     * @param string $message Mensaje
     */
    private function writeToConsole($level, $message) {
        $levelUpper = strtoupper($level);
        error_log("[{$this->channel}.{$levelUpper}] {$message}");
    }

    /**
     * Obtener nombre de archivo de log
     *
     * @return string Ruta completa
     */
    private function getLogFilename() {
        $date = date('Y-m-d');
        return self::$logDir . "/{$this->channel}-{$date}.log";
    }

    /**
     * Rotar archivo si excede el tamaño máximo
     *
     * @param string $filename Archivo a verificar
     */
    private function rotateIfNeeded($filename) {
        if (!file_exists($filename)) {
            return;
        }

        $filesize = filesize($filename);

        if ($filesize >= self::$maxFileSize) {
            // Rotar archivos existentes
            for ($i = self::$maxFiles - 1; $i > 0; $i--) {
                $oldFile = "{$filename}.{$i}";
                $newFile = "{$filename}." . ($i + 1);

                if (file_exists($oldFile)) {
                    @rename($oldFile, $newFile);
                }
            }

            // Renombrar archivo actual
            @rename($filename, "{$filename}.1");
        }
    }

    /**
     * Limpiar logs antiguos
     *
     * @param int $days Días a mantener
     */
    public static function cleanOldLogs($days = 30) {
        if (!is_dir(self::$logDir)) {
            return;
        }

        $files = glob(self::$logDir . '/*.log*');
        $now = time();

        foreach ($files as $file) {
            if (is_file($file)) {
                $fileAge = $now - filemtime($file);
                $daysOld = floor($fileAge / 86400);

                if ($daysOld >= $days) {
                    @unlink($file);
                }
            }
        }
    }

    /**
     * Leer últimas líneas del log
     *
     * @param int $lines Número de líneas
     * @return array Líneas del log
     */
    public function tail($lines = 100) {
        $filename = $this->getLogFilename();

        if (!file_exists($filename)) {
            return [];
        }

        $file = new SplFileObject($filename, 'r');
        $file->seek(PHP_INT_MAX);
        $totalLines = $file->key() + 1;

        $startLine = max(0, $totalLines - $lines);
        $file->seek($startLine);

        $result = [];
        while (!$file->eof()) {
            $line = $file->current();
            if (trim($line) !== '') {
                $result[] = $line;
            }
            $file->next();
        }

        return $result;
    }

    /**
     * Obtener estadísticas de logs
     *
     * @return array Estadísticas
     */
    public function getStats() {
        $filename = $this->getLogFilename();

        if (!file_exists($filename)) {
            return [
                'file' => $filename,
                'exists' => false,
                'size' => 0,
                'lines' => 0
            ];
        }

        $file = new SplFileObject($filename, 'r');
        $file->seek(PHP_INT_MAX);
        $lines = $file->key() + 1;

        return [
            'file' => $filename,
            'exists' => true,
            'size' => filesize($filename),
            'size_human' => $this->formatBytes(filesize($filename)),
            'lines' => $lines,
            'last_modified' => filemtime($filename),
            'last_modified_human' => date('Y-m-d H:i:s', filemtime($filename))
        ];
    }

    /**
     * Formatear bytes a formato legible
     *
     * @param int $bytes Bytes
     * @return string Tamaño formateado
     */
    private function formatBytes($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}

/**
 * FUNCIONES HELPER GLOBALES
 */

/**
 * Obtener logger para canal (helper global)
 */
function logger($channel = 'app') {
    return Logger::channel($channel);
}

/**
 * Log rápido nivel INFO (helper global)
 */
function log_info($message, $context = []) {
    Logger::channel('app')->info($message, $context);
}

/**
 * Log rápido nivel ERROR (helper global)
 */
function log_error($message, $context = []) {
    Logger::channel('app')->error($message, $context);
}

/**
 * Log rápido nivel WARNING (helper global)
 */
function log_warning($message, $context = []) {
    Logger::channel('app')->warning($message, $context);
}

/**
 * Log rápido nivel DEBUG (helper global)
 */
function log_debug($message, $context = []) {
    Logger::channel('app')->debug($message, $context);
}

/**
 * Log de auditoría (helper global)
 */
function log_audit($action, $context = []) {
    Logger::channel('audit')->audit($action, $context);
}

/**
 * Log de SQL (helper global)
 */
function log_sql($sql, $params = [], $duration = 0) {
    Logger::channel('sql')->sql($sql, $params, $duration);
}

/**
 * Log de excepción (helper global)
 */
function log_exception($exception, $message = 'Exception caught') {
    Logger::channel('app')->exception($exception, $message);
}
