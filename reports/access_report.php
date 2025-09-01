<?php
require_once('../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/csvlib.class.php');
require_once($CFG->libdir.'/excellib.class.php');
require_once($CFG->libdir.'/tablelib.php');

admin_externalpage_setup('local_cadreports');

$PAGE->set_url('/local/cadreports/reports/access_report.php');
$PAGE->set_title('Reporte de Accesos y Sesiones');
$PAGE->set_heading('Accesos e Ingresos al Aula Virtual');

// Parámetros de filtros
$courseid = optional_param('courseid', 0, PARAM_INT);
$groupid = optional_param('groupid', 0, PARAM_INT);
$userid = optional_param('userid', 0, PARAM_INT);
$datefrom = optional_param('datefrom', '', PARAM_TEXT);
$dateto = optional_param('dateto', '', PARAM_TEXT);
$export = optional_param('export', '', PARAM_ALPHA);
$download = optional_param('download', '', PARAM_ALPHA);

// Configurar tabla para exportación
$table = new table_sql('cadreports_access');
$table->is_downloadable(true);
$table->show_download_buttons_at([TABLE_P_BOTTOM]);

$baseurl = new moodle_url('/local/cadreports/reports/access_report.php', [
    'courseid' => $courseid,
    'groupid' => $groupid,
    'userid' => $userid,
    'datefrom' => $datefrom,
    'dateto' => $dateto
]);

$table->define_baseurl($baseurl);

// Definir columnas
$columns = ['coursename', 'groupname', 'lastname', 'firstname', 'idnumber', 'access_time', 'duration'];
$headers = [
    get_string('course'),
    'Grupo',
    get_string('lastname'),
    get_string('firstname'),
    'DNI',
    'Fecha/Hora',
    'Tiempo de permanencia'
];

$table->define_columns($columns);
$table->define_headers($headers);

// Configurar tabla
$table->sortable(true, 'coursename', SORT_ASC);
$table->collapsible(false);
$table->pageable(true);
$table->set_attribute('class', 'table table-striped table-hover');

// Procesar descarga si se solicita
if ($table->is_downloading()) {
    $table->start_output();
}

if (!$table->is_downloading()) {
    echo $OUTPUT->header();

    // Formulario de filtros
    echo html_writer::start_tag('div', ['class' => 'cadreports-filters card mb-4']);
    echo html_writer::start_tag('div', ['class' => 'card-header']);
    echo html_writer::tag('h5', 'Filtros de búsqueda', ['class' => 'mb-0']);
    echo html_writer::end_tag('div');

    echo html_writer::start_tag('div', ['class' => 'card-body']);
    echo html_writer::start_tag('form', ['method' => 'GET', 'id' => 'cadreports-form']);
    echo html_writer::start_tag('div', ['class' => 'row g-3']);

    // Filtro de cursos
    echo html_writer::start_tag('div', ['class' => 'col-md-3']);
    echo html_writer::tag('label', 'Curso:', ['for' => 'courseid', 'class' => 'form-label']);
    $courses = get_courses();
    $courseoptions = [0 => 'Todos los cursos'];
    foreach ($courses as $course) {
        if ($course->id > 1) {
            $courseoptions[$course->id] = format_string($course->fullname);
        }
    }
    echo html_writer::select($courseoptions, 'courseid', $courseid, false,
        ['class' => 'form-select', 'id' => 'id_courseid']);
    echo html_writer::end_tag('div');

    // Filtro de grupos
    echo html_writer::start_tag('div', ['class' => 'col-md-3']);
    echo html_writer::tag('label', 'Grupo:', ['for' => 'groupid', 'class' => 'form-label']);
    $groupoptions = [0 => 'Todos los grupos'];
    if ($courseid) {
        $groups = groups_get_all_groups($courseid);
        foreach ($groups as $group) {
            $groupoptions[$group->id] = format_string($group->name);
        }
    }
    echo html_writer::select($groupoptions, 'groupid', $groupid, false,
        ['class' => 'form-select', 'id' => 'id_groupid']);
    echo html_writer::end_tag('div');

    // Filtro de usuarios
    echo html_writer::start_tag('div', ['class' => 'col-md-3']);
    echo html_writer::tag('label', 'Usuario:', ['for' => 'userid', 'class' => 'form-label']);
    $useroptions = [0 => 'Todos los usuarios'];
    if ($courseid) {
        $context = context_course::instance($courseid);
        $users = get_enrolled_users($context, '', $groupid, 'u.id, u.firstname, u.lastname',
            'u.lastname, u.firstname');
        foreach ($users as $user) {
            $useroptions[$user->id] = fullname($user);
        }
    }
    echo html_writer::select($useroptions, 'userid', $userid, false,
        ['class' => 'form-select', 'id' => 'id_userid']);
    echo html_writer::end_tag('div');

    // Fecha desde
    echo html_writer::start_tag('div', ['class' => 'col-md-2']);
    echo html_writer::tag('label', 'Desde:', ['for' => 'datefrom', 'class' => 'form-label']);
    echo html_writer::empty_tag('input', [
        'type' => 'date',
        'name' => 'datefrom',
        'id' => 'id_datefrom',
        'value' => $datefrom,
        'class' => 'form-control'
    ]);
    echo html_writer::end_tag('div');

    // Fecha hasta
    echo html_writer::start_tag('div', ['class' => 'col-md-2']);
    echo html_writer::tag('label', 'Hasta:', ['for' => 'dateto', 'class' => 'form-label']);
    echo html_writer::empty_tag('input', [
        'type' => 'date',
        'name' => 'dateto',
        'id' => 'id_dateto',
        'value' => $dateto,
        'class' => 'form-control'
    ]);
    echo html_writer::end_tag('div');

    echo html_writer::end_tag('div'); // row

    // Botones
    echo html_writer::start_tag('div', ['class' => 'mt-3 d-flex gap-2']);
    echo html_writer::empty_tag('input', [
        'type' => 'submit',
        'value' => 'Generar Reporte',
        'class' => 'btn btn-primary'
    ]);
    echo html_writer::link($baseurl, 'Limpiar filtros', ['class' => 'btn btn-outline-secondary']);
    echo html_writer::end_tag('div');

    echo html_writer::end_tag('form');
    echo html_writer::end_tag('div'); // card-body
    echo html_writer::end_tag('div'); // card
}

// Generar reporte si hay filtros aplicados
if ($courseid || $datefrom || $userid) {

    // Construir consulta SQL
    $fields = "u.id as userid,
               u.firstname,
               u.lastname, 
               u.idnumber,
               c.fullname as coursename,
               COALESCE(g.name, 'Sin grupo') as groupname,
               FROM_UNIXTIME(l.timecreated) as access_time,
               COALESCE(s.duration, 0) as duration";

    $from = "{user} u
             JOIN {user_enrolments} ue ON u.id = ue.userid
             JOIN {enrol} e ON ue.enrolid = e.id  
             JOIN {course} c ON e.courseid = c.id
             LEFT JOIN {groups_members} gm ON u.id = gm.userid
             LEFT JOIN {groups} g ON gm.groupid = g.id AND g.courseid = c.id
             JOIN {logstore_standard_log} l ON u.id = l.userid AND l.courseid = c.id
             LEFT JOIN (
                 SELECT userid, courseid, 
                        SUM(CASE WHEN action = 'viewed' THEN 1 ELSE 0 END) * 300 as duration
                 FROM {logstore_standard_log} 
                 WHERE action IN ('viewed', 'created', 'updated')
                 GROUP BY userid, courseid
             ) s ON u.id = s.userid AND c.id = s.courseid";

    $where = "u.deleted = 0 AND u.id > 1";
    $params = [];

    if ($courseid) {
        $where .= " AND c.id = :courseid";
        $params['courseid'] = $courseid;
    }

    if ($groupid) {
        $where .= " AND g.id = :groupid";
        $params['groupid'] = $groupid;
    }

    if ($userid) {
        $where .= " AND u.id = :userid";
        $params['userid'] = $userid;
    }

    if ($datefrom) {
        $where .= " AND l.timecreated >= :datefrom";
        $params['datefrom'] = strtotime($datefrom . ' 00:00:00');
    }

    if ($dateto) {
        $where .= " AND l.timecreated <= :dateto";
        $params['dateto'] = strtotime($dateto . ' 23:59:59');
    }

    $table->set_sql($fields, $from, $where, $params);

    // Configurar formato de columnas
    $table->column_class('coursename', 'text-start');
    $table->column_class('groupname', 'text-start');
    $table->column_class('lastname', 'text-start');
    $table->column_class('firstname', 'text-start');
    $table->column_class('idnumber', 'text-center');
    $table->column_class('access_time', 'text-center');
    $table->column_class('duration', 'text-end');

    // Personalizar formato de datos para visualización
    if (!$table->is_downloading()) {
        $table->define_baseurl($baseurl);

        echo html_writer::start_tag('div', ['class' => 'mt-4']);
        echo html_writer::tag('h4', 'Resultados del Reporte');

        // Agregar JavaScript para mejorar UX
        $PAGE->requires->js_init_call('M.util.init_toggle_class_on_click', [
            'id_courseid', 'form-control', 'form-control-changed'
        ]);
    }

    $table->out(25, true); // 25 registros por página

    if (!$table->is_downloading()) {
        // Mostrar estadísticas del reporte
        $totalrecords = $table->totalrows;
        if ($totalrecords > 0) {
            echo html_writer::tag('div',
                html_writer::tag('strong', 'Total de registros: ') . $totalrecords,
                ['class' => 'alert alert-info mt-3']
            );
        }
        echo html_writer::end_tag('div');
    }

} else if (!$table->is_downloading()) {
    echo html_writer::tag('div',
        html_writer::tag('i', '', ['class' => 'fa fa-info-circle me-2']) .
        'Seleccione al menos un filtro para generar el reporte.',
        ['class' => 'alert alert-info mt-4']
    );
}

if (!$table->is_downloading()) {
    echo $OUTPUT->footer();
}
?>
