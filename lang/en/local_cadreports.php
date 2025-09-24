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

// Menú principal y sección principal
$string['cadreports'] = 'Reportes CAD';
$string['reports'] = 'Reportes';
$string['settings'] = 'Configuración';

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

// Exportación
$string['exportoptions'] = 'Opciones de exportación';
$string['downloadexcel'] = 'Descargar Excel';
$string['downloadcsv'] = 'Descargar CSV';

// Errores
$string['error_daterange'] = 'La fecha hasta debe ser posterior a la fecha desde';
$string['error_nofilters'] = 'Debe seleccionar al menos un filtro';

// Privacidad
$string['privacy:metadata'] = 'El plugin Reportes CAD no almacena datos personales, solo muestra información existente en Moodle.';

// Configuraciones
$string['session_gap'] = 'Tiempo máximo de sesión';
$string['session_gap_desc'] = 'Tiempo máximo en segundos entre clicks para considerar que el usuario sigue en la misma sesión de estudio.';
$string['records_per_page'] = 'Registros por página';
$string['records_per_page_desc'] = 'Número máximo de registros a mostrar por página en los reportes.';

// Strings específicos del reporte de accesos y dedicación
$string['dedication'] = 'Dedicación';
$string['courseaccesses'] = 'Accesos al Curso';
$string['lastcourseaccess'] = 'Último Acceso';
$string['durationformat_detailed'] = '{$a->days} días, {$a->hours} horas, {$a->minutes} minutos, {$a->seconds} segundos';

// Strings específicos del reporte de notas
$string['activityname'] = 'Nombre de Actividad';
$string['activityidnumber'] = 'ID de Actividad';
$string['activitytype'] = 'Tipo de Actividad';
$string['allactivities'] = 'Todas las actividades';
$string['maxgrade'] = 'Nota Máxima';
$string['gradefinal'] = 'Nota';
$string['percentage'] = 'Porcentaje';
$string['timemodified'] = 'Fecha/Hora Modificación';
$string['modifiedby'] = 'Modificado por';
$string['finalgrade'] = 'Nota Final del Curso';
$string['manualgrade'] = 'Calificación Manual';
$string['modifiedonly'] = 'Solo calificaciones modificadas';
$string['modifiedonly_help'] = 'Mostrar únicamente las calificaciones que han sido modificadas manualmente';

// Strings específicos del reporte de cuestionarios
$string['quizreportinfo'] = 'Este reporte muestra información detallada sobre los intentos de cuestionarios de los estudiantes.';
$string['quizname'] = 'Nombre del Cuestionario';
$string['quizidnumber'] = 'ID del Cuestionario';
$string['attemptsmade'] = 'Intentos Realizados';
$string['attemptsallowed'] = 'Intentos Permitidos';
$string['bestgrade'] = 'Mejor Nota';
$string['latestattempt'] = 'Último Intento';

// Strings específicos del reporte de actividad de usuarios
$string['activityreportinfo'] = 'Este reporte muestra todas las acciones realizadas por los usuarios en los cursos seleccionados.';
$string['action'] = 'Acción';
$string['component'] = 'Componente';
$string['target'] = 'Objetivo';
$string['targetname'] = 'Nombre del Objetivo';
$string['eventdescription'] = 'Descripción';
$string['datetime'] = 'Fecha y Hora';

// Strings para filtros de actividad
$string['allactions'] = 'Todas las acciones';
$string['actionviewed'] = 'Visto';
$string['actioncreated'] = 'Creado';
$string['actionupdated'] = 'Actualizado';
$string['actionsubmitted'] = 'Enviado';
$string['actiondeleted'] = 'Eliminado';
$string['actionloggedin'] = 'Inició Sesión';
$string['actionloggedout'] = 'Cerró Sesión';
$string['actionfilter'] = 'Filtrar por Acción';

$string['allcomponents'] = 'Todos los componentes';
$string['componentcore'] = 'Sistema';
$string['componentquiz'] = 'Cuestionario';
$string['componentforum'] = 'Foro';
$string['componentassign'] = 'Tarea';
$string['componentscorm'] = 'SCORM';
$string['componentlesson'] = 'Lección';
$string['componentfilter'] = 'Filtrar por Componente';

// Strings específicos del reporte de participación en foros
$string['forumreportinfo'] = 'Este reporte muestra la participación de estudiantes en foros y las respuestas del personal docente/administrativo.';
$string['forumname'] = 'Nombre del Foro';
$string['forumidnumber'] = 'ID del Foro';
$string['participationdate'] = 'Fecha de Participación';
$string['staffresponsestatus'] = 'Estado de Respuesta';
$string['staffresponsedate'] = 'Fecha de Respuesta';

// Strings para filtros de participación en foros
$string['allparticipation'] = 'Toda la participación';
$string['participated'] = 'Con participación';
$string['notparticipated'] = 'Sin participación';
$string['respondedbycstaff'] = 'Respondido por staff';
$string['participationstatus'] = 'Estado de Participación';

// Strings para multiselección de cursos y grupos
$string['courses'] = 'Cursos';
$string['groups'] = 'Grupos';
$string['selectcourses'] = 'Buscar y seleccionar cursos...';
$string['selectgroups'] = 'Buscar y seleccionar grupos...';
$string['selectcoursefirst'] = 'Selecciona primero uno o más cursos';
$string['nogroups'] = 'No hay grupos para los cursos seleccionados';
$string['errorloadinggroups'] = 'Error cargando grupos';
$string['allactivitiesinfo'] = 'Este reporte muestra TODAS las actividades y la nota final del curso para los cursos seleccionados.';
