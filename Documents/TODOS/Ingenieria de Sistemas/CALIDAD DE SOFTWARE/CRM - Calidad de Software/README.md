# 🏢 NYNEL CRM System

Sistema de Gestión de Relaciones con el Cliente desarrollado para NYNEL E.I.R.L.

## 🚀 Inicio Rápido para Windows

### Opción 1: Script Automático (Recomendado)
```cmd
# Hacer doble clic en:
start-windows.bat
```

### Opción 2: Manual (Si hay problemas)
```cmd
# 1. Limpiar contenedores anteriores
docker-compose down -v

# 2. Construir e iniciar servicios
docker-compose up --build

# 3. En otra terminal, inicializar datos:
docker-compose exec backend python manage.py shell -c "exec(open('init_project.py').read())"
```

### 🔍 Ver Logs
```cmd
# Para ver logs en tiempo real:
logs-windows.bat
# o manualmente:
docker-compose logs -f
```

## 🌐 Acceso

- **🎨 Frontend**: http://localhost:3000
- **🔧 Admin**: http://localhost:8000/admin (admin / admin123)  
- **📚 API Docs**: http://localhost:8000/swagger/

## 📋 Requisitos

- **Windows 10/11** con Docker Desktop
- **Docker Desktop** con integración WSL2 activada

## 🛠️ Módulos Implementados

### 👥 **Contactos y Cuentas**
- ✅ Gestión completa de contactos con información personal y profesional
- ✅ Administración de cuentas empresariales con datos fiscales
- ✅ CRUD completo con formularios validados
- ✅ Búsqueda y filtrado avanzado

### 💼 **Oportunidades de Venta**
- ✅ Pipeline visual de ventas con etapas personalizables
- ✅ Gestión de probabilidades y montos estimados
- ✅ Seguimiento de fechas de cierre y alertas SLA
- ✅ Análisis de conversión y reportes de rendimiento

### 📧 **Marketing y Leads**
- ✅ Gestión de campañas multicanal (email, web, eventos)
- ✅ Seguimiento de leads con scoring automático
- ✅ Plantillas de email personalizables
- ✅ ROI de campañas y métricas de conversión

### 🎫 **Tickets de Soporte**
- ✅ Sistema completo de tickets con prioridades
- ✅ SLA automático y alertas de vencimiento
- ✅ Calificación de satisfacción del cliente
- ✅ Métricas de tiempo de respuesta y resolución

### 📊 **Reportes y Análisis**
- ✅ Dashboard ejecutivo con KPIs principales
- ✅ Reportes de ventas con gráficos interactivos
- ✅ Análisis de soporte y satisfacción
- ✅ Exportación en PDF y Excel

### 📋 **Actividades**
- ✅ Gestión de tareas, llamadas y reuniones
- ✅ Calendario integrado con recordatorios
- ✅ Seguimiento de actividades por contacto/cuenta
- ✅ Estados personalizables y prioridades

## 🔐 **Usuarios Demo**

El sistema incluye 4 usuarios de prueba:

| Usuario | Contraseña | Rol | Descripción |
|---------|------------|-----|-------------|
| `admin` | `admin123` | Administrador | Acceso completo al sistema |
| `vendedor1` | `vendedor123` | Vendedor | Gestión de oportunidades y contactos |
| `marketing1` | `marketing123` | Marketing | Campañas y gestión de leads |
| `soporte1` | `soporte123` | Soporte | Tickets y atención al cliente |

## 🎨 **Características Técnicas**

### Frontend (React)
- ✅ Material-UI para diseño profesional
- ✅ Redux Toolkit para gestión de estado
- ✅ Autenticación JWT funcional
- ✅ Formularios validados con manejo de errores
- ✅ Componentes reutilizables y modulares
- ✅ Responsive design para móviles

### Backend (Django)
- ✅ API REST completa y documentada
- ✅ Modelos relacionales optimizados
- ✅ Sistema de autenticación robusto
- ✅ Admin personalizado con funcionalidades avanzadas
- ✅ Scripts de inicialización de datos

### DevOps
- ✅ Dockerización completa del stack
- ✅ Docker Compose para desarrollo
- ✅ Scripts automatizados para Windows
- ✅ Base de datos PostgreSQL
- ✅ Nginx para servir archivos estáticos

## 📚 Documentación

Ver `GUIA_INSTALACION.md` para instrucciones detalladas.

## 👥 Desarrolladores

- **Lenyn Mauricio Perez Araujo** - lepereza@ucvvirtual.edu.pe
- **Randy Yordi Pariasca Lopez** - rpariasca@ucvvirtual.edu.pe

**Universidad César Vallejo - Calidad de Software**