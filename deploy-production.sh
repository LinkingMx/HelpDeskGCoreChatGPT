#!/bin/bash

echo "🚀 Iniciando despliegue en producción..."
echo "=================================="

# 1. Verificar que estemos en el directorio correcto
if [ ! -f "artisan" ]; then
    echo "❌ Error: No se encontró el archivo artisan. Asegúrate de estar en el directorio raíz del proyecto."
    exit 1
fi

echo "📁 Directorio verificado ✓"

# 2. Hacer pull de los últimos cambios
echo "📥 Obteniendo últimos cambios del repositorio..."
git pull origin main

# 3. Verificar que el archivo UserResource.php existe y tiene el contenido correcto
if [ -f "app/Filament/Resources/UserResource.php" ]; then
    echo "📄 UserResource.php encontrado ✓"
    
    # Verificar que contenga los campos client_id y department_id
    if grep -q "client_id" app/Filament/Resources/UserResource.php && grep -q "department_id" app/Filament/Resources/UserResource.php; then
        echo "🔍 Campos client_id y department_id encontrados ✓"
    else
        echo "⚠️  Advertencia: No se encontraron los campos client_id o department_id en UserResource.php"
    fi
else
    echo "❌ Error: No se encontró app/Filament/Resources/UserResource.php"
    exit 1
fi

# 4. Instalar/actualizar dependencias de Composer
echo "📦 Actualizando dependencias de Composer..."
composer install --no-dev --optimize-autoloader

# 5. Limpiar todos los caches
echo "🧹 Limpiando caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear

# 6. Limpiar caché de OPcache si está habilitado
echo "🔄 Reiniciando OPcache..."
php -r "if (function_exists('opcache_reset')) { opcache_reset(); echo 'OPcache limpiado ✓'; } else { echo 'OPcache no disponible'; }"

# 7. Optimizar para producción
echo "⚡ Optimizando para producción..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 8. Ejecutar migraciones si las hay
echo "🗄️  Verificando migraciones..."
php artisan migrate --force

# 9. Verificar que Filament esté funcionando
echo "🎛️  Verificando recursos de Filament..."
php artisan tinker --execute="
try {
    \$resource = \App\Filament\Resources\UserResource::class;
    echo 'UserResource cargado correctamente ✓' . PHP_EOL;
    
    // Verificar que los campos estén en el formulario
    \$form = \$resource::form(Filament\Forms\Form::make());
    echo 'Formulario del UserResource generado correctamente ✓' . PHP_EOL;
    
} catch (Exception \$e) {
    echo '❌ Error en UserResource: ' . \$e->getMessage() . PHP_EOL;
    exit(1);
}
"

# 10. Verificar permisos de archivos
echo "🔐 Verificando permisos..."
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || echo "⚠️  No se pudieron cambiar los propietarios (esto es normal si no eres root)"

echo ""
echo "✅ ¡Despliegue completado!"
echo "🌐 Tu aplicación debería estar actualizada."
echo ""
echo "🔍 Para verificar que todo funciona:"
echo "   1. Ve a la sección de Usuarios en el panel admin"
echo "   2. Crea o edita un usuario"
echo "   3. Selecciona un rol 'user' → debería aparecer el campo Cliente"
echo "   4. Selecciona un rol 'agent' → debería aparecer el campo Departamento"
echo ""
