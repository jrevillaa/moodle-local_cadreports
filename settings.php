<?php
/**
 * Settings para local_cadreports - Sección root propia
 *
 * @package    local_cadreports
 * @copyright  2024 CAD
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {

    // Crear sección ROOT nueva "Reportes CAD" al nivel de Usuarios, Cursos, etc.
    $cadreports = new admin_category('cadperu', 'CAD Perú');
    $ADMIN->add('root', $cadreports);

    // Crear subsección "Reportes" dentro de "Reportes CAD"
    $reportssection = new admin_category('cadreports_reports', get_string('reports', 'local_cadreports'));
    $ADMIN->add('cadperu', $reportssection);

    // Reporte de Accesos y Dedicación
    $ADMIN->add('cadreports_reports',
        new admin_externalpage('local_cadreports_access',
            get_string('accessreport', 'local_cadreports'),
            new moodle_url('/local/cadreports/reports/access.php'),
            'local/cadreports:view'));

    // Reporte de Notas (para futuro)
    $ADMIN->add('cadreports_reports',
        new admin_externalpage('local_cadreports_grades',
            get_string('gradesreport', 'local_cadreports'),
            new moodle_url('/local/cadreports/reports/grades.php'),
            'local/cadreports:view'));

    // Reporte de Cuestionarios (para futuro)
    $ADMIN->add('cadreports_reports',
        new admin_externalpage('local_cadreports_quiz',
            get_string('quizreport', 'local_cadreports'),
            new moodle_url('/local/cadreports/reports/quiz.php'),
            'local/cadreports:view'));

    // Reporte de Actividad de Usuarios (para futuro)
    $ADMIN->add('cadreports_reports',
        new admin_externalpage('local_cadreports_activity',
            get_string('activityreport', 'local_cadreports'),
            new moodle_url('/local/cadreports/reports/activity.php'),
            'local/cadreports:view'));

    // Reporte de Participación en Foros (para futuro)
    $ADMIN->add('cadreports_reports',
        new admin_externalpage('local_cadreports_forum',
            get_string('forumreport', 'local_cadreports'),
            new moodle_url('/local/cadreports/reports/forum.php'),
            'local/cadreports:view'));

    // Opcional: Agregar configuración general del plugin
    $settingspage = new admin_settingpage('cadreports_settings', get_string('settings', 'local_cadreports'));

    if ($ADMIN->fulltree) {
        // Configuración del gap de sesión para cálculo de dedicación
        $settingspage->add(new admin_setting_configduration('local_cadreports/session_gap',
            get_string('session_gap', 'local_cadreports'),
            get_string('session_gap_desc', 'local_cadreports'),
            1800, // 30 minutos por defecto
            MINSECS)); // Mínimo 1 minuto

        // Configuración del número máximo de registros por página
        $settingspage->add(new admin_setting_configtext('local_cadreports/records_per_page',
            get_string('records_per_page', 'local_cadreports'),
            get_string('records_per_page_desc', 'local_cadreports'),
            25, // Por defecto 25
            PARAM_INT));
    }

    $ADMIN->add('cadperu', $settingspage);
}
