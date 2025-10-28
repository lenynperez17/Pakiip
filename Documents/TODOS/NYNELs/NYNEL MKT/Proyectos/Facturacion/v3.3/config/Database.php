<?php
/**
 * CLASE DE ABSTRACCIÓN DE BASE DE DATOS
 * Capa de abstracción sobre PDO para conexiones seguras y eficientes
 *
 * CARACTERÍSTICAS:
 * - Singleton pattern para una única conexión
 * - Prepared statements obligatorios (previene SQL Injection)
 * - Manejo de transacciones con rollback automático
 * - Logging de errores estructurado
 * - Soporte para múltiples tipos de consultas
 * - Connection pooling implícito
 * - Lazy loading de conexión
 *
 * @version 1.0
 * @author Sistema Facturación v3.3
 */

class Database {
    /**
     * @var PDO|null Instancia única de conexión PDO
     */
    private static $instance = null;

    /**
     * @var PDO|null Conexión PDO activa
     */
    private $connection = null;

    /**
     * @var array Configuración de base de datos
     */
    private $config = [];

    /**
     * @var bool Indica si hay una transacción activa
     */
    private $inTransaction = false;

    /**
     * @var array Estadísticas de queries ejecutadas
     */
    private $queryStats = [
        'total' => 0,
        'selects' => 0,
        'inserts' => 0,
        'updates' => 0,
        'deletes' => 0,
        'procedures' => 0,
        'errors' => 0
    ];

    /**
     * Constructor privado (Singleton)
     */
    private function __construct() {
        // Cargar configuración desde global.php
        if (file_exists(__DIR__ . '/global.php')) {
            require_once __DIR__ . '/global.php';

            $this->config = [
                'host' => defined('DB_HOST') ? DB_HOST : 'localhost',
                'username' => defined('DB_USERNAME') ? DB_USERNAME : '',
                'password' => defined('DB_PASSWORD') ? DB_PASSWORD : '',
                'database' => defined('DB_NAME') ? DB_NAME : '',
                'charset' => defined('DB_ENCODE') ? DB_ENCODE : 'utf8mb4',
                'options' => [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                    PDO::ATTR_PERSISTENT => true  // Connection pooling
                ]
            ];
        }
    }

    /**
     * Obtener instancia única (Singleton)
     *
     * @return Database Instancia de Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Obtener conexión PDO (lazy loading)
     *
     * @return PDO Conexión PDO activa
     * @throws Exception Si no se puede conectar
     */
    public function getConnection() {
        if ($this->connection === null) {
            try {
                $dsn = sprintf(
                    "mysql:host=%s;dbname=%s;charset=%s",
                    $this->config['host'],
                    $this->config['database'],
                    $this->config['charset']
                );

                $this->connection = new PDO(
                    $dsn,
                    $this->config['username'],
                    $this->config['password'],
                    $this->config['options']
                );

                error_log("Database: Conexión establecida exitosamente");

            } catch (PDOException $e) {
                error_log("Database ERROR: No se pudo conectar - " . $e->getMessage());
                throw new Exception("Error de conexión a base de datos: " . $e->getMessage());
            }
        }

        return $this->connection;
    }

    /**
     * Ejecutar consulta SELECT con prepared statement
     *
     * @param string $sql Consulta SQL con placeholders (?)
     * @param array $params Parámetros para bind
     * @return array Resultados como array asociativo
     */
    public function select($sql, $params = []) {
        try {
            $this->queryStats['total']++;
            $this->queryStats['selects']++;

            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll();

        } catch (PDOException $e) {
            $this->queryStats['errors']++;
            error_log("Database SELECT ERROR: " . $e->getMessage() . " | SQL: " . $sql);
            return [];
        }
    }

    /**
     * Ejecutar consulta SELECT y obtener solo la primera fila
     *
     * @param string $sql Consulta SQL
     * @param array $params Parámetros
     * @return array|null Primera fila o null
     */
    public function selectOne($sql, $params = []) {
        try {
            $this->queryStats['total']++;
            $this->queryStats['selects']++;

            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute($params);

            $result = $stmt->fetch();
            return $result ?: null;

        } catch (PDOException $e) {
            $this->queryStats['errors']++;
            error_log("Database SELECT ONE ERROR: " . $e->getMessage() . " | SQL: " . $sql);
            return null;
        }
    }

    /**
     * Ejecutar INSERT y retornar el ID insertado
     *
     * @param string $sql Consulta INSERT
     * @param array $params Parámetros
     * @return int|false ID insertado o false si falla
     */
    public function insert($sql, $params = []) {
        try {
            $this->queryStats['total']++;
            $this->queryStats['inserts']++;

            $stmt = $this->getConnection()->prepare($sql);
            $success = $stmt->execute($params);

            if ($success) {
                return $this->getConnection()->lastInsertId();
            }

            return false;

        } catch (PDOException $e) {
            $this->queryStats['errors']++;
            error_log("Database INSERT ERROR: " . $e->getMessage() . " | SQL: " . $sql);
            return false;
        }
    }

    /**
     * Ejecutar UPDATE y retornar filas afectadas
     *
     * @param string $sql Consulta UPDATE
     * @param array $params Parámetros
     * @return int|false Filas afectadas o false si falla
     */
    public function update($sql, $params = []) {
        try {
            $this->queryStats['total']++;
            $this->queryStats['updates']++;

            $stmt = $this->getConnection()->prepare($sql);
            $success = $stmt->execute($params);

            if ($success) {
                return $stmt->rowCount();
            }

            return false;

        } catch (PDOException $e) {
            $this->queryStats['errors']++;
            error_log("Database UPDATE ERROR: " . $e->getMessage() . " | SQL: " . $sql);
            return false;
        }
    }

    /**
     * Ejecutar DELETE y retornar filas eliminadas
     *
     * @param string $sql Consulta DELETE
     * @param array $params Parámetros
     * @return int|false Filas eliminadas o false si falla
     */
    public function delete($sql, $params = []) {
        try {
            $this->queryStats['total']++;
            $this->queryStats['deletes']++;

            $stmt = $this->getConnection()->prepare($sql);
            $success = $stmt->execute($params);

            if ($success) {
                return $stmt->rowCount();
            }

            return false;

        } catch (PDOException $e) {
            $this->queryStats['errors']++;
            error_log("Database DELETE ERROR: " . $e->getMessage() . " | SQL: " . $sql);
            return false;
        }
    }

    /**
     * Ejecutar stored procedure
     *
     * @param string $procedureName Nombre del procedimiento
     * @param array $params Parámetros IN
     * @param array $outParams Parámetros OUT (nombres)
     * @return array|false Parámetros OUT como array asociativo o false
     */
    public function callProcedure($procedureName, $params = [], $outParams = []) {
        try {
            $this->queryStats['total']++;
            $this->queryStats['procedures']++;

            $pdo = $this->getConnection();

            // Construir placeholders para IN params
            $inPlaceholders = array_fill(0, count($params), '?');

            // Construir placeholders para OUT params
            $outPlaceholders = array_map(function($name) {
                return "@{$name}";
            }, $outParams);

            // Unir todos los placeholders
            $allPlaceholders = array_merge($inPlaceholders, $outPlaceholders);

            // Llamar al procedimiento
            $sql = "CALL {$procedureName}(" . implode(', ', $allPlaceholders) . ")";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $stmt->closeCursor();

            // Obtener valores OUT si existen
            $result = [];
            if (!empty($outParams)) {
                $outVars = '@' . implode(', @', $outParams);
                $stmt = $pdo->query("SELECT {$outVars}");
                $result = $stmt->fetch();
            }

            return $result ?: true;

        } catch (PDOException $e) {
            $this->queryStats['errors']++;
            error_log("Database PROCEDURE ERROR: " . $e->getMessage() . " | PROCEDURE: " . $procedureName);
            return false;
        }
    }

    /**
     * Iniciar transacción
     *
     * @return bool true si se inició correctamente
     */
    public function beginTransaction() {
        try {
            if (!$this->inTransaction) {
                $this->getConnection()->beginTransaction();
                $this->inTransaction = true;
                error_log("Database: Transacción iniciada");
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Database TRANSACTION ERROR: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Confirmar transacción
     *
     * @return bool true si se confirmó correctamente
     */
    public function commit() {
        try {
            if ($this->inTransaction) {
                $this->getConnection()->commit();
                $this->inTransaction = false;
                error_log("Database: Transacción confirmada (COMMIT)");
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Database COMMIT ERROR: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Revertir transacción
     *
     * @return bool true si se revirtió correctamente
     */
    public function rollback() {
        try {
            if ($this->inTransaction) {
                $this->getConnection()->rollBack();
                $this->inTransaction = false;
                error_log("Database: Transacción revertida (ROLLBACK)");
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Database ROLLBACK ERROR: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Ejecutar transacción con callback
     * Auto-commit si tiene éxito, auto-rollback si falla
     *
     * @param callable $callback Función a ejecutar dentro de la transacción
     * @return mixed Resultado del callback o false si falla
     */
    public function transaction(callable $callback) {
        $this->beginTransaction();

        try {
            $result = $callback($this);
            $this->commit();
            return $result;

        } catch (Exception $e) {
            $this->rollback();
            error_log("Database TRANSACTION CALLBACK ERROR: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener estadísticas de queries ejecutadas
     *
     * @return array Estadísticas
     */
    public function getQueryStats() {
        return $this->queryStats;
    }

    /**
     * Reiniciar estadísticas
     */
    public function resetStats() {
        $this->queryStats = [
            'total' => 0,
            'selects' => 0,
            'inserts' => 0,
            'updates' => 0,
            'deletes' => 0,
            'procedures' => 0,
            'errors' => 0
        ];
    }

    /**
     * Verificar si hay una transacción activa
     *
     * @return bool true si hay transacción activa
     */
    public function isInTransaction() {
        return $this->inTransaction;
    }

    /**
     * Cerrar conexión explícitamente
     */
    public function close() {
        if ($this->connection !== null) {
            // Rollback automático si hay transacción pendiente
            if ($this->inTransaction) {
                $this->rollback();
            }

            $this->connection = null;
            error_log("Database: Conexión cerrada");
        }
    }

    /**
     * Prevenir clonación (Singleton)
     */
    private function __clone() {}

    /**
     * Prevenir deserialización (Singleton)
     */
    public function __wakeup() {
        throw new Exception("No se puede deserializar un singleton");
    }

    /**
     * Destructor: cerrar conexión automáticamente
     */
    public function __destruct() {
        // Rollback automático si hay transacción activa al destruir
        if ($this->inTransaction) {
            error_log("Database WARNING: Transacción activa al destruir objeto - ejecutando ROLLBACK automático");
            $this->rollback();
        }
    }
}

/**
 * FUNCIONES HELPER GLOBALES PARA RETROCOMPATIBILIDAD
 * Permiten usar la nueva clase Database con el código existente
 */

/**
 * Obtener instancia de Database (helper global)
 *
 * @return Database
 */
function db() {
    return Database::getInstance();
}

/**
 * Ejecutar consulta SELECT (helper global)
 *
 * @param string $sql Consulta SQL
 * @param array $params Parámetros
 * @return array Resultados
 */
function db_select($sql, $params = []) {
    return db()->select($sql, $params);
}

/**
 * Ejecutar consulta SELECT ONE (helper global)
 *
 * @param string $sql Consulta SQL
 * @param array $params Parámetros
 * @return array|null Primera fila
 */
function db_select_one($sql, $params = []) {
    return db()->selectOne($sql, $params);
}

/**
 * Ejecutar INSERT (helper global)
 *
 * @param string $sql Consulta INSERT
 * @param array $params Parámetros
 * @return int|false ID insertado
 */
function db_insert($sql, $params = []) {
    return db()->insert($sql, $params);
}

/**
 * Ejecutar UPDATE (helper global)
 *
 * @param string $sql Consulta UPDATE
 * @param array $params Parámetros
 * @return int|false Filas afectadas
 */
function db_update($sql, $params = []) {
    return db()->update($sql, $params);
}

/**
 * Ejecutar DELETE (helper global)
 *
 * @param string $sql Consulta DELETE
 * @param array $params Parámetros
 * @return int|false Filas eliminadas
 */
function db_delete($sql, $params = []) {
    return db()->delete($sql, $params);
}

/**
 * Ejecutar transacción con callback (helper global)
 *
 * @param callable $callback Función a ejecutar
 * @return mixed Resultado
 */
function db_transaction(callable $callback) {
    return db()->transaction($callback);
}
