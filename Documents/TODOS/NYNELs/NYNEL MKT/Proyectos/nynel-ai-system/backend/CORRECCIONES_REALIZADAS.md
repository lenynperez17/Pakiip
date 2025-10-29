# 🔧 CORRECCIONES REALIZADAS - SISTEMA DE COTIZACIONES

**Fecha:** 29 de octubre de 2025
**Problema reportado por cliente:** Bot manda cotizaciones PDF cuando NO las pide

---

## 🚨 PROBLEMA IDENTIFICADO

**Ubicación:** `src/services/master-conversational-ai.service.ts` línea 510

**Comportamiento incorrecto:**
```typescript
// ❌ ANTES: Generaba PDF SIEMPRE que detectara intent "quotation"
if (decision.intentType === 'quotation') {
  // Generaba PDF automáticamente sin verificar si el cliente lo pidió
  const quotationResult = await quotationService.generateQuotation(...);
}
```

**Casos problemáticos detectados:**
1. Cliente pregunta: "¿Cuánto cuesta una app móvil?" → ❌ Generaba PDF (incorrecto)
2. Cliente pregunta: "¿Qué servicios ofrecen?" → ❌ Generaba PDF (incorrecto)
3. Cliente pregunta: "¿Cuánto cobran otras agencias?" → ❌ Generaba PDF (incorrecto)

---

## ✅ SOLUCIÓN IMPLEMENTADA

### 1. Agregado campo `generatePdf` a la decisión del AI

**Archivo:** `src/services/master-conversational-ai.service.ts`
**Línea:** 43

```typescript
interface IntelligentDecision {
  intentType: 'calendar' | 'quotation' | 'knowledge' | 'chat' | 'other';
  understanding: string;
  confidence: number;
  reasoning: string;
  suggestedResponse: string;
  generatePdf?: boolean; // ✅ NUEVO: Control explícito de generación de PDF
  // ... resto de campos
}
```

### 2. Modificado system prompt con REGLAS CLARAS

**Ubicación:** Líneas 300-327

**Reglas agregadas:**

#### ✅ `generatePdf: true` SOLO cuando el cliente:
1. Dice explícitamente "cotización" o "presupuesto"
2. Pide "enviar cotización", "mandar presupuesto"
3. Dice "quiero una cotización formal"
4. Solicita documento oficial de precios
5. Ya dio detalles específicos del proyecto y pide precio final

#### ❌ `generatePdf: false` cuando el cliente:
1. Solo pregunta "¿cuánto cuesta X?"
2. Pregunta rangos de precios
3. Compara precios con competencia
4. Pregunta "¿qué servicios ofrecen?"
5. Conversación inicial de descubrimiento
6. Solo pide información general
7. Pregunta "cómo funciona" o proceso
8. Aún no tiene claro lo que necesita

### 3. Agregados ejemplos ESPECÍFICOS en el prompt

**EJEMPLO 1 - Pregunta de Precio (SIN PDF):**
```
Usuario: "Cuánto cuesta una app móvil?"
→ generatePdf: false
→ Response: Dar rangos de precio y preguntar detalles
→ ❌ NO generar PDF
```

**EJEMPLO 6 - Solicitud EXPLÍCITA (CON PDF):**
```
Usuario: "Necesito una cotización formal para mi proyecto de e-commerce"
→ generatePdf: true
→ Response: Preparar cotización formal
→ ✅ SÍ generar PDF
```

**EJEMPLO 7 - Solicitud de Presupuesto (CON PDF):**
```
Usuario: "Quiero un presupuesto para desarrollar una app móvil para mi restaurante"
→ generatePdf: true
→ ✅ SÍ generar PDF
```

### 4. Modificada lógica de generación de PDF

**Ubicación:** Línea 559

```typescript
// ✅ DESPUÉS: Solo genera PDF si generatePdf === true
if (decision.intentType === 'quotation' && decision.generatePdf === true) {
  logger.info('💰 [MASTER AI] Cliente solicita cotización formal - Generando PDF...');
  // Genera PDF
}
```

### 5. Agregado caso para quotation SIN PDF

**Ubicación:** Líneas 623-636

```typescript
// Nuevo caso: quotation sin PDF (solo información)
if (decision.intentType === 'quotation' && decision.generatePdf === false) {
  logger.info('💬 [MASTER AI] Respondiendo sobre precios (sin generar PDF)...');

  return {
    success: true,
    response: decision.suggestedResponse, // Solo texto informativo
    intentType: 'quotation',
    confidence: decision.confidence,
    actionTaken: 'quotation_info_only',
  };
}
```

---

## 📊 COMPORTAMIENTO ESPERADO DESPUÉS DEL FIX

| Mensaje del Cliente | Debería generar PDF? | Respuesta esperada |
|---------------------|----------------------|-------------------|
| "¿Cuánto cuesta un sitio web?" | ❌ NO | Dar rangos de precio + preguntar detalles |
| "¿Qué servicios ofrecen?" | ❌ NO | Listar servicios + ofrecer ayuda |
| "¿Cuánto cobran otras agencias?" | ❌ NO | Información de mercado + destacar valor |
| "Como funciona el servicio?" | ❌ NO | Explicar proceso + preguntar interés |
| "Necesito una cotización" | ✅ SÍ | Preparar cotización + generar PDF |
| "Quiero un presupuesto para X" | ✅ SÍ | Confirmar proyecto + generar PDF |
| "Envíame cotización formal" | ✅ SÍ | Generar PDF inmediatamente |

---

## 🚀 INSTRUCCIONES DE DEPLOYMENT

### Opción 1: Usando script automatizado

```bash
cd /mnt/c/Users/Lenyn/Documents/TODOS/NYNELs/NYNEL\ MKT/Proyectos/nynel-ai-system/backend

# Dar permisos de ejecución
chmod +x deploy_fix.sh

# Ejecutar deployment
./deploy_fix.sh
```

### Opción 2: Manual (paso a paso)

```bash
# 1. Subir archivo corregido
scp src/services/master-conversational-ai.service.ts root@147.79.74.193:/var/www/nynel-ai-system/backend/src/services/

# 2. Conectar al VPS y reiniciar
ssh root@147.79.74.193
cd /var/www/nynel-ai-system/backend
pm2 restart nynel-ai-backend

# 3. Verificar logs
pm2 logs nynel-ai-backend --lines 50
```

---

## ✅ VERIFICACIÓN POST-DEPLOYMENT

### Casos de prueba:

1. **Mensaje: "¿Cuánto cuesta una app móvil?"**
   - ✅ Esperado: Responde con rangos, NO genera PDF
   - ❌ Error: Si genera PDF con link

2. **Mensaje: "Necesito una cotización para mi proyecto"**
   - ✅ Esperado: Genera PDF con cotización
   - ❌ Error: Si solo responde sin PDF

3. **Mensaje: "Qué servicios ofrecen?"**
   - ✅ Esperado: Lista servicios, NO genera PDF
   - ❌ Error: Si genera PDF

### Revisar logs:

```bash
# Buscar mensajes de cotización
pm2 logs nynel-ai-backend | grep "cotización"

# Verificar si se está usando el nuevo campo generatePdf
pm2 logs nynel-ai-backend | grep "generatePdf"

# Ver casos donde NO se genera PDF
pm2 logs nynel-ai-backend | grep "quotation_info_only"
```

---

## 📝 OTROS FIXES REALIZADOS EN ESTA SESIÓN

### 1. ✅ Corregido puerto en quotation.service.ts (línea 80)
```typescript
// ANTES: http://147.79.74.193:3000 (puerto incorrecto)
// DESPUÉS: http://147.79.74.193:3001 (puerto correcto)
```

### 2. ✅ Corregido rate limiter en app.ts
```typescript
// Agregado keyGenerator personalizado para manejar IPs de proxy
keyGenerator: (req) => {
  const forwarded = req.headers['x-forwarded-for'];
  if (forwarded) {
    const ips = Array.isArray(forwarded) ? forwarded[0] : forwarded;
    return ips.split(',')[0].trim();
  }
  return req.ip || 'unknown';
}
```

---

## ⚠️ NOTAS IMPORTANTES

1. **Dominio/SSL pendiente:** El usuario mencionó que tiene dominio con SSL. Actualizar `BASE_URL` en `.env` cuando esté disponible.

2. **Monitoring recomendado:** Después del deployment, monitorear las primeras 24 horas para verificar que los cambios funcionan correctamente.

3. **Backup realizado:** El código anterior está en Git (commit previo), se puede revertir si hay problemas.

---

## 📞 CONTACTO

Si tienes algún problema con el deployment o necesitas ajustes adicionales, revisa los logs y documenta los casos específicos que fallen.

**Archivos modificados:**
- ✅ `src/services/master-conversational-ai.service.ts` (CRÍTICO)
- ✅ `src/services/quotation.service.ts` (Fix de puerto)
- ✅ `src/app.ts` (Fix de rate limiter)

---

**FIN DEL DOCUMENTO**
