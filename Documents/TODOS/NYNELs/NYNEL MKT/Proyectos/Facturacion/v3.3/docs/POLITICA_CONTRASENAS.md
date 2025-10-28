# 🔐 POLÍTICA DE CONTRASEÑAS FUERTE
## Sistema de Facturación v3.3

---

## 📋 RESUMEN EJECUTIVO

Sistema completo de validación y gestión de contraseñas seguras que implementa:
- ✅ Validación robusta en frontend (JavaScript) y backend (PHP)
- ✅ Requisitos estrictos de complejidad
- ✅ Prevención de reutilización (historial de 5 contraseñas)
- ✅ Detección de contraseñas comunes
- ✅ Expiración configurable (90 días por defecto)
- ✅ Indicador visual de fortaleza en tiempo real

---

## 🛡️ REQUISITOS DE CONTRASEÑA

### Configuración Actual (password_policy.php)

| Requisito | Valor | Descripción |
|-----------|-------|-------------|
| **Longitud mínima** | 12 caracteres | Estándar NIST: mínimo 8, recomendado 12+ |
| **Mayúsculas** | Requerido | Al menos una letra A-Z |
| **Minúsculas** | Requerido | Al menos una letra a-z |
| **Números** | Requerido | Al menos un dígito 0-9 |
| **Símbolos** | Requerido | Al menos un carácter especial (!@#$%^&*) |
| **Historial** | 5 contraseñas | No reutilizar las últimas 5 |
| **Expiración** | 90 días | Cambio obligatorio cada 90 días |

### Validaciones Adicionales

✅ **Prevención de Contraseñas Comunes**
- Base de datos de 30+ contraseñas más usadas
- Verificación case-insensitive
- Rechaza contraseñas como: password123, admin123, factura123

✅ **Detección de Patrones Secuenciales**
- Detecta: 123, abc, qwerty, asdf
- Rechaza teclados obvios (qwe, asd, zxc)
- Penaliza en puntuación de fortaleza

✅ **Indicador de Fortaleza (0-100 puntos)**
```
0-20:   Muy débil   (🔴 Rojo)
20-40:  Débil       (🟠 Naranja)
40-60:  Media       (🟡 Amarillo)
60-80:  Fuerte      (🟢 Verde claro)
80-100: Muy fuerte  (🟢 Verde)
```

---

## 🚀 INSTALACIÓN

### Paso 1: Crear Tabla de Historial

```bash
# Ejecutar script SQL
mysql -u root -p dbsistema < /path/to/config/password_history_table.sql

# Verificar creación
mysql -u root -p dbsistema -e "SHOW TABLES LIKE '%password%'"
```

Esto creará:
- ✅ Tabla `usuario_password_history`
- ✅ Columna `password_changed_at` en `usuario`
- ✅ Trigger `usuario_password_updated`
- ✅ Vista `v_usuario_password_status`
- ✅ Procedimiento `limpiar_historial_passwords()`
- ✅ Evento programado `limpiar_historial_semanal`

### Paso 2: Integrar Helper PHP

```php
// En cualquier archivo que valide contraseñas
require_once "../config/password_policy.php";

// Al crear/actualizar usuario
$password = $_POST['password'];
$idusuario = $_POST['idusuario']; // Solo para edición

// Validar contraseña
$validacion = validarPoliticaPassword($password, $idusuario);

if (!$validacion['valida']) {
    // Mostrar errores
    echo json_encode([
        'success' => false,
        'errors' => $validacion['errores']
    ]);
    exit();
}

// Crear hash bcrypt
$password_hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

// Registrar en historial (para edición)
if ($idusuario) {
    registrarPasswordEnHistorial($idusuario, $password_hash);
}

// Guardar en BD
// ...
```

### Paso 3: Integrar JavaScript en Frontend

```html
<!-- En el formulario de usuario -->
<script src="/public/js/password_validator.js"></script>

<div class="form-group">
    <label for="password">Contraseña *</label>
    <div class="input-group">
        <input type="password" class="form-control" id="password" name="password" required>
        <button type="button" class="btn btn-outline-secondary" id="password_toggle">
            <i class="fa fa-eye"></i>
        </button>
        <button type="button" class="btn btn-outline-primary" id="password_generate">
            <i class="fa fa-random"></i> Generar
        </button>
    </div>
    <div id="password_feedback"></div>
</div>

<script>
    // Inicializar validador
    initPasswordValidator('password', 'password_feedback');

    // Mostrar requisitos (opcional)
    document.getElementById('password_requirements').innerHTML = getPasswordRequirementsHTML();
</script>
```

### Paso 4: Actualizar Formulario de Usuario

Modificar `/ajax/usuario.php` caso `guardaryeditar`:

```php
case 'guardaryeditar':
    // ... código existente ...

    if (!empty($clave)) {
        // VALIDAR CONTRASEÑA CON POLÍTICA
        require_once "../config/password_policy.php";

        $validacion = validarPoliticaPassword($clave, $idusuario);

        if (!$validacion['valida']) {
            echo json_encode([
                'success' => false,
                'message' => 'Contraseña no cumple requisitos',
                'errors' => $validacion['errores']
            ]);
            exit();
        }

        // Crear hash bcrypt
        $clavehash = password_hash($clave, PASSWORD_BCRYPT, ['cost' => 12]);

        // Registrar en historial si es edición
        if (!empty($idusuario)) {
            registrarPasswordEnHistorial($idusuario, $clavehash);
        }
    }

    // ... continuar con insertar/editar ...
```

---

## 📊 USO Y EJEMPLOS

### Ejemplo 1: Validar Contraseña Manualmente

```php
require_once "../config/password_policy.php";

$password = "MiPassword123!";
$resultado = validarPoliticaPassword($password);

if ($resultado['valida']) {
    echo "✅ Contraseña válida";
    echo "Fortaleza: {$resultado['fortaleza_nivel']} ({$resultado['fortaleza_puntos']}/100)";
} else {
    echo "❌ Contraseña inválida:";
    foreach ($resultado['errores'] as $error) {
        echo "- $error\n";
    }
}
```

### Ejemplo 2: Generar Contraseña Segura

```php
require_once "../config/password_policy.php";

// Generar contraseña de 16 caracteres
$password_seguro = generarPasswordSeguro(16);
echo "Contraseña generada: $password_seguro";

// Ejemplo de salida: kL9#mZ2@pN5&qR8!
```

### Ejemplo 3: Verificar Expiración

```php
require_once "../config/password_policy.php";

$idusuario = 1;
$expiracion = verificarExpiracionPassword($idusuario);

if ($expiracion['expirada']) {
    echo "⚠️ Tu contraseña ha expirado. Debes cambiarla.";
} elseif ($expiracion['dias_restantes'] <= 15) {
    echo "🔔 Tu contraseña expira en {$expiracion['dias_restantes']} días.";
} else {
    echo "✅ Tu contraseña está vigente ({$expiracion['dias_restantes']} días restantes).";
}
```

### Ejemplo 4: Usar Validador JavaScript

```javascript
// Validar en tiempo real
const password = document.getElementById('password').value;
const result = validatePassword(password);

if (result.valid) {
    console.log('✅ Válida:', result.strengthLevel);
} else {
    console.log('❌ Errores:', result.errors);
}

// Generar contraseña
const securePassword = generateSecurePassword(16);
console.log('Nueva contraseña:', securePassword);
```

---

## 🔧 CONFIGURACIÓN AVANZADA

### Personalizar Requisitos

Editar `/config/password_policy.php`:

```php
// Hacer más estricto (empresa de alto riesgo)
define('PASSWORD_MIN_LENGTH', 16);              // 16 caracteres
define('PASSWORD_HISTORY_COUNT', 10);           // No reutilizar últimas 10
define('PASSWORD_EXPIRATION_DAYS', 60);         // Cambiar cada 60 días

// Hacer más flexible (ambiente de desarrollo)
define('PASSWORD_MIN_LENGTH', 8);               // 8 caracteres
define('PASSWORD_REQUIRE_SYMBOLS', false);      // Símbolos opcionales
define('PASSWORD_EXPIRATION_DAYS', 0);          // Sin expiración
```

### Agregar Contraseñas Prohibidas Personalizadas

```php
// En password_policy.php, agregar al array $COMMON_PASSWORDS:
$COMMON_PASSWORDS = array_merge($COMMON_PASSWORDS, [
    'miempresa', 'miempresa123', 'ruc20123456789',
    'factura2025', 'ventas2025', // Específicas del negocio
]);
```

### Deshabilitar Expiración

```php
// En password_policy.php
define('PASSWORD_EXPIRATION_DAYS', 0);  // 0 = sin expiración
```

---

## 📈 MONITOREO Y REPORTES

### Consultas SQL Útiles

#### Ver Estado de Contraseñas

```sql
-- Estado general de todas las contraseñas
SELECT * FROM v_usuario_password_status;

-- Solo contraseñas expiradas
SELECT login, nombre, dias_desde_cambio, estado_password
FROM v_usuario_password_status
WHERE estado_password = 'Expirada';

-- Usuarios con contraseñas legacy (no migradas)
SELECT login, nombre, tipo_hash
FROM v_usuario_password_status
WHERE tipo_hash LIKE '%legacy%';

-- Top 5 usuarios con contraseñas más antiguas
SELECT login, nombre, dias_desde_cambio, ultima_actualizacion
FROM v_usuario_password_status
ORDER BY dias_desde_cambio DESC
LIMIT 5;
```

#### Historial de Cambios

```sql
-- Ver historial de un usuario
SELECT
    h.id_history,
    h.fecha_cambio,
    DATEDIFF(NOW(), h.fecha_cambio) AS dias_desde_cambio
FROM usuario_password_history h
WHERE h.idusuario = 1
ORDER BY h.fecha_cambio DESC;

-- Contar cambios por usuario
SELECT
    u.login,
    u.nombre,
    COUNT(h.id_history) AS total_cambios
FROM usuario u
LEFT JOIN usuario_password_history h ON u.idusuario = h.idusuario
GROUP BY u.idusuario
ORDER BY total_cambios DESC;
```

### Dashboard de Seguridad

```sql
-- Estadísticas generales
SELECT
    COUNT(*) AS total_usuarios,
    SUM(CASE WHEN estado_password = 'Vigente' THEN 1 ELSE 0 END) AS vigentes,
    SUM(CASE WHEN estado_password = 'Por expirar' THEN 1 ELSE 0 END) AS por_expirar,
    SUM(CASE WHEN estado_password = 'Expirada' THEN 1 ELSE 0 END) AS expiradas,
    SUM(CASE WHEN tipo_hash LIKE '%legacy%' THEN 1 ELSE 0 END) AS legacy
FROM v_usuario_password_status;
```

---

## 🔐 MEJORES PRÁCTICAS

### ✅ HACER (DO)

1. **Educar a los Usuarios**
   - Explicar por qué las contraseñas fuertes son importantes
   - Mostrar ejemplos de contraseñas buenas vs. malas
   - Proveer indicador visual de fortaleza en tiempo real

2. **Usar Generador de Contraseñas**
   - Ofrecer botón "Generar contraseña segura"
   - Permitir copiar al portapapeles
   - Mostrar contraseña generada temporalmente

3. **Notificar Expiración**
   - Avisar 15 días antes de expiración
   - Enviar recordatorios por email
   - Permitir cambio proactivo

4. **Auditar Regularmente**
   - Revisar usuarios con contraseñas legacy
   - Identificar usuarios que no cambian contraseñas
   - Monitorear intentos de reutilización

5. **Facilitar el Cambio**
   - Proceso simple y claro
   - Validación en tiempo real
   - Mensajes de error específicos

### ❌ NO HACER (DON'T)

1. **NO Almacenar Contraseñas en Texto Plano**
   - ❌ Nunca guardar passwords sin hashear
   - ❌ No loggear contraseñas en archivos
   - ❌ No enviar por email sin encriptar

2. **NO Ser Demasiado Restrictivo**
   - ❌ No exigir cambios muy frecuentes (< 60 días)
   - ❌ No rechazar contraseñas muy largas
   - ❌ No limitar tipos de caracteres válidos

3. **NO Reutilizar Componentes Inseguros**
   - ❌ No usar MD5/SHA1 para passwords
   - ❌ No implementar algoritmos propios
   - ❌ No omitir salt en hashing

4. **NO Complicar Sin Razón**
   - ❌ No exigir cambio de contraseña al primer login sin razón
   - ❌ No bloquear cuenta permanentemente
   - ❌ No dificultar recuperación legítima

---

## 🛠️ SOLUCIÓN DE PROBLEMAS

### Problema: "La contraseña no cumple requisitos" (pero parece correcta)

**Causa**: Espacios en blanco o caracteres invisibles

**Solución**:
```javascript
// Limpiar antes de validar
password = password.trim();
```

### Problema: "No puedes reutilizar tus últimas 5 contraseñas" (pero es nueva)

**Causa**: Error en historial o contraseña muy similar

**Solución**:
```sql
-- Ver historial del usuario
SELECT * FROM usuario_password_history WHERE idusuario = 1;

-- Limpiar historial si es necesario (con precaución)
DELETE FROM usuario_password_history WHERE idusuario = 1;
```

### Problema: Usuarios no pueden cambiar contraseña expirada

**Causa**: No hay flujo para forzar cambio

**Solución**: Implementar pantalla de cambio obligatorio en login
```php
$expiracion = verificarExpiracionPassword($_SESSION['idusuario']);
if ($expiracion['expirada']) {
    header("Location: cambiar_password_obligatorio.php");
    exit();
}
```

### Problema: El indicador de fortaleza no aparece

**Causa**: JavaScript no cargado o ID incorrecto

**Solución**:
```javascript
// Verificar que el script esté cargado
console.log(typeof initPasswordValidator); // debe ser 'function'

// Verificar IDs
console.log(document.getElementById('password'));      // debe existir
console.log(document.getElementById('password_feedback')); // debe existir
```

---

## 📚 REFERENCIAS

### Estándares y Guías
- [NIST SP 800-63B - Password Guidelines](https://pages.nist.gov/800-63-3/sp800-63b.html)
- [OWASP Password Storage Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Password_Storage_Cheat_Sheet.html)
- [OWASP Authentication Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Authentication_Cheat_Sheet.html)

### Herramientas
- [Have I Been Pwned - Compromised Passwords](https://haveibeenpwned.com/Passwords)
- [zxcvbn - Password Strength Estimator](https://github.com/dropbox/zxcvbn)

### Archivos del Sistema
- `/config/password_policy.php` - Validación backend
- `/config/password_history_table.sql` - Estructura BD
- `/public/js/password_validator.js` - Validación frontend

---

## ✅ CHECKLIST DE IMPLEMENTACIÓN

### Pre-Implementación
- [ ] Revisar y ajustar configuración en `password_policy.php`
- [ ] Decidir días de expiración (90 días recomendado)
- [ ] Preparar comunicación a usuarios sobre nuevos requisitos

### Implementación Backend
- [ ] Ejecutar script SQL para crear tabla de historial
- [ ] Verificar trigger y evento programado funcionando
- [ ] Integrar `password_policy.php` en formularios de usuario
- [ ] Implementar validación en caso `guardaryeditar`
- [ ] Registrar contraseñas en historial

### Implementación Frontend
- [ ] Incluir `password_validator.js` en formularios
- [ ] Agregar contenedor de retroalimentación (`password_feedback`)
- [ ] Agregar botones de generar y mostrar/ocultar
- [ ] Inicializar validador con `initPasswordValidator()`
- [ ] Mostrar requisitos con `getPasswordRequirementsHTML()`

### Testing
- [ ] Probar creación de usuario con contraseña débil (debe fallar)
- [ ] Probar con contraseña fuerte (debe funcionar)
- [ ] Verificar que no se puedan reutilizar contraseñas
- [ ] Comprobar migración de contraseñas legacy
- [ ] Validar indicador de fortaleza en tiempo real

### Post-Implementación
- [ ] Comunicar nuevos requisitos a usuarios
- [ ] Forzar cambio de contraseñas legacy
- [ ] Configurar notificaciones de expiración
- [ ] Establecer revisión mensual de contraseñas expiradas
- [ ] Documentar procedimiento para excepciones

---

**Última actualización**: 2025-10-10
**Versión del documento**: 1.0
**Sistema**: Facturación v3.3
