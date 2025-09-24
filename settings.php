<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    // Crear categoría principal para CAD Reports
    $ADMIN->add('localplugins', new admin_category('cadreports',
        get_string('pluginname', 'local_cadreports')));

    // Página de configuración principal (opcional)
    $settings = new admin_settingpage('local_cadreports',
        get_string('settings', 'local_cadreports'));

    if ($ADMIN->fulltree) {
        // Aquí puedes agregar configuraciones si las necesitas
    }

    $ADMIN->add('cadreports', $settings);

    // Registrar páginas de reportes como páginas externas
    $ADMIN->add('cadreports', new admin_externalpage('cadreports_access',
        get_string('accessreport', 'local_cadreports'),
        new moodle_url('/local/cadreports/reports/access_report.php'),
        'local/cadreports:view'));

    $ADMIN->add('cadreports', new admin_externalpage('cadreports_grades',
        get_string('gradesreport', 'local_cadreports'),
        new moodle_url('/local/cadreports/reports/grades_report.php'),
        'local/cadreports:view'));

    $ADMIN->add('cadreports', new admin_externalpage('cadreports_quiz',
        get_string('quizreport', 'local_cadreports'),
        new moodle_url('/local/cadreports/reports/quiz_report.php'),
        'local/cadreports:view'));

    $ADMIN->add('cadreports', new admin_externalpage('cadreports_activity',
        get_string('activityreport', 'local_cadreports'),
        new moodle_url('/local/cadreports/reports/activity_report.php'),
        'local/cadreports:view'));

    $ADMIN->add('cadreports', new admin_externalpage('cadreports_forum',
        get_string('forumreport', 'local_cadreports'),
        new moodle_url('/local/cadreports/reports/forum_report.php'),
        'local/cadreports:view'));
}
