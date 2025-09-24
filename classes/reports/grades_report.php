<?php
/**
 * Reporte específico de notas - Usa arquitectura modular
 * Plugin local_cadreports para Moodle 4.4
 */

namespace local_cadreports\reports;

use local_cadreports\base\report_base;
use local_cadreports\tables\grades_table;
use local_cadreports\forms\grades_form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/cadreports/classes/base/report_base.php');
require_once($CFG->dirroot.'/local/cadreports/classes/tables/grades_table.php');
require_once($CFG->dirroot.'/local/cadreports/classes/forms/grades_form.php');

/**
 * Implementación específica del reporte de notas
 */
class grades_report extends report_base {

    /** @var grades_form Formulario específico */
    private $grades_form;

    /** @var grades_table Tabla específica */
    private $grades_table;

    /**
     * Constructor específico
     */
    public function __construct() {
        parent::__construct('grades');
    }

    /**
     * Filtros adicionales específicos del reporte de notas
     */
    protected function get_additional_filters() {
        return [
            'activitytype' => optional_param('activitytype', '', PARAM_ALPHA),
            'gradeitemid' => optional_param('gradeitemid', 0, PARAM_INT)
        ];
    }

    /**
     * Obtener formulario específico del reporte de notas
     */
    protected function get_form() {
        if (!$this->grades_form) {
            $this->grades_form = new grades_form();
        }
        return $this->grades_form;
    }

    /**
     * Obtener tabla específica del reporte de notas
     */
    protected function get_table() {
        if (!$this->grades_table) {
            $this->grades_table = new grades_table('grades', $this->filters);
        }
        return $this->grades_table;
    }
}
