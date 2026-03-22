<?php
/**
 * Página principal del reporte de cuestionarios
 * Plugin local_cadreports para Moodle 4.4
 */

require_once('../../../config.php');
require_once($CFG->dirroot.'/local/cadreports/classes/autoload.php');

use local_cadreports\reports\quiz_report;

// Crear y renderizar el reporte
$report = new quiz_report();
$report->render();
