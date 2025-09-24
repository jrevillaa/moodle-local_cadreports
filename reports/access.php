<?php
/**
 * Página principal del reporte de accesos y dedicación
 * Plugin local_cadreports para Moodle 4.4
 */

require_once('../../../config.php');
global $CFG;
require_once($CFG->dirroot.'/local/cadreports/classes/reports/access_report.php');

use local_cadreports\reports\access_report;

// Crear y renderizar el reporte
$report = new access_report();
$report->render();
