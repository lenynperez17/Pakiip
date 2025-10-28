# 🔐 GUÍA DE ENCRIPTACIÓN DE DATOS SENSIBLES
## Sistema de Facturación v3.3

---

## 📋 RESUMEN EJECUTIVO

Este documento describe el sistema de encriptación implementado en el sistema de facturación para proteger datos sensibles.

### ✅ Estado Actual de Implementación

| Tipo de Dato | Método de Protección | Estado | Archivo |
|--------------|---------------------|--------|---------|
| **Contraseñas** | bcrypt (cost 12) | ✅ Implementado | `/ajax/usuario.php` |
| **Secrets 2FA** | AES-256-CBC | ✅ Implementado | `/config/2fa_helper.php` |
| **Códigos Respaldo 2FA** | AES-256-CBC | ✅ Implementado | `/config/2fa_helper.php` |
| **Datos Sensibles Generales** | AES-256-CBC | ✅ Helper disponible | `/config/encryption_helper.php` |

---

## 🔑 1. SISTEMA DE CONTRASEÑAS

### Algoritmo: bcrypt (PASSWORD_BCRYPT)
- **Cost Factor**: 12 (2^12 = 4,096 iteraciones)
- **Migración Automática**: SHA1/SHA256 → bcrypt
- **Ubicación**: `/ajax/usuario.php`

### Flujo de Contraseñas

#### Creación de Usuario Nuevo:
```php
// Línea 41 de usuario.php
$clavehash = password_hash($clave, PASSWORD_BCRYPT, ['cost' => 12]);
```

#### Login y Verificación:
```php
// Líneas 388-424 de usuario.php
if (substr($fetch->clave, 0, 4) === '$2y$') {
    // Verificar bcrypt
    $password_valido = password_verify($clavea, $fetch->clave);
} else {
    // Migrar automáticamente SHA1/SHA256 → bcrypt
    if (sha1($clavea) === $fetch->clave || hash('sha256', $clavea) === $fetch->clave) {
        $password_valido = true;
        $nuevo_hash = password_hash($clavea, PASSWORD_BCRYPT, ['cost' => 12]);
        $usuario->actualizarPassword($fetch->idusuario, $nuevo_hash);
    }
}
```

### Ventajas de bcrypt:
✅ Resistente a ataques de fuerza bruta (cost factor ajustable)
✅ Salt automático incorporado
✅ Diseñado específicamente para passwords
✅ Estándar de la industria

### ⚠️ IMPORTANTE: NUNCA usar este método para contraseñas
```php
// ❌ INCORRECTO
$clave = hash('sha256', $password);
$clave = hash('sha1', $password);
$clave = md5($password);

// ✅ CORRECTO
$clave = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
```

---

## 🔐 2. SISTEMA DE ENCRIPTACIÓN 2FA

### Algoritmo: AES-256-CBC
- **Ubicación**: `/config/2fa_helper.php`
- **Uso**: Secrets TOTP y códigos de respaldo

### Funciones Específicas:
```php
// Encriptar secret 2FA
$secret_encrypted = encriptar2FASecret($secret);

// Desencriptar secret 2FA
$secret_plain = desencriptar2FASecret($secret_encrypted);
```

### Características:
- IV aleatorio de 16 bytes por encriptación
- Clave derivada de ENCRYPTION_KEY con SHA-256
- Formato: base64(IV + datos_encriptados)

---

## 🛡️ 3. SISTEMA DE ENCRIPTACIÓN GENERAL

### Algoritmo: AES-256-CBC
- **Ubicación**: `/config/encryption_helper.php`
- **Uso**: Cualquier dato sensible (tarjetas, cuentas bancarias, etc.)

### Funciones Disponibles:

#### Encriptar Dato Individual:
```php
require_once "../config/encryption_helper.php";

$numero_tarjeta = "4111111111111111";
$tarjeta_encriptada = encriptarDato($numero_tarjeta);

// Guardar en BD
$sql = "INSERT INTO metodo_pago (numero_tarjeta) VALUES (?)";
ejecutarConsultaPreparada($sql, "s", [$tarjeta_encriptada]);
```

#### Desencriptar Dato:
```php
$row = $resultado->fetch_object();
$numero_tarjeta = desencriptarDato($row->numero_tarjeta);

// Mostrar enmascarado en UI
echo enmascararDato($numero_tarjeta, 4); // ************1111
```

#### Encriptar Múltiples Campos:
```php
$datos_sensibles = [
    'numero_cuenta' => '191-123456789',
    'cci' => '00219100012345678901',
    'titular' => 'Juan Pérez'
];

$datos_encriptados = encriptarArray($datos_sensibles);

// Guardar todos a la vez
$sql = "UPDATE empresa SET numero_cuenta=?, cci=?, titular_cuenta=? WHERE idempresa=?";
ejecutarConsultaPreparada($sql, "sssi", [
    $datos_encriptados['numero_cuenta'],
    $datos_encriptados['cci'],
    $datos_encriptados['titular'],
    $idempresa
]);
```

#### Búsqueda por Dato Encriptado:
```php
// Usar hash para búsquedas sin desencriptar
$numero_tarjeta_buscar = "4111111111111111";
$hash_busqueda = hashearParaBusqueda($numero_tarjeta_buscar);

$sql = "SELECT * FROM metodo_pago WHERE hash_tarjeta = ?";
$resultado = ejecutarConsultaPreparada($sql, "s", [$hash_busqueda]);
```

---

## 🔧 4. CONFIGURACIÓN DE CLAVE DE ENCRIPTACIÓN

### Variable de Entorno: ENCRYPTION_KEY

#### Desarrollo Local (.env):
```bash
ENCRYPTION_KEY=tu_clave_super_secreta_de_desarrollo_cambiar_en_produccion
```

#### Producción (servidor):
```bash
# Linux/Apache - Archivo .htaccess
SetEnv ENCRYPTION_KEY "clave_produccion_ultra_secreta_256_bits_random"

# Linux/Nginx - Archivo de configuración
fastcgi_param ENCRYPTION_KEY "clave_produccion_ultra_secreta_256_bits_random";

# Linux/systemd - Archivo de servicio
Environment="ENCRYPTION_KEY=clave_produccion_ultra_secreta_256_bits_random"
```

### Generar Clave Segura:
```bash
# Opción 1: OpenSSL (recomendado)
openssl rand -base64 32

# Opción 2: PHP
php -r "echo bin2hex(random_bytes(32));"

# Opción 3: Linux /dev/urandom
head -c 32 /dev/urandom | base64
```

### ⚠️ SEGURIDAD DE LA CLAVE:
- ✅ Mínimo 32 caracteres aleatorios
- ✅ Diferente entre desarrollo y producción
- ✅ Nunca commitear en Git
- ✅ Rotar cada 6-12 meses
- ✅ Almacenar backup seguro

---

## 📊 5. DECISIONES DE QUÉ ENCRIPTAR

### ✅ DEBE Encriptarse:
- Contraseñas (con bcrypt, NO reversible)
- Números de tarjetas de crédito
- CVV de tarjetas
- Números de cuentas bancarias
- CCI (Código de Cuenta Interbancario)
- Secrets 2FA y códigos de respaldo
- Claves API de terceros
- Tokens de autenticación

### ⚠️ PUEDE Encriptarse (según compliance):
- Emails personales
- Números de teléfono
- Direcciones completas
- Datos biométricos

### ❌ NO Encriptar:
- Precios de productos (datos de negocio)
- Totales de facturas (públicos ante SUNAT)
- RUC/DNI (públicos en comprobantes)
- Nombres comerciales
- Datos ya públicos

---

## 🔄 6. MIGRACIÓN DE DATOS LEGACY

### Sistema Híbrido Implementado:

El sistema soporta migración transparente de contraseñas antiguas:

```
SHA1 (40 caracteres)  ──┐
                        ├──> LOGIN ──> Migración Automática ──> bcrypt
SHA256 (64 caracteres)──┘
```

### Estadísticas de Migración:
```sql
-- Ver usuarios con contraseñas legacy
SELECT
    idusuario,
    login,
    CASE
        WHEN clave LIKE '$2y$%' THEN 'bcrypt (migrado)'
        WHEN LENGTH(clave) = 40 THEN 'SHA1 (legacy)'
        WHEN LENGTH(clave) = 64 THEN 'SHA256 (legacy)'
        ELSE 'desconocido'
    END AS tipo_hash,
    LENGTH(clave) AS longitud_hash
FROM usuario
ORDER BY tipo_hash;
```

### Log de Migraciones:
```bash
# Ver log de migraciones automáticas
tail -f /var/log/apache2/error.log | grep "Password migrado"

# Ejemplo de salida:
# Password migrado de SHA1 a bcrypt para usuario: admin
# Password migrado de SHA256 a bcrypt para usuario: vendedor1
```

---

## 🛠️ 7. HERRAMIENTAS Y UTILIDADES

### Enmascaramiento para UI:
```php
// Mostrar solo últimos 4 dígitos
$numero_tarjeta = desencriptarDato($tarjeta_encriptada);
echo enmascararDato($numero_tarjeta, 4); // ************1111

// Personalizar máscara
echo enmascararDato("juan@email.com", 6, 'X'); // XXXXXXXXil.com
```

### Validación de Datos Encriptados:
```php
// Verificar si un dato puede desencriptarse
if (validarDatoEncriptado($dato_encriptado)) {
    $dato_plano = desencriptarDato($dato_encriptado);
} else {
    error_log("Dato corrupto o clave incorrecta");
}
```

### Hash para Búsquedas:
```php
// Guardar hash junto con dato encriptado
$numero_tarjeta = "4111111111111111";
$tarjeta_encriptada = encriptarDato($numero_tarjeta);
$hash_tarjeta = hashearParaBusqueda($numero_tarjeta);

// BD: dos columnas
// - numero_tarjeta: encriptado (para mostrar)
// - hash_tarjeta: hash (para buscar)

// Buscar sin desencriptar
$hash_busqueda = hashearParaBusqueda("4111111111111111");
$sql = "SELECT * FROM metodo_pago WHERE hash_tarjeta = ?";
```

---

## 📈 8. MEJORES PRÁCTICAS

### ✅ DO (Hacer):
1. **Usar bcrypt para contraseñas** siempre
2. **Generar IV aleatorio** para cada encriptación AES
3. **Validar entrada** antes de encriptar
4. **Loggear migraciones** de hashes legacy
5. **Rotar claves** periódicamente (6-12 meses)
6. **Enmascarar datos** en UI y logs
7. **Usar prepared statements** siempre
8. **Implementar auditoría** de accesos a datos sensibles

### ❌ DON'T (No hacer):
1. **NO usar MD5 o SHA1** para contraseñas
2. **NO reutilizar IVs** en AES
3. **NO loggear datos desencriptados**
4. **NO hardcodear claves** en código
5. **NO commitear ENCRYPTION_KEY** en Git
6. **NO desencriptar** sin necesidad real
7. **NO mostrar datos completos** en UI (enmascarar)
8. **NO almacenar claves** en base de datos

---

## 🔍 9. AUDITORÍA Y MONITOREO

### Logs de Encriptación:
```bash
# Ver migraciones de contraseñas
grep "Password migrado" /var/log/apache2/error.log

# Ver errores de encriptación
grep "Error al encriptar\|Error al desencriptar" /var/log/apache2/error.log
```

### Queries de Monitoreo:
```sql
-- Usuarios con contraseñas no migradas
SELECT idusuario, login, 'SHA1' AS tipo
FROM usuario
WHERE LENGTH(clave) = 40
UNION
SELECT idusuario, login, 'SHA256' AS tipo
FROM usuario
WHERE LENGTH(clave) = 64;

-- Últimos accesos a datos sensibles (si se implementa auditoría)
SELECT *
FROM audit_log
WHERE tipo_operacion IN ('DECRYPT_SENSITIVE_DATA', 'VIEW_CREDIT_CARD')
ORDER BY fecha_hora DESC
LIMIT 20;
```

---

## 📚 10. REFERENCIAS Y RECURSOS

### Estándares y Documentación:
- [OWASP - Password Storage Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Password_Storage_Cheat_Sheet.html)
- [NIST SP 800-63B - Digital Identity Guidelines](https://pages.nist.gov/800-63-3/sp800-63b.html)
- [PHP password_hash() Documentation](https://www.php.net/manual/en/function.password-hash.php)
- [AES-256-CBC OpenSSL Documentation](https://www.openssl.org/docs/)

### Compliance:
- **PCI-DSS**: Encriptación obligatoria para datos de tarjetas
- **GDPR**: Encriptación recomendada para PII (Personally Identifiable Information)
- **SUNAT Perú**: Contraseñas y claves API deben estar encriptadas

---

## 🚀 11. IMPLEMENTACIÓN FUTURA

### Roadmap de Encriptación:

#### Fase 1: ✅ COMPLETADO
- [x] Migración de contraseñas a bcrypt
- [x] Sistema 2FA con encriptación
- [x] Helper genérico de encriptación

#### Fase 2: 📋 PLANIFICADO (si se requiere)
- [ ] Encriptar datos bancarios en tabla `empresa`
- [ ] Encriptar métodos de pago si se implementan
- [ ] Rotación automática de ENCRYPTION_KEY
- [ ] Key Management System (KMS) para producción

#### Fase 3: 🔮 FUTURO (opcional)
- [ ] Encriptación a nivel de base de datos (MySQL TDE)
- [ ] Hardware Security Module (HSM) para claves
- [ ] Auditoría de accesos a datos encriptados
- [ ] Encriptación en tránsito (HTTPS enforced)

---

## 📞 SOPORTE

### Problemas Comunes:

#### Error: "Error al desencriptar dato"
**Causa**: ENCRYPTION_KEY incorrecta o cambió
**Solución**: Verificar que ENCRYPTION_KEY sea la misma usada para encriptar

#### Error: "Password migrado de SHA1 a bcrypt"
**Causa**: Es normal, migración automática funcionando
**Solución**: No requiere acción, es el comportamiento esperado

#### Error: "Dato corrupto o clave incorrecta"
**Causa**: Dato en BD corrupto o formato incorrecto
**Solución**: Re-encriptar dato o validar estructura de BD

---

**Última actualización**: 2025-10-10
**Versión del documento**: 1.0
**Sistema**: Facturación v3.3
