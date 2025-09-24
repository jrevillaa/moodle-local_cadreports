<?php
/**
 * Reporte específico de participación en foros - Usa arquitectura modular
 * Plugin local_cadreports para Moodle 4.4
 */

namespace local_cadreports\reports;

use local_cadreports\base\report_base;
use local_cadreports\tables\forum_table;
use local_cadreports\forms\forum_form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/cadreports/classes/base/report_base.php');
require_once($CFG->dirroot.'/local/cadreports/classes/tables/forum_table.php');
require_once($CFG->dirroot.'/local/cadreports/classes/forms/forum_form.php');

/**
 * Implementación específica del reporte de participación en foros
 */
class forum_report extends report_base {

    /** @var forum_form Formulario específico */
    private $forum_form;

    /** @var forum_table Tabla específica */
    private $forum_table;

    /**
     * Constructor específico
     */
    public function __construct() {
        parent::__construct('forum');
    }

    /**
     * Filtros adicionales específicos del reporte de foros
     */
    protected function get_additional_filters() {
        return [
            'participation_status' => optional_param('participation_status', '', PARAM_ALPHA)
        ];
    }

    /**
     * Obtener formulario específico del reporte de foros
     */
    protected function get_form() {
        if (!$this->forum_form) {
            $this->forum_form = new forum_form();
        }
        return $this->forum_form;
    }

    /**
     * Obtener tabla específica del reporte de foros
     */
    protected function get_table() {
        if (!$this->forum_table) {
            $this->forum_table = new forum_table('forum', $this->filters);
        }
        return $this->forum_table;
    }
}
