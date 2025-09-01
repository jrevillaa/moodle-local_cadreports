<?php
require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');

admin_externalpage_setup('local_cadreports');

$PAGE->set_url('/local/cadreports/index.php');
$PAGE->set_title(get_string('pluginname', 'local_cadreports'));
$PAGE->set_heading(get_string('reports_menu', 'local_cadreports'));

echo $OUTPUT->header();

// Menú de reportes
echo html_writer::start_tag('div', ['class' => 'cadreports-container']);
echo html_writer::tag('h2', get_string('reports_menu', 'local_cadreports'));

$reports = [
    'access' => [
        'title' => get_string('access_sessions', 'local_cadreports'),
        'description' => 'Reporte de accesos e ingresos al aula virtual por curso, grupo y participante',
        'url' => new moodle_url('/local/cadreports/reports/access_report.php')
    ],
    'grades' => [
        'title' => get_string('academic_grades', 'local_cadreports'),
        'description' => 'Registro de notas por curso/grupo con nomenclatura de módulos y unidades',
        'url' => new moodle_url('/local/cadreports/reports/grades_report.php')
    ],
    'quiz' => [
        'title' => get_string('quiz_summary', 'local_cadreports'),
        'description' => 'Resumen de cuestionarios con datos de participantes',
        'url' => new moodle_url('/local/cadreports/reports/quiz_report.php')
    ],
    'login' => [
        'title' => get_string('login_activity', 'local_cadreports'),
        'description' => 'Actividad de accesos de usuarios con contadores y último acceso',
        'url' => new moodle_url('/local/cadreports/reports/login_report.php')
    ],
    'forum' => [
        'title' => get_string('forum_participation', 'local_cadreports'),
        'description' => 'Participación en foros con estado de respuestas de docentes y administradores',
        'url' => new moodle_url('/local/cadreports/reports/forum_report.php')
    ]
];

echo html_writer::start_tag('div', ['class' => 'row']);

foreach ($reports as $key => $report) {
    echo html_writer::start_tag('div', ['class' => 'col-md-6 col-lg-4 mb-3']);
    echo html_writer::start_tag('div', ['class' => 'card h-100']);
    echo html_writer::start_tag('div', ['class' => 'card-body']);

    echo html_writer::tag('h5', $report['title'], ['class' => 'card-title']);
    echo html_writer::tag('p', $report['description'], ['class' => 'card-text']);

    echo html_writer::link($report['url'], 'Generar Reporte', [
        'class' => 'btn btn-primary'
    ]);

    echo html_writer::end_tag('div'); // card-body
    echo html_writer::end_tag('div'); // card
    echo html_writer::end_tag('div'); // col
}

echo html_writer::end_tag('div'); // row
echo html_writer::end_tag('div'); // container

echo $OUTPUT->footer();
?>
