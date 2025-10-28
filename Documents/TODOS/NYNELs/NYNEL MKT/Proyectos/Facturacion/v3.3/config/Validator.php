<?php
/**
 * SISTEMA DE VALIDACIÓN CENTRALIZADO
 * Proporciona validaciones reutilizables para todo el sistema
 *
 * CARACTERÍSTICAS:
 * - Validaciones comunes (email, RUC, DNI, teléfono, etc.)
 * - Validaciones personalizadas
 * - Mensajes de error descriptivos en español
 * - Validaciones específicas de Perú (SUNAT)
 * - Sanitización integrada
 * - Validaciones en cadena (fluent interface)
 * - Validaciones de arrays y objetos
 *
 * @version 1.0
 * @author Sistema Facturación v3.3
 */

class Validator {
    /**
     * @var array Errores de validación acumulados
     */
    private $errors = [];

    /**
     * @var array Datos a validar
     */
    private $data = [];

    /**
     * @var array Reglas de validación
     */
    private $rules = [];

    /**
     * @var array Mensajes personalizados
     */
    private $messages = [];

    /**
     * Constructor
     *
     * @param array $data Datos a validar
     */
    public function __construct($data = []) {
        $this->data = $data;
    }

    /**
     * Crear instancia estática
     *
     * @param array $data Datos a validar
     * @return Validator
     */
    public static function make($data = []) {
        return new self($data);
    }

    /**
     * Establecer reglas de validación
     *
     * @param array $rules Reglas ['campo' => 'regla1|regla2']
     * @return $this
     */
    public function rules($rules) {
        $this->rules = $rules;
        return $this;
    }

    /**
     * Establecer mensajes personalizados
     *
     * @param array $messages Mensajes ['campo.regla' => 'mensaje']
     * @return $this
     */
    public function messages($messages) {
        $this->messages = $messages;
        return $this;
    }

    /**
     * Ejecutar validación
     *
     * @return bool true si pasa todas las validaciones
     */
    public function validate() {
        $this->errors = [];

        foreach ($this->rules as $field => $rulesString) {
            $rules = explode('|', $rulesString);
            $value = $this->data[$field] ?? null;

            foreach ($rules as $rule) {
                // Parsear regla con parámetros (ej: "max:100")
                $ruleParts = explode(':', $rule);
                $ruleName = $ruleParts[0];
                $ruleParams = isset($ruleParts[1]) ? explode(',', $ruleParts[1]) : [];

                // Ejecutar validación
                $methodName = 'validate' . ucfirst($ruleName);

                if (method_exists($this, $methodName)) {
                    $isValid = $this->$methodName($value, $field, ...$ruleParams);

                    if (!$isValid) {
                        $this->addError($field, $ruleName, $ruleParams);
                    }
                }
            }
        }

        return empty($this->errors);
    }

    /**
     * Validar campo específico
     *
     * @param string $field Nombre del campo
     * @param mixed $value Valor a validar
     * @param string $rulesString Reglas separadas por |
     * @return bool true si válido
     */
    public function validateField($field, $value, $rulesString) {
        $rules = explode('|', $rulesString);

        foreach ($rules as $rule) {
            $ruleParts = explode(':', $rule);
            $ruleName = $ruleParts[0];
            $ruleParams = isset($ruleParts[1]) ? explode(',', $ruleParts[1]) : [];

            $methodName = 'validate' . ucfirst($ruleName);

            if (method_exists($this, $methodName)) {
                $isValid = $this->$methodName($value, $field, ...$ruleParams);

                if (!$isValid) {
                    $this->addError($field, $ruleName, $ruleParams);
                    return false;
                }
            }
        }

        return true;
    }

    // ============================================
    // VALIDACIONES BÁSICAS
    // ============================================

    /**
     * Validar campo requerido
     */
    private function validateRequired($value, $field) {
        if (is_array($value)) {
            return !empty($value);
        }
        return $value !== null && $value !== '';
    }

    /**
     * Validar email
     */
    private function validateEmail($value, $field) {
        if (empty($value)) return true; // Solo validar si tiene valor
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validar número entero
     */
    private function validateInteger($value, $field) {
        if (empty($value)) return true;
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    /**
     * Validar número decimal
     */
    private function validateNumeric($value, $field) {
        if (empty($value)) return true;
        return is_numeric($value);
    }

    /**
     * Validar mínimo de caracteres
     */
    private function validateMin($value, $field, $min) {
        if (empty($value)) return true;
        return mb_strlen($value) >= $min;
    }

    /**
     * Validar máximo de caracteres
     */
    private function validateMax($value, $field, $max) {
        if (empty($value)) return true;
        return mb_strlen($value) <= $max;
    }

    /**
     * Validar longitud exacta
     */
    private function validateLength($value, $field, $length) {
        if (empty($value)) return true;
        return mb_strlen($value) == $length;
    }

    /**
     * Validar valor mínimo (numérico)
     */
    private function validateMinValue($value, $field, $min) {
        if (empty($value)) return true;
        return floatval($value) >= floatval($min);
    }

    /**
     * Validar valor máximo (numérico)
     */
    private function validateMaxValue($value, $field, $max) {
        if (empty($value)) return true;
        return floatval($value) <= floatval($max);
    }

    /**
     * Validar que valor esté en lista
     */
    private function validateIn($value, $field, ...$allowedValues) {
        if (empty($value)) return true;
        return in_array($value, $allowedValues);
    }

    /**
     * Validar que valor NO esté en lista
     */
    private function validateNotIn($value, $field, ...$forbiddenValues) {
        if (empty($value)) return true;
        return !in_array($value, $forbiddenValues);
    }

    /**
     * Validar URL
     */
    private function validateUrl($value, $field) {
        if (empty($value)) return true;
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Validar IP
     */
    private function validateIp($value, $field) {
        if (empty($value)) return true;
        return filter_var($value, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Validar fecha (formato Y-m-d)
     */
    private function validateDate($value, $field) {
        if (empty($value)) return true;
        $d = DateTime::createFromFormat('Y-m-d', $value);
        return $d && $d->format('Y-m-d') === $value;
    }

    /**
     * Validar patrón regex
     */
    private function validateRegex($value, $field, $pattern) {
        if (empty($value)) return true;
        return preg_match($pattern, $value) === 1;
    }

    /**
     * Validar solo letras
     */
    private function validateAlpha($value, $field) {
        if (empty($value)) return true;
        return preg_match('/^[\pL\s]+$/u', $value) === 1;
    }

    /**
     * Validar solo letras y números
     */
    private function validateAlphaNum($value, $field) {
        if (empty($value)) return true;
        return preg_match('/^[\pL\pN\s]+$/u', $value) === 1;
    }

    /**
     * Validar JSON válido
     */
    private function validateJson($value, $field) {
        if (empty($value)) return true;
        json_decode($value);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Validar booleano
     */
    private function validateBoolean($value, $field) {
        return in_array($value, [true, false, 0, 1, '0', '1'], true);
    }

    // ============================================
    // VALIDACIONES ESPECÍFICAS DE PERÚ
    // ============================================

    /**
     * Validar RUC (11 dígitos)
     */
    private function validateRuc($value, $field) {
        if (empty($value)) return true;

        // Debe tener 11 dígitos
        if (!preg_match('/^\d{11}$/', $value)) {
            return false;
        }

        // Validar dígito verificador
        $suma = 0;
        $factores = [5, 4, 3, 2, 7, 6, 5, 4, 3, 2];

        for ($i = 0; $i < 10; $i++) {
            $suma += $value[$i] * $factores[$i];
        }

        $resto = $suma % 11;
        $digitoVerificador = 11 - $resto;

        if ($digitoVerificador == 10) $digitoVerificador = 0;
        if ($digitoVerificador == 11) $digitoVerificador = 1;

        return $value[10] == $digitoVerificador;
    }

    /**
     * Validar DNI (8 dígitos)
     */
    private function validateDni($value, $field) {
        if (empty($value)) return true;
        return preg_match('/^\d{8}$/', $value) === 1;
    }

    /**
     * Validar Carnet de Extranjería (9 dígitos)
     */
    private function validateCe($value, $field) {
        if (empty($value)) return true;
        return preg_match('/^\d{9}$/', $value) === 1;
    }

    /**
     * Validar teléfono peruano (9 dígitos, empieza con 9)
     */
    private function validateTelefonoPeru($value, $field) {
        if (empty($value)) return true;
        return preg_match('/^9\d{8}$/', $value) === 1;
    }

    /**
     * Validar código postal peruano (5 dígitos)
     */
    private function validateCodigoPostal($value, $field) {
        if (empty($value)) return true;
        return preg_match('/^\d{5}$/', $value) === 1;
    }

    /**
     * Validar partida arancelaria SUNAT (10 dígitos)
     */
    private function validatePartidaArancelaria($value, $field) {
        if (empty($value)) return true;
        return preg_match('/^\d{10}$/', $value) === 1;
    }

    /**
     * Validar código de tipo de comprobante SUNAT
     */
    private function validateTipoComprobanteSunat($value, $field) {
        if (empty($value)) return true;
        $tiposValidos = ['01', '03', '07', '08', '09', '12', '13', '20', '31', '40', '50', '80'];
        return in_array($value, $tiposValidos);
    }

    /**
     * Validar serie de comprobante
     */
    private function validateSerieComprobante($value, $field) {
        if (empty($value)) return true;
        // Formato: F001, B001, T001, etc. (1 letra + 3 dígitos)
        return preg_match('/^[A-Z]{1}\d{3}$/', $value) === 1;
    }

    // ============================================
    // VALIDACIONES DE ARCHIVOS
    // ============================================

    /**
     * Validar tamaño máximo de archivo (en KB)
     */
    private function validateMaxFileSize($file, $field, $maxSizeKB) {
        if (!isset($file['size'])) return true;
        return ($file['size'] / 1024) <= $maxSizeKB;
    }

    /**
     * Validar extensiones de archivo permitidas
     */
    private function validateFileExtension($file, $field, ...$allowedExtensions) {
        if (!isset($file['name'])) return true;
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        return in_array($extension, $allowedExtensions);
    }

    /**
     * Validar MIME type de archivo
     */
    private function validateMimeType($file, $field, ...$allowedMimes) {
        if (!isset($file['type'])) return true;
        return in_array($file['type'], $allowedMimes);
    }

    // ============================================
    // MÉTODOS DE ERROR
    // ============================================

    /**
     * Agregar error de validación
     */
    private function addError($field, $rule, $params = []) {
        $key = "{$field}.{$rule}";

        // Buscar mensaje personalizado
        if (isset($this->messages[$key])) {
            $message = $this->messages[$key];
        } else {
            $message = $this->getDefaultMessage($field, $rule, $params);
        }

        $this->errors[$field][] = $message;
    }

    /**
     * Obtener mensaje por defecto
     */
    private function getDefaultMessage($field, $rule, $params) {
        $messages = [
            'required' => "El campo {$field} es obligatorio",
            'email' => "El campo {$field} debe ser un email válido",
            'integer' => "El campo {$field} debe ser un número entero",
            'numeric' => "El campo {$field} debe ser numérico",
            'min' => "El campo {$field} debe tener al menos {$params[0]} caracteres",
            'max' => "El campo {$field} no debe exceder {$params[0]} caracteres",
            'length' => "El campo {$field} debe tener exactamente {$params[0]} caracteres",
            'minValue' => "El campo {$field} debe ser al menos {$params[0]}",
            'maxValue' => "El campo {$field} no debe ser mayor a {$params[0]}",
            'in' => "El campo {$field} debe ser uno de: " . implode(', ', $params),
            'notIn' => "El campo {$field} no puede ser: " . implode(', ', $params),
            'url' => "El campo {$field} debe ser una URL válida",
            'ip' => "El campo {$field} debe ser una IP válida",
            'date' => "El campo {$field} debe ser una fecha válida (Y-m-d)",
            'regex' => "El campo {$field} no tiene el formato correcto",
            'alpha' => "El campo {$field} solo puede contener letras",
            'alphaNum' => "El campo {$field} solo puede contener letras y números",
            'json' => "El campo {$field} debe ser un JSON válido",
            'boolean' => "El campo {$field} debe ser verdadero o falso",
            'ruc' => "El campo {$field} debe ser un RUC válido (11 dígitos)",
            'dni' => "El campo {$field} debe ser un DNI válido (8 dígitos)",
            'ce' => "El campo {$field} debe ser un Carnet de Extranjería válido (9 dígitos)",
            'telefonoPeru' => "El campo {$field} debe ser un teléfono válido (9 dígitos empezando con 9)",
            'codigoPostal' => "El campo {$field} debe ser un código postal válido (5 dígitos)",
            'partidaArancelaria' => "El campo {$field} debe ser una partida arancelaria válida (10 dígitos)",
            'tipoComprobanteSunat' => "El campo {$field} debe ser un tipo de comprobante SUNAT válido",
            'serieComprobante' => "El campo {$field} debe tener formato de serie (ej: F001)",
            'maxFileSize' => "El archivo {$field} no debe exceder {$params[0]} KB",
            'fileExtension' => "El archivo {$field} debe ser de tipo: " . implode(', ', $params),
            'mimeType' => "El archivo {$field} debe ser de tipo MIME: " . implode(', ', $params),
        ];

        return $messages[$rule] ?? "El campo {$field} no es válido";
    }

    /**
     * Obtener todos los errores
     *
     * @return array Errores
     */
    public function errors() {
        return $this->errors;
    }

    /**
     * Obtener primer error de un campo
     *
     * @param string $field Nombre del campo
     * @return string|null Primer error o null
     */
    public function first($field) {
        return $this->errors[$field][0] ?? null;
    }

    /**
     * Verificar si hay errores
     *
     * @return bool true si hay errores
     */
    public function fails() {
        return !empty($this->errors);
    }

    /**
     * Verificar si pasó la validación
     *
     * @return bool true si no hay errores
     */
    public function passes() {
        return empty($this->errors);
    }

    /**
     * Obtener todos los errores en formato plano
     *
     * @return array Array de strings con todos los errores
     */
    public function allErrors() {
        $all = [];
        foreach ($this->errors as $field => $errors) {
            $all = array_merge($all, $errors);
        }
        return $all;
    }

    // ============================================
    // MÉTODOS ESTÁTICOS DE UTILIDAD
    // ============================================

    /**
     * Validación rápida de RUC
     */
    public static function isValidRUC($ruc) {
        $validator = new self();
        return $validator->validateRuc($ruc, 'ruc');
    }

    /**
     * Validación rápida de DNI
     */
    public static function isValidDNI($dni) {
        $validator = new self();
        return $validator->validateDni($dni, 'dni');
    }

    /**
     * Validación rápida de email
     */
    public static function isValidEmail($email) {
        $validator = new self();
        return $validator->validateEmail($email, 'email');
    }

    /**
     * Validación rápida de teléfono peruano
     */
    public static function isValidTelefono($telefono) {
        $validator = new self();
        return $validator->validateTelefonoPeru($telefono, 'telefono');
    }

    /**
     * Sanitizar string (eliminar HTML, espacios)
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
     * Sanitizar número (solo dígitos)
     */
    public static function sanitizeNumber($value) {
        return preg_replace('/[^0-9.-]/', '', $value);
    }

    /**
     * Sanitizar email
     */
    public static function sanitizeEmail($email) {
        return filter_var($email, FILTER_SANITIZE_EMAIL);
    }
}
