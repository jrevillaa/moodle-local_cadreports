<?php
/**
 * Reporte específico de exportación de notas a ERP
 * Usa arquitectura modular con clases base
 *
 * @package    local_cadreports
 * @copyright  2024 Jair Revilla <jrevilla492@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cadreports\reports;

use local_cadreports\base\report_base;
use local_cadreports\tables\erp_export_table;
use local_cadreports\forms\erp_export_form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/cadreports/classes/base/report_base.php');
require_once($CFG->dirroot.'/local/cadreports/classes/tables/erp_export_table.php');
require_once($CFG->dirroot.'/local/cadreports/classes/forms/erp_export_form.php');

/**
 * Implementación específica del reporte de exportación a ERP
 * Extiende report_base para aprovechar funcionalidades comunes
 */
class erp_export_report extends report_base {

    /** @var erp_export_form Formulario específico */
    private $erp_export_form;

    /** @var erp_export_table Tabla específica */
    private $erp_export_table;

    /**
     * Constructor específico del reporte
     * Inicializa con el nombre 'erp_export'
     */
    public function __construct() {
        parent::__construct('erp_export');
    }

    /**
     * Filtros adicionales propios de este reporte
     * Los filtros comunes (mode, userquery, courseids, etc.) ya se procesan en report_base
     *
     * @return array Filtros adicionales específicos (vacío para este reporte)
     */
    protected function get_additional_filters() {
        return [];
    }

    /**
     * Obtener formulario específico del reporte de exportación a ERP
     *
     * @return erp_export_form Instancia del formulario
     */
    protected function get_form() {
        if (!$this->erp_export_form) {
            $this->erp_export_form = new erp_export_form();
        }
        return $this->erp_export_form;
    }

    /**
     * Obtener tabla específica del reporte de exportación a ERP
     *
     * @return erp_export_table Instancia de la tabla
     */
    protected function get_table() {
        if (!$this->erp_export_table) {
            $this->erp_export_table = new erp_export_table('erp_export', $this->filters);
        }
        return $this->erp_export_table;
    }
}
