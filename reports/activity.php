<?php
/**
 * Página principal del reporte de actividad de usuarios
 * Plugin local_cadreports para Moodle 4.4
 */

require_once('../../../config.php');
require_once($CFG->dirroot.'/local/cadreports/classes/autoload.php');

use local_cadreports\reports\activity_report;

// Crear y renderizar el reporte
$report = new activity_report();
$report->render();
