@echo off
echo 🚀 Iniciando NYNEL CRM en Windows...
echo.

echo 📋 Verificando Docker...
docker --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Error: Docker no está instalado o no está ejecutándose
    echo 💡 Instala Docker Desktop desde: https://www.docker.com/products/docker-desktop/
    pause
    exit /b 1
)

echo ✅ Docker encontrado
echo.

echo 🧹 Limpiando contenedores anteriores...
docker-compose down -v 2>nul

echo 🐳 Construyendo e iniciando servicios...
docker-compose up --build -d

echo.
echo ⏳ Esperando que los servicios estén listos...
timeout /t 10 >nul

echo.
echo 🔧 Inicializando base de datos y datos de prueba...
docker-compose exec -T backend python manage.py shell -c "exec(open('init_project.py').read())"

echo.
echo 🎉 ¡CRM iniciado correctamente!
echo.
echo 🌐 Accede a la aplicación:
echo    Frontend: http://localhost:3000
echo    Admin:    http://localhost:8000/admin
echo    API Docs: http://localhost:8000/swagger/
echo.
echo 🔑 Credenciales:
echo    Usuario: admin
echo    Contraseña: admin123
echo.
echo ⚠️  Para detener: docker-compose down
echo.
pause