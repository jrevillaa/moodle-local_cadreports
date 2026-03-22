<?php
/**
 * Reporte específico de actividad de usuarios - Usa arquitectura modular
 * Plugin local_cadreports para Moodle 4.4
 */

namespace local_cadreports\reports;

use local_cadreports\base\report_base;
use local_cadreports\tables\activity_table;
use local_cadreports\forms\activity_form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/cadreports/classes/base/report_base.php');
require_once($CFG->dirroot.'/local/cadreports/classes/tables/activity_table.php');
require_once($CFG->dirroot.'/local/cadreports/classes/forms/activity_form.php');

/**
 * Implementación específica del reporte de actividad de usuarios
 */
class activity_report extends report_base {

    /** @var activity_form Formulario específico */
    private $activity_form;

    /** @var activity_table Tabla específica */
    private $activity_table;

    /**
     * Constructor específico
     */
    public function __construct() {
        parent::__construct('activity');
    }

    /**
     * Filtros adicionales específicos del reporte de actividad
     */
    protected function get_additional_filters() {
        return [
            'action' => optional_param('action', '', PARAM_ALPHA),
            'component' => optional_param('component', '', PARAM_ALPHA)
        ];
    }

    /**
     * Obtener formulario específico del reporte de actividad
     */
    protected function get_form() {
        if (!$this->activity_form) {
            $this->activity_form = new activity_form();
        }
        return $this->activity_form;
    }

    /**
     * Obtener tabla específica del reporte de actividad
     */
    protected function get_table() {
        if (!$this->activity_table) {
            $this->activity_table = new activity_table('activity', $this->filters);
        }
        return $this->activity_table;
    }
}
