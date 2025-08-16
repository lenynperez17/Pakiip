# 🚀 NYNEL CRM - Guía de Instalación para Windows

## 📋 Requisitos Previos

**Recomendado: Docker (Más Fácil)**
- Windows 10/11 Pro, Enterprise o Education
- Docker Desktop con integración WSL2 activada

**Alternativa: Instalación Local**
- Python 3.9+, Node.js 16+, PostgreSQL 12+

## 🐳 Instalación con Docker (Recomendado)

### 1. Instalar Docker Desktop
- Descargar: https://www.docker.com/products/docker-desktop/
- Instalar y reiniciar Windows
- Activar integración WSL2 en configuración
- Verificar: `docker --version` en PowerShell

### 2. Ejecutar el Proyecto

**Opción A: Script Automático**
```cmd
# Hacer doble clic en el archivo:
start-windows.bat
```

**Opción B: Manual**
```cmd
# Abrir PowerShell o CMD en el directorio del proyecto
cd "CRM - Calidad de Software"

# Primera vez - construir todo
docker-compose up --build

# En otra terminal, inicializar datos:
docker-compose exec backend python manage.py shell -c "exec(open('init_project.py').read())"
```

### 4. ✅ Acceder a la Aplicación
- **🎨 Frontend**: http://localhost:3000
- **🔧 Admin Django**: http://localhost:8000/admin (admin / admin123)
- **📚 API Docs**: http://localhost:8000/swagger/
- **🔌 API Backend**: http://localhost:8000/api/v1/

### 5. Comandos Útiles
```bash
# Ver logs
docker-compose logs

# Parar todo
docker-compose down

# Reiniciar
docker-compose restart

# Limpiar y empezar de nuevo
docker-compose down -v && docker-compose up --build
```

## 💻 Instalación Local (Alternativa)

### Backend
```bash
cd nynel-crm/backend
python -m venv venv
# Windows: venv\Scripts\activate
# Linux/Mac: source venv/bin/activate
pip install -r requirements.txt
cp .env.example .env
python manage.py migrate
python manage.py shell -c "exec(open('init_project.py').read())"
python manage.py runserver
```

### Frontend
```bash
cd nynel-crm/frontend
npm install
cp .env.example .env
npm start
```

## 🔧 Estructura del Proyecto

```
CRM - Calidad de Software/
├── 📁 nynel-crm/
│   ├── 📁 backend/          # Django API
│   │   ├── 📁 apps/         # Módulos del CRM
│   │   ├── 📄 manage.py     # Django manager
│   │   └── 📄 init_project.py # Script de inicialización
│   └── 📁 frontend/         # React App
│       ├── 📁 src/          # Código fuente
│       └── 📄 package.json  # Dependencias
├── 📄 docker-compose.yml   # Configuración Docker
└── 📄 GUIA_INSTALACION.md  # Esta guía
```

## 🛠️ Módulos Implementados

- ✅ **Contactos**: Gestión de cuentas, contactos y actividades
- ✅ **Oportunidades**: Pipeline de ventas y cotizaciones  
- ✅ **Marketing**: Campañas y gestión de leads
- ✅ **Tickets**: Sistema de soporte y base de conocimientos
- ✅ **Reportes**: Dashboards y análisis

## 🆘 Solución de Problemas

**Error Docker**: Verificar que Docker Desktop esté ejecutándose

**Puerto ocupado**: Cambiar puertos en docker-compose.yml

**Datos no aparecen**: Ejecutar el script de inicialización

## 📞 Contacto

- **Desarrolladores**: Lenyn Perez, Randy Pariasca
- **Universidad**: UCV Virtual
- **Curso**: Calidad de Software