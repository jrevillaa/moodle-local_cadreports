<?php
/**
 * Página principal del reporte de exportación de notas a ERP
 *
 * @package    local_cadreports
 * @copyright  2024 Jair Revilla <jrevilla492@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/cadreports/classes/reports/erp_export_report.php');

use local_cadreports\reports\erp_export_report;

// Configuración de la página
admin_externalpage_setup('local_cadreports_erp_export');

$PAGE->set_url(new moodle_url('/local/cadreports/reports/erp_export.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('erp_export', 'local_cadreports'));
$PAGE->set_heading(get_string('erp_export', 'local_cadreports'));

// Verificar permisos
require_capability('local/cadreports:view', context_system::instance());

// Crear instancia del reporte usando la arquitectura base
$report = new erp_export_report();

// Renderizar el reporte completo (formulario + tabla + exportación)
// El método render() está en report_base y hace:
// - Configurar página
// - Verificar permisos
// - Mostrar header
// - Mostrar formulario
// - Mostrar tabla si hay filtros
// - Manejar descargas
$report->render();
