
# CAD Reports Plugin for Moodle 4.4

Plugin local para generar reportes especializados del sistema CAD (Centro de Apoyo Docente) en Moodle 4.4.

## 📋 Características

- ✅ **5 Reportes especializados** con filtros avanzados
- ✅ **Visualización en línea** con tablas responsive  
- ✅ **Exportación opcional** a Excel y CSV
- ✅ **Componentes nativos de Moodle** (tablelib, exportación)
- ✅ **Integración en menú de administración**
- ✅ **Sistema de permisos robusto**
- ✅ **Interfaz responsive** con Bootstrap

## 🚀 Instalación

### Requisitos
- Moodle 4.4 o superior
- PHP 8.0 o superior
- Permisos de administrador del sitio

### Pasos de instalación

1. **Descargar plugin**
```
cd /path/to/moodle/local/
git clone [repository-url] cadreports
```

2. **Instalar desde interfaz web**
   - Ir a `Administración del sitio > Notificaciones`
   - Seguir el proceso de instalación automática
   - Confirmar la instalación

3. **Configurar permisos**
   - Ir a `Administración del sitio > Usuarios > Permisos > Definir roles`
   - Asignar capacidades `local/cadreports:view` y `local/cadreports:export`
   - Aplicar a los roles necesarios (Manager, Coursecreator, etc.)

## 📊 Reportes Disponibles

### 1. Accesos y Sesiones
**Ubicación:** `Administración > CAD > Reportes > Accesos y Sesiones`

**Datos incluidos:**
- Curso, Grupo, Apellidos, Nombres, DNI
- Fecha/Hora de acceso
- Tiempo de permanencia en el aula virtual

**Filtros:** Curso, Grupo, Usuario, Rango de fechas

### 2. Registro de Notas  
**Ubicación:** `Administración > CAD > Reportes > Registro de Notas`

**Datos incluidos:**
- Calificaciones por Módulo/Unidad (M1 U1, M2 U3, etc.)
- Ponderado final por curso/grupo
- Nomenclatura estandarizada de actividades

**Filtros:** Curso, Grupo, Periodo académico

### 3. Resumen de Cuestionarios
**Ubicación:** `Administración > CAD > Reportes > Resumen de Cuestionarios`

**Datos incluidos:**
- Grupo, Curso, Apellidos, Nombres, DNI
- Calificaciones y intentos de cuestionarios
- Estado de completación

**Filtros:** Curso, Grupo, Cuestionario específico

### 4. Actividad de Usuarios
**Ubicación:** `Administración > CAD > Reportes > Actividad de Usuarios`

**Datos incluidos:**
- Grupo, Curso, Apellidos, Nombres, DNI
- Número total de ingresos
- Fecha de último acceso

**Filtros:** Curso, Grupo, Rango de fechas

### 5. Participación en Foros
**Ubicación:** `Administración > CAD > Reportes > Participación en Foros`

**Datos incluidos:**
- Datos básicos del participante
- Fecha de participación del alumno
- Estado y fecha de respuesta de docentes/administradores

**Filtros:** Curso, Grupo, Estado de respuesta, Foro específico

## 🛠️ Arquitectura del Plugin

### Estructura de archivos
```
local/cadreports/
├── version.php              # Información del plugin
├── lang/                    # Archivos de idioma
├── classes/                 # Clases PHP
│   ├── table/              # Clases de tablas personalizadas
│   ├── reports/            # Lógica de reportes
│   └── form/               # Formularios Moodle
├── reports/                # Páginas de reportes
├── templates/              # Plantillas Mustache
└── styles.css             # Estilos CSS
```

### Clases principales

#### `cadreports_base` (Clase abstracta)
Funcionalidades comunes para todos los reportes:
- Manejo de filtros
- Renderizado de formularios
- Validación de permisos
- Configuración base de tablas

#### `access_report_table` (Extiende table_sql)
Tabla especializada para reporte de accesos:
- Formateo personalizado de columnas
- Soporte para exportación
- Paginación automática
- Ordenamiento configurable

### Componentes Moodle utilizados

#### `table_sql`
- **Ventajas:** Paginación, ordenamiento, exportación nativa
- **Uso:** Base para todas las tablas de reportes
- **Configuración:** Columnas, headers, formato, descarga

#### `moodle_url`
- **Propósito:** URLs consistentes y seguras
- **Implementación:** Filtros, paginación, exportación

#### `context_course` 
- **Función:** Validación de permisos por curso
- **Seguridad:** Acceso controlado a datos sensibles

## 🔒 Seguridad y Permisos

### Capacidades definidas
```
'local/cadreports:view' => [
'captype' => 'read',
'contextlevel' => CONTEXT_SYSTEM,
'archetypes' => [
'manager' => CAP_ALLOW,
'coursecreator' => CAP_ALLOW
]
],
'local/cadreports:export' => [
'captype' => 'read',
'contextlevel' => CONTEXT_SYSTEM,
'archetypes' => [
'manager' => CAP_ALLOW
]
]
```

### Validaciones de seguridad
- ✅ Verificación de contexto y permisos
- ✅ Sanitización de parámetros de entrada
- ✅ Escape de salida HTML
- ✅ Prevención de inyección SQL
- ✅ Validación de roles por curso

## 📈 Rendimiento y Optimización

### Consultas SQL optimizadas
- **Índices:** Utilizados en campos de filtrado frecuente
- **JOINs:** Optimizados para reducir carga
- **LIMIT:** Paginación para grandes datasets
- **Caché:** Implementado en consultas repetitivas

### Paginación inteligente
```
$table->pageable(true);
$table->out(25, true); // 25 registros por página
```

### Exportación eficiente
- **Streaming:** Para archivos grandes
- **Memoria:** Gestión optimizada
- **Formatos:** Excel y CSV nativos de Moodle

## 🧪 Testing y Debugging

### Logs disponibles
```
// Habilitar debugging
debugging('CAD Reports: ' . $message, DEBUG_DEVELOPER);

// Log de errores específicos
error_log('CAD Reports Error: ' . $error_message);
```

### Testing recomendado
1. **Permisos:** Verificar accesos por rol
2. **Filtros:** Probar combinaciones de filtros
3. **Exportación:** Validar formatos de salida
4. **Rendimiento:** Medir tiempo en datasets grandes
5. **Responsive:** Probar en dispositivos móviles

## 🔧 Configuración Avanzada

### Variables de configuración
```
// En config.php o settings
$CFG->cadreports_max_records = 5000;  // Máximo registros por reporte
$CFG->cadreports_cache_time = 3600;   // Tiempo de caché en segundos
$CFG->cadreports_export_limit = 10000; // Límite para exportación
```

### Personalización de estilos
```
/* Archivo: local/cadreports/styles.css */
.cadreports-table {
/* Estilos personalizados */
}
```

## 🚨 Troubleshooting

### Problemas comunes

#### Error: "No se pueden generar reportes"
**Solución:**
1. Verificar permisos del usuario
2. Comprobar configuración de roles
3. Revisar logs de Moodle

#### Exportación lenta o falla
**Solución:**
1. Aumentar límites de PHP (memory_limit, max_execution_time)
2. Reducir rango de fechas
3. Aplicar filtros más específicos

#### Tablas vacías
**Solución:**
1. Verificar datos en logstore_standard_log
2. Comprobar configuración de logging
3. Validar filtros aplicados

### Logs útiles
```
# Ver logs de Moodle
tail -f /path/to/moodle/moodledata/logs/moodle.log

# Ver logs de PHP
tail -f /var/log/php/error.log
```

## 🔄 Actualizaciones

### Proceso de actualización
1. **Backup:** Respaldar base de datos
2. **Código:** Actualizar archivos del plugin
3. **Base de datos:** Ejecutar upgrade desde admin
4. **Testing:** Verificar funcionamiento

### Versionado
- **Major:** Cambios incompatibles (v2.0.0)
- **Minor:** Nuevas características (v1.1.0)  
- **Patch:** Correcciones de bugs (v1.0.1)

## 📞 Soporte

### Información de soporte
- **Versión Moodle:** 4.4+
- **Compatibilidad:** Temas estándar (Boost, Classic)
- **Navegadores:** Chrome, Firefox, Safari, Edge



---

## 📄 Licencia

Este plugin está licenciado bajo GPL v3.0. Ver archivo `LICENSE` para más detalles.

---

**Última actualización:** Septiembre 2024  
**Version:** 1.0.0  
**Compatibilidad:** Moodle 4.4+
```
