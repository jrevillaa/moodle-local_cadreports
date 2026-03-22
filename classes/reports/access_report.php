<?php
/**
 * Reporte específico de accesos y dedicación
 * Usa arquitectura modular con clases base
 *
 * @package    local_cadreports
 * @copyright  2024 Jair Revilla <jrevilla492@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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
 * Implementación específica del reporte de accesos y dedicación
 * Extiende report_base para aprovechar funcionalidades comunes
 */
class access_report extends report_base {

    /** @var access_form Formulario específico */
    private $access_form;

    /** @var access_table Tabla específica */
    private $access_table;

    /**
     * Constructor específico del reporte
     */
    public function __construct() {
        parent::__construct('access');
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
     * Obtener formulario específico del reporte de accesos
     *
     * @return access_form Instancia del formulario
     */
    protected function get_form() {
        if (!$this->access_form) {
            $this->access_form = new access_form();
        }
        return $this->access_form;
    }

    /**
     * Obtener tabla específica del reporte de accesos
     *
     * @return access_table Instancia de la tabla
     */
    protected function get_table() {
        if (!$this->access_table) {
            $this->access_table = new access_table('access', $this->filters);
        }
        return $this->access_table;
    }
}
