<?php
/**
 * Página principal del reporte de participación en foros
 * Plugin local_cadreports para Moodle 4.4
 */

require_once('../../../config.php');
require_once($CFG->dirroot.'/local/cadreports/classes/autoload.php');

use local_cadreports\reports\forum_report;

// Crear y renderizar el reporte
$report = new forum_report();
$report->render();
