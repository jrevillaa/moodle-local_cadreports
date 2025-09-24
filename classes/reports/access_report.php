<?php
/**
 * Reporte específico de accesos y dedicación - Usa arquitectura modular
 * Plugin local_cadreports para Moodle 4.4
 */

namespace local_cadreports\reports;

use local_cadreports\base\report_base;
use local_cadreports\tables\access_table;
use local_cadreports\forms\access_form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/cadreports/classes/base/report_base.php');
require_once($CFG->dirroot.'/local/cadreports/classes/tables/access_table.php');
require_once($CFG->dirroot.'/local/cadreports/classes/forms/access_form.php');

/**
 * Implementación específica del reporte de accesos
 */
class access_report extends report_base {

    /** @var access_form Formulario específico */
    private $access_form;

    /** @var access_table Tabla específica */
    private $access_table;

    /**
     * Constructor específico
     */
    public function __construct() {
        parent::__construct('access');
    }

    /**
     * No hay filtros adicionales para el reporte de accesos
     */
    protected function get_additional_filters() {
        return [];
    }

    /**
     * Obtener formulario específico del reporte de accesos
     */
    protected function get_form() {
        if (!$this->access_form) {
            $this->access_form = new access_form();
        }
        return $this->access_form;
    }

    /**
     * Obtener tabla específica del reporte de accesos
     */
    protected function get_table() {
        if (!$this->access_table) {
            $this->access_table = new access_table('access', $this->filters);
        }
        return $this->access_table;
    }
}
