<?php
/**
 * Reporte específico de cuestionarios - Usa arquitectura modular
 * Plugin local_cadreports para Moodle 4.4
 */

namespace local_cadreports\reports;

use local_cadreports\base\report_base;
use local_cadreports\tables\quiz_table;
use local_cadreports\forms\quiz_form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/cadreports/classes/base/report_base.php');
require_once($CFG->dirroot.'/local/cadreports/classes/tables/quiz_table.php');
require_once($CFG->dirroot.'/local/cadreports/classes/forms/quiz_form.php');

/**
 * Implementación específica del reporte de cuestionarios
 */
class quiz_report extends report_base {

    /** @var quiz_form Formulario específico */
    private $quiz_form;

    /** @var quiz_table Tabla específica */
    private $quiz_table;

    /**
     * Constructor específico
     */
    public function __construct() {
        parent::__construct('quiz');
    }

    /**
     * Filtros adicionales específicos del reporte de cuestionarios
     */
    protected function get_additional_filters() {
        return [
            // Sin filtros adicionales específicos por ahora
        ];
    }

    /**
     * Obtener formulario específico del reporte de cuestionarios
     */
    protected function get_form() {
        if (!$this->quiz_form) {
            $this->quiz_form = new quiz_form();
        }
        return $this->quiz_form;
    }

    /**
     * Obtener tabla específica del reporte de cuestionarios
     */
    protected function get_table() {
        if (!$this->quiz_table) {
            $this->quiz_table = new quiz_table('quiz', $this->filters);
        }
        return $this->quiz_table;
    }
}
