<?php
/**
 * HEADERS DE SEGURIDAD HTTP
 * Este archivo configura headers de seguridad esenciales para proteger la aplicación
 * contra ataques comunes como XSS, Clickjacking, MIME sniffing, etc.
 *
 * IMPORTANTE: Este archivo debe ser incluido al inicio de cada página
 * Se incluye automáticamente en header.php
 */

// Prevenir que este archivo sea accedido directamente
if (!defined('SECURITY_HEADERS_LOADED')) {
    define('SECURITY_HEADERS_LOADED', true);

    // X-Frame-Options: Previene ataques de clickjacking
    // DENY = No permite que la página sea mostrada en un iframe
    header("X-Frame-Options: DENY");

    // X-Content-Type-Options: Previene MIME type sniffing
    // Los navegadores no intentarán adivinar el tipo MIME, usarán el declarado
    header("X-Content-Type-Options: nosniff");

    // X-XSS-Protection: Protección adicional contra XSS en navegadores antiguos
    // mode=block detiene la carga de la página si detecta XSS
    header("X-XSS-Protection: 1; mode=block");

    // Referrer-Policy: Controla qué información de referrer se envía
    // strict-origin-when-cross-origin solo envía el origin en requests cross-origin
    header("Referrer-Policy: strict-origin-when-cross-origin");

    // Permissions-Policy: Controla qué features del navegador pueden usarse
    // Deshabilitamos features peligrosas como geolocation, microphone, camera
    header("Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=()");

    // Content-Security-Policy: Previene XSS y data injection attacks
    // Esta es una política básica que permite:
    // - Scripts solo del mismo origen y específicos CDNs confiables
    // - Estilos del mismo origen y inline (necesario para el sistema actual)
    // - Imágenes de cualquier origen (común en sistemas de facturación)
    // - Fuentes de CDNs confiables
    $csp = "Content-Security-Policy: ";
    $csp .= "default-src 'self'; ";
    $csp .= "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://ajax.googleapis.com https://cdn.jsdelivr.net https://cdn.datatables.net https://unpkg.com https://code.jquery.com https://cdnjs.cloudflare.com; ";
    $csp .= "style-src 'self' 'unsafe-inline' https://unpkg.com https://fonts.googleapis.com https://cdn.datatables.net https://cdn.jsdelivr.net; ";
    $csp .= "img-src 'self' data: https:; ";
    $csp .= "font-src 'self' data: https://fonts.gstatic.com https://unpkg.com; ";
    $csp .= "connect-src 'self' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net; ";
    $csp .= "frame-ancestors 'none'; ";
    $csp .= "base-uri 'self'; ";
    $csp .= "form-action 'self';";

    header($csp);

    // Strict-Transport-Security (HSTS): Fuerza HTTPS
    // NOTA: Solo se activa si el sitio se accede por HTTPS
    // max-age=31536000 = 1 año
    // includeSubDomains aplica a todos los subdominios
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
    }

    // Cache-Control para páginas autenticadas
    // Previene que páginas con información sensible se cacheen
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Pragma: no-cache");
    header("Expires: 0");
}
?>
