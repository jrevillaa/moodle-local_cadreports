<?php
defined('MOODLE_INTERNAL') || die();

/** @var TYPE_NAME $hassiteconfig */
if ($hassiteconfig) {
    // Agregar al menú de reportes
    /** @var TYPE_NAME $ADMIN */
    if ($ADMIN->locate('reports')) {
        $ADMIN->add('reports', new admin_category('cadreports',
            get_string('pluginname', 'local_cadreports')));

        // Reporte de accesos
        $ADMIN->add('cadreports', new admin_externalpage(
            'cadreports_access',
            get_string('access_sessions', 'local_cadreports'),
            new moodle_url('/local/cadreports/reports/access_report.php'),
            ['local/cadreports:view', 'local/cadreports:viewall']
        ));

    }
}
