<?php
/**
 * Strings de idioma para local_cadreports - Arquitectura modular
 * Plugin local_cadreports para Moodle 4.4
 */

defined('MOODLE_INTERNAL') || die();

// Plugin info
$string['pluginname'] = 'Reportes CAD';
$string['cadreports:view'] = 'Ver reportes CAD';
$string['cadreports:export'] = 'Exportar reportes CAD';

// Menú principal
$string['cadreports'] = 'Reportes CAD';

// Reportes disponibles
$string['accessreport'] = 'Accesos y Dedicación';
$string['gradesreport'] = 'Registro de Notas';
$string['quizreport'] = 'Resumen de Cuestionarios';
$string['activityreport'] = 'Actividad de Usuarios';
$string['forumreport'] = 'Participación en Foros';

// Formularios
$string['filters'] = 'Filtros';
$string['course'] = 'Curso';
$string['allcourses'] = 'Todos los cursos';
$string['group'] = 'Grupo';
$string['allgroups'] = 'Todos los grupos';
$string['nogroup'] = 'Sin grupo';
$string['datefrom'] = 'Fecha desde';
$string['dateto'] = 'Fecha hasta';
$string['generatereport'] = 'Generar reporte';
$string['selectfilters'] = 'Selecciona al menos un filtro para generar el reporte';

// Tabla - Columnas comunes
$string['order'] = 'Orden';
$string['coursefullname'] = 'Nombre largo del curso';
$string['courseshortname'] = 'Nombre corto del curso';
$string['firstname'] = 'Nombres';
$string['lastname'] = 'Apellidos';
$string['username'] = 'Usuario';
$string['email'] = 'Email';

// Columnas específicas del reporte de accesos
$string['dedication'] = 'Dedicación';

// Exportación
$string['exportoptions'] = 'Opciones de exportación';
$string['downloadexcel'] = 'Descargar Excel';
$string['downloadcsv'] = 'Descargar CSV';

// Errores
$string['error_daterange'] = 'La fecha hasta debe ser posterior a la fecha desde';
$string['error_nofilters'] = 'Debe seleccionar al menos un filtro';

// Privacidad
$string['privacy:metadata'] = 'El plugin Reportes CAD no almacena datos personales, solo muestra información existente en Moodle.';

// Sección principal
$string['cadreports'] = 'Reportes CAD';
$string['reports'] = 'Reportes';
$string['settings'] = 'Configuración';

// Configuraciones
$string['session_gap'] = 'Tiempo máximo de sesión';
$string['session_gap_desc'] = 'Tiempo máximo en segundos entre clicks para considerar que el usuario sigue en la misma sesión de estudio.';
$string['records_per_page'] = 'Registros por página';
$string['records_per_page_desc'] = 'Número máximo de registros a mostrar por página en los reportes.';

// ... resto del código existente ...
