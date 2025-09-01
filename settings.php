<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {

    // Crear categoría principal CAD
    $ADMIN->add('reports', new admin_category('cadreports',
        get_string('pluginname', 'local_cadreports')));

    // Agregar página de reportes
    $ADMIN->add('cadreports', new admin_externalpage(
        'local_cadreports',
        get_string('reports_menu', 'local_cadreports'),
        new moodle_url('/local/cadreports/index.php'),
        'local/cadreports:view'
    ));
}
?>
