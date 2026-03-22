<?php
/**
 * Página principal del reporte de notas
 * Plugin local_cadreports para Moodle 4.4
 */

require_once('../../../config.php');
require_once($CFG->dirroot.'/local/cadreports/classes/autoload.php');

use local_cadreports\reports\grades_report;

// Crear y renderizar el reporte
$report = new grades_report();
$report->render();
