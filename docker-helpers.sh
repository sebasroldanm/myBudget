#!/bin/bash

# docker-helpers.sh
# Helpers para trabajar con Docker en Laravel
# Uso: ./docker-helpers.sh [comando]

# Colores
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Comando base
DOCKER_EXEC="docker-compose exec -u www-data app"

case "$1" in
    # ============================================
    # ARTISAN
    # ============================================
    "artisan"|"art")
        shift
        echo -e "${BLUE}Ejecutando: php artisan $@${NC}"
        $DOCKER_EXEC php artisan "$@"
        ;;
    
    "migrate")
        echo -e "${BLUE}Ejecutando migraciones...${NC}"
        $DOCKER_EXEC php artisan migrate
        ;;
    
    "migrate-fresh")
        echo -e "${YELLOW}⚠️  Esto eliminará TODOS los datos!${NC}"
        read -p "¿Continuar? (y/N): " confirm
        if [ "$confirm" = "y" ]; then
            $DOCKER_EXEC php artisan migrate:fresh --seed
        fi
        ;;
    
    "seed")
        echo -e "${BLUE}Ejecutando seeders...${NC}"
        $DOCKER_EXEC php artisan db:seed
        ;;
    
    "optimize")
        echo -e "${BLUE}Limpiando cache...${NC}"
        $DOCKER_EXEC php artisan optimize:clear
        ;;
    
    # ============================================
    # COMPOSER
    # ============================================
    "composer"|"comp")
        shift
        echo -e "${BLUE}Ejecutando: composer $@${NC}"
        $DOCKER_EXEC composer "$@"
        ;;
    
    "composer-install"|"ci")
        echo -e "${BLUE}Instalando dependencias de Composer...${NC}"
        $DOCKER_EXEC composer install --no-interaction
        ;;
    
    "composer-update"|"cu")
        echo -e "${BLUE}Actualizando dependencias de Composer...${NC}"
        $DOCKER_EXEC composer update
        ;;
    
    # ============================================
    # NPM
    # ============================================
    "npm")
        shift
        echo -e "${BLUE}Ejecutando: npm $@${NC}"
        $DOCKER_EXEC npm "$@"
        ;;
    
    "npm-install"|"ni")
        echo -e "${BLUE}Instalando dependencias de npm...${NC}"
        $DOCKER_EXEC npm install
        ;;
    
    "npm-dev")
        echo -e "${BLUE}Compilando assets (dev)...${NC}"
        $DOCKER_EXEC npm run dev
        ;;
    
    "npm-watch")
        echo -e "${BLUE}Compilando assets (watch mode)...${NC}"
        $DOCKER_EXEC npm run dev -- --watch
        ;;
    
    "npm-build")
        echo -e "${BLUE}Compilando assets (production)...${NC}"
        $DOCKER_EXEC npm run build
        ;;
    
    # ============================================
    # BREEZE
    # ============================================
    "install-breeze")
        echo -e "${GREEN}Instalando Laravel Breeze...${NC}"
        $DOCKER_EXEC composer require laravel/breeze --dev
        echo -e "${YELLOW}Ahora ejecuta: ./docker-helpers.sh breeze-setup [blade|livewire|react|vue]${NC}"
        ;;
    
    "breeze-setup")
        STACK=${2:-blade}
        echo -e "${GREEN}Configurando Breeze con stack: $STACK${NC}"
        $DOCKER_EXEC php artisan breeze:install $STACK
        echo -e "${BLUE}Instalando dependencias npm...${NC}"
        $DOCKER_EXEC npm install
        echo -e "${BLUE}Compilando assets...${NC}"
        $DOCKER_EXEC npm run dev
        echo -e "${BLUE}Ejecutando migraciones...${NC}"
        $DOCKER_EXEC php artisan migrate
        echo -e "${GREEN}✓ Breeze instalado!${NC}"
        ;;
    
    # ============================================
    # FILAMENT
    # ============================================
    "install-filament")
        echo -e "${GREEN}Instalando Filament v3...${NC}"
        $DOCKER_EXEC composer require filament/filament:"^3.0"
        $DOCKER_EXEC php artisan filament:install --panels
        echo -e "${YELLOW}Crea un usuario admin con: ./docker-helpers.sh filament-user${NC}"
        ;;
    
    "filament-user")
        echo -e "${GREEN}Creando usuario admin de Filament...${NC}"
        $DOCKER_EXEC php artisan make:filament-user
        ;;
    
    # ============================================
    # SHELL & LOGS
    # ============================================
    "bash"|"shell"|"sh")
        echo -e "${BLUE}Entrando al contenedor...${NC}"
        docker-compose exec -u www-data app bash
        ;;
    
    "root")
        echo -e "${YELLOW}⚠️  Entrando como ROOT...${NC}"
        docker-compose exec app sh
        ;;
    
    "logs")
        echo -e "${BLUE}Mostrando logs (Ctrl+C para salir)...${NC}"
        docker-compose logs -f app
        ;;
    
    "xdebug-log")
        echo -e "${BLUE}Mostrando log de Xdebug...${NC}"
        docker-compose exec app cat /tmp/xdebug.log
        ;;
    
    # ============================================
    # MANTENIMIENTO
    # ============================================
    "restart")
        echo -e "${BLUE}Reiniciando contenedores...${NC}"
        docker-compose restart
        ;;
    
    "rebuild")
        echo -e "${BLUE}Reconstruyendo imágenes...${NC}"
        docker-compose down
        docker-compose build --no-cache
        docker-compose up -d
        ;;
    
    "fix-permissions"|"perms")
        echo -e "${BLUE}Corrigiendo permisos...${NC}"
        docker-compose exec app chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
        docker-compose exec app chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
        echo -e "${GREEN}✓ Permisos corregidos${NC}"
        ;;
    
    "fresh-start")
        echo -e "${YELLOW}⚠️  Esto eliminará TODOS los contenedores y volúmenes!${NC}"
        read -p "¿Continuar? (y/N): " confirm
        if [ "$confirm" = "y" ]; then
            docker-compose down -v
            docker-compose up -d --build
        fi
        ;;
    
    # ============================================
    # INFORMACIÓN
    # ============================================
    "status"|"ps")
        docker-compose ps
        ;;
    
    "php-info")
        docker-compose exec app php -v
        docker-compose exec app php -m
        ;;
    
    # ============================================
    # AYUDA
    # ============================================
    "help"|"--help"|"-h"|"")
        echo -e "${GREEN}=== Docker Helpers para Laravel ===${NC}\n"
        echo -e "${YELLOW}Artisan:${NC}"
        echo "  artisan [cmd]      - Ejecutar comando artisan"
        echo "  migrate            - Ejecutar migraciones"
        echo "  migrate-fresh      - Migración fresh + seed"
        echo "  seed               - Ejecutar seeders"
        echo "  optimize           - Limpiar cache"
        echo ""
        echo -e "${YELLOW}Composer:${NC}"
        echo "  composer [cmd]     - Ejecutar comando composer"
        echo "  composer-install   - Instalar dependencias"
        echo "  composer-update    - Actualizar dependencias"
        echo ""
        echo -e "${YELLOW}NPM:${NC}"
        echo "  npm [cmd]          - Ejecutar comando npm"
        echo "  npm-install        - Instalar dependencias"
        echo "  npm-dev            - Compilar assets (dev)"
        echo "  npm-watch          - Compilar assets (watch)"
        echo "  npm-build          - Compilar assets (prod)"
        echo ""
        echo -e "${YELLOW}Paquetes:${NC}"
        echo "  install-breeze     - Instalar Laravel Breeze"
        echo "  breeze-setup [stack] - Configurar Breeze (blade|livewire|react|vue)"
        echo "  install-filament   - Instalar Filament v3"
        echo "  filament-user      - Crear usuario admin Filament"
        echo ""
        echo -e "${YELLOW}Shell & Logs:${NC}"
        echo "  bash               - Entrar al contenedor (www-data)"
        echo "  root               - Entrar al contenedor (root)"
        echo "  logs               - Ver logs en tiempo real"
        echo "  xdebug-log         - Ver log de Xdebug"
        echo ""
        echo -e "${YELLOW}Mantenimiento:${NC}"
        echo "  restart            - Reiniciar contenedores"
        echo "  rebuild            - Reconstruir imágenes"
        echo "  fix-permissions    - Corregir permisos"
        echo "  fresh-start        - Reiniciar todo (elimina datos)"
        echo "  status             - Ver estado de contenedores"
        echo "  php-info           - Ver info de PHP"
        echo ""
        echo -e "${YELLOW}Ejemplos:${NC}"
        echo "  ./docker-helpers.sh artisan make:model Product -mfs"
        echo "  ./docker-helpers.sh composer require spatie/laravel-permission"
        echo "  ./docker-helpers.sh npm-watch"
        echo "  ./docker-helpers.sh install-breeze"
        ;;
    
    *)
        echo -e "${YELLOW}Comando no reconocido: $1${NC}"
        echo "Usa './docker-helpers.sh help' para ver comandos disponibles"
        exit 1
        ;;
esac