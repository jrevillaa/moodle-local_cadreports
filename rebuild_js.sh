#!/bin/bash
# Script para limpiar caché de Moodle y recompilar JavaScript
# Plugin: local_cadreports

echo "========================================="
echo "Limpiando caché de Moodle..."
echo "========================================="

cd /usr/local/var/www/test_isil

# Limpiar caché de Moodle
php admin/cli/purge_caches.php

echo ""
echo "========================================="
echo "Recompilando módulos JavaScript AMD..."
echo "========================================="

cd /usr/local/var/www/test_isil/local/cadreports

# Verificar si grunt está instalado
if ! command -v grunt &> /dev/null; then
    echo "⚠️  Grunt no está instalado globalmente."
    echo "   Intentando usar npx..."
    
    # Intentar con npx
    if command -v npx &> /dev/null; then
        npx grunt amd
    else
        echo "❌ Error: Ni grunt ni npx están disponibles."
        echo "   Por favor instala grunt-cli:"
        echo "   npm install -g grunt-cli"
        exit 1
    fi
else
    grunt amd
fi

echo ""
echo "========================================="
echo "✅ Proceso completado"
echo "========================================="
echo "Por favor recarga la página en el navegador (Ctrl+Shift+R o Cmd+Shift+R)"
