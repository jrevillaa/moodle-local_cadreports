<?php
require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/local/cadreports/classes/form/access_report_form.php');

require_login();
admin_externalpage_setup('cadreports_access');

$context = context_system::instance();
require_capability('local/cadreports:view', $context);

$PAGE->set_title(get_string('accessreport', 'local_cadreports'));
$PAGE->set_heading(get_string('accessreport', 'local_cadreports'));

// Inicializar el formulario
$mform = new \local_cadreports\form\access_report_form();

// Procesar datos del formulario
if ($data = $mform->get_data()) {
    // Procesar filtros y generar reporte
    process_report_data($data);
} else if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/cadreports/reports/access_report.php'));
}

echo $OUTPUT->header();

// Mostrar el formulario
$mform->display();

// Aquí va el código para mostrar los resultados del reporte
// si hay datos filtrados

echo $OUTPUT->footer();

function process_report_data($data) {
    // Implementar lógica del reporte basada en los filtros
    // $data->courseid contiene array de IDs de cursos
    // $data->groupid contiene array de IDs de grupos
    // $data->userid contiene array de IDs de usuarios
    // etc.
}
