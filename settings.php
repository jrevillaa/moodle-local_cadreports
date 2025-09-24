<?php
/**
 * Settings para local_cadreports - Estructura modular
 *
 * @package    local_cadreports
 * @copyright  2024 CAD
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {

    // Crear categoría principal para CAD Reports
    $ADMIN->add('root', new admin_category('local_cadreports',
        get_string('cadreports', 'local_cadreports')));

    // Reporte de Accesos y Dedicación
    $ADMIN->add('local_cadreports',
        new admin_externalpage('local_cadreports_access',
            get_string('accessreport', 'local_cadreports'),
            new moodle_url('/local/cadreports/reports/access.php'),
            'local/cadreports:view'));

    // Reporte de Notas (para futuro)
    $ADMIN->add('local_cadreports',
        new admin_externalpage('local_cadreports_grades',
            get_string('gradesreport', 'local_cadreports'),
            new moodle_url('/local/cadreports/reports/grades.php'),
            'local/cadreports:view'));

    // Reporte de Cuestionarios (para futuro)
    $ADMIN->add('local_cadreports',
        new admin_externalpage('local_cadreports_quiz',
            get_string('quizreport', 'local_cadreports'),
            new moodle_url('/local/cadreports/reports/quiz.php'),
            'local/cadreports:view'));

    // Reporte de Actividad de Usuarios (para futuro)
    $ADMIN->add('local_cadreports',
        new admin_externalpage('local_cadreports_activity',
            get_string('activityreport', 'local_cadreports'),
            new moodle_url('/local/cadreports/reports/activity.php'),
            'local/cadreports:view'));

    // Reporte de Participación en Foros (para futuro)
    $ADMIN->add('local_cadreports',
        new admin_externalpage('local_cadreports_forum',
            get_string('forumreport', 'local_cadreports'),
            new moodle_url('/local/cadreports/reports/forum.php'),
            'local/cadreports:view'));
}
