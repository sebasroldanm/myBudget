#!/bin/bash

# ============================================================================
# Script de inicialización completa para proyecto Laravel con Docker
# Uso: ./init.sh
# ============================================================================

# Colores para output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Función para mostrar errores y salir
error_exit() {
    echo -e "${RED}❌ Error: $1${NC}" 1>&2
    exit 1
}

# Función para mostrar pasos
step() {
    echo -e "\n${BLUE}▶ $1${NC}"
}

# Función para mostrar éxito
success() {
    echo -e "${GREEN}✓ $1${NC}"
}

# Verificar que estamos en el directorio correcto
if [ ! -f "artisan" ]; then
    error_exit "No se encuentra el archivo 'artisan'. Ejecuta este script desde la raíz del proyecto."
fi

echo -e "${GREEN}============================================================${NC}"
echo -e "${GREEN}  🚀 Inicialización completa del proyecto${NC}"
echo -e "${GREEN}============================================================${NC}"

# ============================================================================
# PASO 1: Detener contenedores existentes
# ============================================================================
step "[1/15] Deteniendo contenedores existentes..."
docker-compose down -v
success "Contenedores detenidos"

# ============================================================================
# PASO 2: Limpiar archivos temporales y cache
# ============================================================================
step "[2/15] Limpiando archivos temporales..."
rm -rf bootstrap/cache/*.php 2>/dev/null
rm -rf storage/framework/cache/data/* 2>/dev/null
rm -rf storage/framework/sessions/* 2>/dev/null
rm -rf storage/framework/views/* 2>/dev/null
rm -rf storage/logs/*.log 2>/dev/null
success "Archivos temporales limpiados"

# ============================================================================
# PASO 3: Crear estructura de directorios de Laravel
# ============================================================================
step "[3/15] Creando estructura de directorios..."
mkdir -p storage/app/public
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/testing
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p bootstrap/cache
mkdir -p public/storage
success "Estructura de directorios creada"

# ============================================================================
# PASO 4: Configurar permisos ANTES de Docker
# ============================================================================
step "[4/15] Configurando permisos (UID 1000:GID 1000)..."
echo -e "${YELLOW}Estableciendo propietario...${NC}"
sudo chown -R 1000:1000 \
    app/ \
    bootstrap/ \
    config/ \
    database/ \
    public/ \
    resources/ \
    routes/ \
    storage/ \
    tests/ \
    vendor/ 2>/dev/null || true

sudo chown 1000:1000 \
    artisan \
    composer.json \
    composer.lock \
    package.json \
    package-lock.json 2>/dev/null || true

echo -e "${YELLOW}Estableciendo permisos...${NC}"
sudo find storage bootstrap/cache -type d -exec chmod 775 {} \; 2>/dev/null || true
sudo find storage bootstrap/cache -type f -exec chmod 664 {} \; 2>/dev/null || true
sudo chmod 775 artisan 2>/dev/null || true
success "Permisos configurados correctamente"

# ============================================================================
# PASO 5: Verificar/Crear archivo .env
# ============================================================================
step "[5/15] Verificando archivo .env..."
if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        echo -e "${YELLOW}Copiando .env.example a .env...${NC}"
        cp .env.example .env
        success "Archivo .env creado desde .env.example"
    else
        error_exit "No existe .env ni .env.example"
    fi
else
    success "Archivo .env existe"
fi

# ============================================================================
# PASO 6: Construir imágenes Docker
# ============================================================================
step "[6/15] Construyendo imágenes Docker (esto puede tardar)..."
docker-compose build --no-cache || error_exit "Fallo al construir imágenes Docker"
success "Imágenes Docker construidas"

# ============================================================================
# PASO 7: Iniciar contenedores
# ============================================================================
step "[7/15] Iniciando contenedores..."
docker-compose up -d || error_exit "Fallo al iniciar contenedores"
success "Contenedores iniciados"

# ============================================================================
# PASO 8: Esperar a que los servicios estén listos
# ============================================================================
step "[8/15] Esperando a que los servicios estén listos..."
echo -e "${YELLOW}Esperando MySQL...${NC}"
sleep 5
COUNTER=0
MAX_TRIES=30
until docker-compose exec mysql mysqladmin ping -h localhost -u root -p'Wq089;Zfu3H[' --silent 2>/dev/null; do
    COUNTER=$((COUNTER+1))
    if [ $COUNTER -gt $MAX_TRIES ]; then
        error_exit "MySQL no está listo después de $MAX_TRIES intentos"
    fi
    echo -e "${YELLOW}Esperando MySQL... ($COUNTER/$MAX_TRIES)${NC}"
    sleep 2
done
success "MySQL está listo"

echo -e "${YELLOW}Esperando Redis...${NC}"
sleep 2
docker-compose exec redis redis-cli ping > /dev/null 2>&1 || error_exit "Redis no está listo"
success "Redis está listo"

echo -e "${YELLOW}Esperando PHP-FPM...${NC}"
sleep 3
success "Todos los servicios están listos"

# ============================================================================
# PASO 9: Verificar estado de contenedores
# ============================================================================
step "[9/15] Verificando estado de contenedores..."
docker-compose ps
success "Contenedores en ejecución"

# ============================================================================
# PASO 10: Instalar dependencias de Composer
# ============================================================================
step "[10/15] Instalando dependencias de Composer..."
docker-compose exec -u www-data app composer install \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    || error_exit "Fallo al instalar dependencias de Composer"
success "Dependencias de Composer instaladas"

# ============================================================================
# PASO 11: Generar APP_KEY si no existe
# ============================================================================
step "[11/15] Generando APP_KEY..."
if grep -q "APP_KEY=$" .env || ! grep -q "APP_KEY=" .env; then
    echo -e "${YELLOW}Generando nueva APP_KEY...${NC}"
    docker-compose exec -u www-data app php artisan key:generate --ansi
    success "APP_KEY generada"
else
    success "APP_KEY ya existe"
fi

# ============================================================================
# PASO 12: Crear enlace simbólico de storage
# ============================================================================
step "[12/15] Creando enlace simbólico de storage..."
# Eliminar enlace anterior si existe
docker-compose exec -u www-data app rm -f public/storage 2>/dev/null || true
docker-compose exec -u www-data app php artisan storage:link || error_exit "Fallo al crear enlace de storage"
success "Enlace simbólico de storage creado"

# ============================================================================
# PASO 13: Ejecutar migraciones (opcional)
# ============================================================================
step "[13/15] Ejecutar migraciones de base de datos..."
echo -e "${YELLOW}¿Deseas ejecutar las migraciones? (y/N):${NC} "
read -r RUN_MIGRATIONS
if [[ "$RUN_MIGRATIONS" =~ ^[Yy]$ ]]; then
    docker-compose exec -u www-data app php artisan migrate --force || echo -e "${RED}Advertencia: Las migraciones fallaron o no hay migraciones pendientes${NC}"
    success "Migraciones ejecutadas"
    
    echo -e "${YELLOW}¿Deseas ejecutar los seeders? (y/N):${NC} "
    read -r RUN_SEEDERS
    if [[ "$RUN_SEEDERS" =~ ^[Yy]$ ]]; then
        docker-compose exec -u www-data app php artisan db:seed || echo -e "${RED}Advertencia: Los seeders fallaron${NC}"
        success "Seeders ejecutados"
    fi
else
    echo -e "${YELLOW}Migraciones omitidas${NC}"
fi

# ============================================================================
# PASO 14: Instalar dependencias de npm (opcional)
# ============================================================================
step "[14/15] Instalar dependencias de npm..."
if [ -f "package.json" ]; then
    echo -e "${YELLOW}¿Deseas instalar dependencias de npm? (y/N):${NC} "
    read -r INSTALL_NPM
    if [[ "$INSTALL_NPM" =~ ^[Yy]$ ]]; then
        docker-compose exec -u www-data app npm install || error_exit "Fallo al instalar dependencias de npm"
        success "Dependencias de npm instaladas"
        
        echo -e "${YELLOW}¿Deseas compilar assets? (y/N):${NC} "
        read -r BUILD_ASSETS
        if [[ "$BUILD_ASSETS" =~ ^[Yy]$ ]]; then
            docker-compose exec -u www-data app npm run dev || echo -e "${RED}Advertencia: La compilación de assets falló${NC}"
            success "Assets compilados"
        fi
    else
        echo -e "${YELLOW}Instalación de npm omitida${NC}"
    fi
else
    echo -e "${YELLOW}No se encontró package.json, omitiendo npm${NC}"
fi

# ============================================================================
# PASO 15: Limpiar y optimizar cache
# ============================================================================
step "[15/15] Limpiando y optimizando cache..."
docker-compose exec -u www-data app php artisan config:clear
docker-compose exec -u www-data app php artisan cache:clear
docker-compose exec -u www-data app php artisan view:clear
docker-compose exec -u www-data app php artisan route:clear
success "Cache limpiado"

# ============================================================================
# INFORMACIÓN FINAL
# ============================================================================
echo -e "\n${GREEN}============================================================${NC}"
echo -e "${GREEN}  ✓ Inicialización completada exitosamente${NC}"
echo -e "${GREEN}============================================================${NC}"

echo -e "\n${BLUE}📊 Información del sistema:${NC}"
echo -e "${YELLOW}PHP:${NC}"
docker-compose exec app php -v | head -1

echo -e "\n${YELLOW}Composer:${NC}"
docker-compose exec app composer --version

echo -e "\n${YELLOW}Node.js:${NC}"
docker-compose exec app node --version

echo -e "\n${YELLOW}npm:${NC}"
docker-compose exec app npm --version

echo -e "\n${YELLOW}Xdebug:${NC}"
docker-compose exec app php -v | grep Xdebug || echo "No instalado o no habilitado"

echo -e "\n${BLUE}🌐 Servicios disponibles:${NC}"
echo -e "  ${GREEN}▶${NC} Aplicación Laravel: ${YELLOW}http://localhost:8066${NC}"
echo -e "  ${GREEN}▶${NC} MySQL:              ${YELLOW}localhost:33062${NC}"
echo -e "    └─ Database:          ${YELLOW}myBudget${NC}"
echo -e "    └─ User:              ${YELLOW}myBudget${NC}"
echo -e "    └─ Password:          ${YELLOW}mzEW3M04z5yl${NC}"
echo -e "  ${GREEN}▶${NC} Redis:              ${YELLOW}localhost:6314${NC}"

echo -e "\n${BLUE}📝 Comandos útiles:${NC}"
echo -e "  ${GREEN}Ver logs:${NC}"
echo -e "    docker-compose logs -f app"
echo -e "  ${GREEN}Entrar al contenedor:${NC}"
echo -e "    docker-compose exec -u www-data app bash"
echo -e "  ${GREEN}Ejecutar Artisan:${NC}"
echo -e "    docker-compose exec -u www-data app php artisan [comando]"
echo -e "  ${GREEN}Ejecutar Composer:${NC}"
echo -e "    docker-compose exec -u www-data app composer [comando]"
echo -e "  ${GREEN}Ejecutar npm:${NC}"
echo -e "    docker-compose exec -u www-data app npm [comando]"
echo -e "  ${GREEN}Verificar Xdebug:${NC}"
echo -e "    docker-compose exec app cat /tmp/xdebug.log"

echo -e "\n${BLUE}🛠️  Scripts auxiliares:${NC}"
if [ -f "docker-helpers.sh" ]; then
    echo -e "  ${GREEN}./docker-helpers.sh help${NC} - Ver todos los comandos disponibles"
else
    echo -e "  ${YELLOW}Considera crear el archivo docker-helpers.sh para comandos rápidos${NC}"
fi

echo -e "\n${BLUE}🔧 Para corregir permisos en el futuro:${NC}"
echo -e "  ${GREEN}./fix-permissions.sh${NC} (si existe)"
echo -e "  O manualmente:"
echo -e "  ${YELLOW}sudo chown -R 1000:1000 storage bootstrap/cache${NC}"
echo -e "  ${YELLOW}sudo chmod -R 775 storage bootstrap/cache${NC}"

echo -e "\n${GREEN}============================================================${NC}"
echo -e "${GREEN}  🎉 ¡El proyecto está listo para desarrollar!${NC}"
echo -e "${GREEN}============================================================${NC}\n"