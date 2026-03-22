<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Configuración del menú de administración para local_cadreports
 *
 * Este archivo define las páginas de administración y sus enlaces en el menú
 * de administración del sitio para el plugin de reportes CAD.
 *
 * @package    local_cadreports
 * @copyright  2024 Jair Revilla <jrevilla492@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {

    // Verificar si la categoría 'cadperu' ya existe (puede haber sido creada por otro plugin)
    $cadperu = $ADMIN->locate('cadperu');
    
    if (!$cadperu) {
        // Si no existe, crear la categoría ROOT "CAD Perú" al nivel de Usuarios, Cursos, etc.
        $cadperu = new admin_category('cadperu', 'CAD Perú');
        $ADMIN->add('root', $cadperu);
    }

    // Crear subsección "Reportes" dentro de "Reportes CAD"
    $reportssection = new admin_category('cadreports_reports', get_string('reports', 'local_cadreports'));
    $ADMIN->add('cadperu', $reportssection);
    
    // Reporte de Notas (para futuro)
    $ADMIN->add('cadreports_reports',
        new admin_externalpage('local_cadreports_grades',
            get_string('gradesreport', 'local_cadreports'),
            new moodle_url('/local/cadreports/reports/grades.php'),
            'local/cadreports:view'));

    // Reporte de Exportación de Notas a ERP
    $ADMIN->add('cadreports_reports',
        new admin_externalpage('local_cadreports_erp_export',
            get_string('erp_export', 'local_cadreports'),
            new moodle_url('/local/cadreports/reports/erp_export.php'),
            'local/cadreports:view'));

    // Reporte de Accesos y Dedicación
    $ADMIN->add('cadreports_reports',
        new admin_externalpage('local_cadreports_access',
            get_string('accessreport', 'local_cadreports'),
            new moodle_url('/local/cadreports/reports/access.php'),
            'local/cadreports:view'));

    

    // Reporte de Cuestionarios (para futuro)
    /*$ADMIN->add('cadreports_reports',
        new admin_externalpage('local_cadreports_quiz',
            get_string('quizreport', 'local_cadreports'),
            new moodle_url('/local/cadreports/reports/quiz.php'),
            'local/cadreports:view')); */

    // Reporte de Actividad de Usuarios (para futuro)
    /*$ADMIN->add('cadreports_reports',
        new admin_externalpage('local_cadreports_activity',
            get_string('activityreport', 'local_cadreports'),
            new moodle_url('/local/cadreports/reports/activity.php'),
            'local/cadreports:view')); */

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

        // === NOTIFICACIONES DE FOROS ===
        $settingspage->add(new admin_setting_heading('local_cadreports/forum_notifications_heading',
            get_string('forum_notifications_heading', 'local_cadreports'),
            get_string('forum_notifications_heading_desc', 'local_cadreports')));

        // Habilitar/deshabilitar notificaciones
        $settingspage->add(new admin_setting_configcheckbox('local_cadreports/forum_notifications_enabled',
            get_string('forum_notifications_enabled', 'local_cadreports'),
            get_string('forum_notifications_enabled_desc', 'local_cadreports'),
            0)); // Deshabilitado por defecto

        // Correos destinatarios
        $settingspage->add(new admin_setting_configtextarea('local_cadreports/forum_notification_emails',
            get_string('forum_notification_emails', 'local_cadreports'),
            get_string('forum_notification_emails_desc', 'local_cadreports'),
            '', // Valor por defecto vacío
            PARAM_TEXT,
            60, // Ancho
            5)); // Alto (líneas)
    }

    $ADMIN->add('cadperu', $settingspage);
}
