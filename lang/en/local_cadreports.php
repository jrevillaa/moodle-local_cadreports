<?php
defined('MOODLE_INTERNAL') || die();

// Plugin name
$string['pluginname'] = 'Reportes CAD';


// Access & activity related
$string['access_report'] = 'Accesos y Sesiones';
$string['access_report_description'] = 'Reporte que muestra los tiempos de acceso del usuario y la duración de sesión en los cursos.';
$string['accessdate'] = 'Fecha/Hora de Acceso';
$string['action'] = 'Acción';
$string['actioncreated'] = 'Creado';
$string['actiondeleted'] = 'Eliminado';
$string['actionfilter'] = 'Filtrar por Acción';
$string['actionloggedin'] = 'Inició Sesión';
$string['actionloggedout'] = 'Cerró Sesión';
$string['actionsubmitted'] = 'Enviado';
$string['actionupdated'] = 'Actualizado';
$string['actionviewed'] = 'Visto';
$string['activity_report'] = 'Actividad de Usuarios';
$string['activity_report_description'] = 'Reporte de cantidad total de inicios de sesión y fecha del último acceso por usuario.';
$string['activityidnumber'] = 'ID de Actividad';
$string['activityname'] = 'Nombre de Actividad';
$string['activityreport'] = 'Actividad de Usuarios';
$string['activityreportinfo'] = 'Este reporte muestra todas las acciones realizadas por los usuarios en los cursos seleccionados.';
$string['activitytype'] = 'Tipo de Actividad';


// Courses & groups
$string['allactivities'] = 'Todas las actividades';
$string['allactivitiesinfo'] = 'Este reporte muestra TODAS las actividades y la nota final del curso para los cursos seleccionados.';
$string['allactions'] = 'Todas las acciones';
$string['allcomponents'] = 'Todos los componentes';
$string['allcourses'] = 'Todos los cursos';
$string['allcourses_option'] = '--- Todos los cursos ---';
$string['allcourses_option_help'] = 'Selecciona esta opción para incluir todos los cursos disponibles';
$string['courses'] = 'Cursos';
$string['allforums'] = 'Todos los foros';
$string['allgroups'] = 'Todos los grupos';
$string['allparticipation'] = 'Toda la participación';
$string['allquizzes'] = 'Todos los cuestionarios';
$string['allusers'] = 'Todos los usuarios';

$string['attempts'] = 'Intentos';
$string['attemptsallowed'] = 'Intentos Permitidos';
$string['attemptsmade'] = 'Intentos Realizados';


// Columns
$string['component'] = 'Componente';
$string['componentassign'] = 'Tarea';
$string['componentcore'] = 'Sistema';
$string['componentfilter'] = 'Filtrar por Componente';
$string['componentforum'] = 'Foro';
$string['componentlesson'] = 'Lección';
$string['componentquiz'] = 'Cuestionario';
$string['componentscorm'] = 'SCORM';
$string['courseshortname'] = 'Nombre corto del curso';
$string['course'] = 'Curso';
$string['courseaccesses'] = 'Accesos al Curso';
$string['coursefullname'] = 'Nombre largo del curso';
$string['datefrom'] = 'Fecha desde';
$string['dateto'] = 'Fecha hasta';
$string['datetime'] = 'Fecha y Hora';
$string['dedication'] = 'Dedicación';
$string['duration'] = 'Duración';
$string['durationformat_detailed'] = '{$a->days} días, {$a->hours} horas, {$a->minutes} minutos, {$a->seconds} segundos';

$string['dni'] = 'DNI';
$string['order'] = '#';


// Errors
$string['error_daterange'] = 'La fecha hasta debe ser posterior a la fecha desde';
$string['error_nofilters'] = 'Debe seleccionar al menos un filtro';
$string['error_userquery_required'] = 'Debe seleccionar al menos un usuario';
$string['error_course_or_group_required'] = 'Debe seleccionar al menos un curso o grupo';
$string['errorloadinggroups'] = 'Error cargando grupos';


// Export
$string['export'] = 'Exportar';
$string['exportoptions'] = 'Opciones de exportación';
$string['exporttoexcel'] = 'Exportar a Excel';
$string['exporttocsv'] = 'Exportar a CSV';
$string['downloadcsv'] = 'Descargar CSV';
$string['downloadexcel'] = 'Descargar Excel';
$string['erp_export'] = 'Reporte de Notas para el ERP';
$string['erp_exportreport'] = 'Reporte de Exportar Notas a ERP';
$string['erp_export_description'] = 'Exportar las notas finales en formato ERP (CSV con código de proyecto, DNI y nota).';


// Filters
$string['filter'] = 'Filtrar';
$string['filters'] = 'Filtros';
$string['filtermode'] = 'Modo de filtrado';
$string['filtermode_bycourse'] = 'Por cursos y/o grupos';
$string['filtermode_byuser'] = 'Por usuarios';


// Forum report
$string['forum'] = 'Foro';
$string['forum_report'] = 'Reporte de Foros de participantes';
$string['forum_report_description'] = 'Reporte de participación en foros con el estado de respuesta del docente.';
$string['forumidnumber'] = 'ID del Foro';
$string['forumname'] = 'Nombre del Foro';
$string['forumreportinfo'] = 'Este reporte muestra la participación de estudiantes en foros y las respuestas del personal docente/administrativo';


// Grades
$string['grade'] = 'Nota';
$string['gradefinal'] = 'Nota';
$string['grades_report'] = 'Reporte de Notas del Alumno';
$string['grades_report_description'] = 'Reporte que muestra las notas por módulo/unidad con el promedio ponderado final.';
$string['finalgrade'] = 'Nota Final del Curso';

$string['group'] = 'Grupo';
$string['groups'] = 'Grupos';


// Info text
$string['generatereport'] = 'Generar reporte';


// Last access / login
$string['lastaccess'] = 'Último Acceso';
$string['lastcourseaccess'] = 'Último Acceso al Curso';
$string['lastname'] = 'Apellidos';
$string['firstname'] = 'Nombres';


// Misc
$string['manualgrade'] = 'Calificación Manual';
$string['maxgrade'] = 'Nota Máxima';
$string['modifiedby'] = 'Modificado por';
$string['modifiedonly'] = 'Solo calificaciones modificadas';
$string['modifiedonly_help'] = 'Mostrar únicamente las calificaciones que han sido modificadas manualmente';
$string['nogroup'] = 'Sin grupo';
$string['nogroups'] = 'No hay grupos para los cursos seleccionados';


// Permissions
$string['nopermission'] = 'No tienes permiso para ver este reporte.';
$string['noexportpermission'] = 'No tienes permiso para exportar reportes.';
$string['nocourseselected'] = 'No se seleccionó ningún curso. Selecciona un curso para generar el reporte.';
$string['nodata'] = 'No hay datos disponibles para los filtros seleccionados.';


// Privacy
$string['privacy:metadata'] = 'El plugin Reportes CAD no almacena datos personales, solo muestra información existente en Moodle.';


// Project / ERP
$string['proyecto'] = 'Proyecto';
$string['nota'] = 'Nota';


// Quiz report
$string['quiz'] = 'Cuestionario';
$string['quiz_report'] = 'Resumen de Cuestionarios';
$string['quiz_report_description'] = 'Resumen de los intentos de cuestionarios y notas por estudiante.';
$string['quizidnumber'] = 'ID del Cuestionario';
$string['quizname'] = 'Nombre del Cuestionario';
$string['quizreport'] = 'Resumen de Cuestionarios';
$string['quizreportinfo'] = 'Este reporte muestra información detallada sobre los intentos de cuestionarios de los estudiantes.';
$string['latestattempt'] = 'Último Intento';
$string['bestgrade'] = 'Mejor Nota';


// Reports menu
$string['reports'] = 'Reportes';
$string['records_per_page'] = 'Registros por página';
$string['records_per_page_desc'] = 'Número máximo de registros a mostrar por página en los reportes.';
$string['session_gap'] = 'Tiempo máximo de sesión';
$string['session_gap_desc'] = 'Tiempo máximo en segundos entre clicks para considerar que el usuario sigue en la misma sesión de estudio.';


// Selection
$string['selectcourse'] = 'Selecciona un curso para ver el reporte.';
$string['selectcoursefirst'] = 'Selecciona primero uno o más cursos';
$string['selectcourses'] = 'Buscar y seleccionar cursos...';
$string['selectfilters'] = 'Selecciona al menos un filtro para generar el reporte';
$string['selectforum'] = 'Seleccionar foro';
$string['selectgroup'] = 'Seleccionar grupo';
$string['selectgroups'] = 'Buscar y seleccionar grupos...';
$string['selectquiz'] = 'Seleccionar cuestionario';
$string['selectuser'] = 'Seleccionar usuario';


// Sessions
$string['session_gap'] = 'Tiempo máximo de sesión';
$string['settings'] = 'Configuración';


// Tables
$string['target'] = 'Objetivo';
$string['targetname'] = 'Nombre del Objetivo';
$string['teacherresponse'] = 'Respuesta del Docente';
$string['timemodified'] = 'Fecha/Hora Modificación';
$string['totalaccess'] = 'Accesos Totales';


// User fields
$string['userquery'] = 'Usuarios (username y/o correo)';
$string['userquery_help'] = "Escribe uno o más usernames o correos.\nPuedes separar varios con comas o saltos de línea.";
$string['userquery_placeholder'] = "Ejemplo:\nalumno01, alumno02\no\nalumno01@example.edu";
$string['username'] = 'Usuario';


// View & export permissions
$string['cadreports:view'] = 'Ver reportes CAD';
$string['cadreports:export'] = 'Exportar reportes CAD';

// Menú principal
$string['cadreports'] = 'Reportes CAD';

// Reportes disponibles
$string['accessreport'] = 'Accesos y Dedicación';
$string['gradesreport'] = 'Registro de Notas';
$string['quizreport'] = 'Resumen de Cuestionarios';
$string['activityreport'] = 'Actividad de Usuarios';
$string['accessreport'] = 'Reporte de Accesos de participantes';
$string['forumreport'] = 'Reporte de Foros de participantes';
$string['gradesreport'] = 'Reporte de Notas del Alumno';
$string['quizreport'] = 'Resumen de Cuestionarios';

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

// Columnas específicas del reporte de accesos
$string['dedication'] = 'Dedicación';
$string['courseaccesses'] = 'Accesos al Curso'; // ✅ NUEVO
$string['lastcourseaccess'] = 'Último Acceso'; // ✅ NUEVO

// ✅ AGREGADO: Formato de duración detallada
$string['durationformat_detailed'] = '{$a->days} días, {$a->hours} horas, {$a->minutes} minutos, {$a->seconds} segundos';


// Strings específicos del reporte de notas
$string['activityname'] = 'Nombre de Actividad';
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

// Strings para multiselección
$string['courses'] = 'Cursos';
$string['groups'] = 'Grupos';
$string['selectcourses'] = 'Buscar y seleccionar cursos...';
$string['selectgroups'] = 'Buscar y seleccionar grupos...';
$string['allactivitiesinfo'] = 'Este reporte muestra TODAS las actividades y la nota final del curso para los cursos seleccionados.';

// Strings para multiselect

$string['selectcoursefirst'] = 'Selecciona primero uno o más cursos';
$string['nogroups'] = 'No hay grupos para los cursos seleccionados';
$string['errorloadinggroups'] = 'Error cargando grupos';


// Strings específicos del reporte de notas
$string['activityidnumber'] = 'ID de Actividad'; // ✅ NUEVO

$string['quizreport'] = 'Resumen de Cuestionarios';
$string['quizreportinfo'] = 'Este reporte muestra información detallada sobre los intentos de cuestionarios de los estudiantes.';
$string['quizname'] = 'Nombre del Cuestionario';
$string['quizidnumber'] = 'ID del Cuestionario';
$string['attemptsmade'] = 'Intentos Realizados';
$string['attemptsallowed'] = 'Intentos Permitidos';
$string['bestgrade'] = 'Mejor Nota';
$string['latestattempt'] = 'Último Intento';

// ... código existente ...

// Strings específicos del reporte de actividad de usuarios
$string['activityreport'] = 'Actividad de Usuarios';
$string['activityreportinfo'] = 'Este reporte muestra todas las acciones realizadas por los usuarios en los cursos seleccionados.';
$string['action'] = 'Acción';
$string['component'] = 'Componente';
$string['target'] = 'Objetivo';
$string['targetname'] = 'Nombre del Objetivo';
$string['eventdescription'] = 'Descripción';
$string['datetime'] = 'Fecha y Hora';

// Strings para filtros
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

$string['forumreport'] = 'Participación en Foros';
$string['forumreportinfo'] = 'Este reporte muestra la participación de estudiantes en foros y las respuestas del personal docente/administrativo.';
$string['forumname'] = 'Nombre del Foro';
$string['forumidnumber'] = 'ID del Foro';
$string['participationdate'] = 'Fecha de Participación';
$string['staffresponsestatus'] = 'Estado de Participación del Profesor';
$string['staffresponsedate'] = 'Fecha de Participación del Profesor';

// Strings para filtros de participación
$string['allparticipation'] = 'Toda la participación';
$string['participated'] = 'Con participación';
$string['notparticipated'] = 'Sin participación';
$string['respondedbycstaff'] = 'Respondido por staff';
$string['participationstatus'] = 'Estado de Participación';


// Extras requeridos por el formulario de Registro de Notas.

$string['filtermode'] = 'Modo de filtrado';
$string['filtermode_bycourse'] = 'Por cursos y/o grupos';
$string['filtermode_byuser'] = 'Por usuarios';

$string['userquery'] = 'Usuarios (username y/o correo)';
$string['userquery_help'] = "Escribe uno o más usernames o correos.\nPuedes separar varios con comas o saltos de línea.\nEjemplo:\nalumno01\nalumno02@example.edu, p.alvarez";
$string['userquery_placeholder'] = "Ejemplo:\nalumno01, alumno02\no\nalumno01@example.edu";
