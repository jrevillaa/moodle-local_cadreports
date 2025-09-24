<?php
/**
 * Tabla específica para reporte de accesos y dedicación
 * Plugin local_cadreports para Moodle 4.4
 */

namespace local_cadreports\tables;

use local_cadreports\base\table_base;
use local_cadreports\utils\time_calculator;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/cadreports/classes/base/table_base.php');
require_once($CFG->dirroot.'/local/cadreports/classes/utils/time_calculator.php');

/**
 * Tabla específica para mostrar accesos y dedicación
 */
class access_table extends table_base {

    /**
     * Configurar columnas específicas del reporte de accesos
     */
    protected function setup_specific_columns() {
        // Obtener columnas base y añadir columna de dedicación
        $base_columns = $this->get_base_columns();
        $base_columns['dedication'] = get_string('dedication', 'local_cadreports');

        // Configurar que la dedicación no es ordenable (cálculo dinámico)
        $this->no_sorting('rownum', 'dedication');

        return [array_keys($base_columns), array_values($base_columns)];
    }

    /**
     * Construir SQL específico del reporte de accesos
     */
    protected function build_specific_sql() {
        // Usar SQL base común
        list($fields, $from, $where, $params) = $this->build_base_sql();

        // Aplicar filtros comunes
        $this->apply_common_filters($where, $params);

        $this->set_sql($fields, $from, $where, $params);
    }

    /**
     * Procesar columnas específicas del reporte de accesos
     */
    public function other_cols($colname, $row) {
        // Primero procesar columnas base
        $base_result = parent::other_cols($colname, $row);
        if ($base_result !== null && $colname !== 'dedication') {
            return $base_result;
        }

        // Procesar columna específica
        switch ($colname) {
            case 'dedication':
                $datefrom = !empty($this->filters['datefrom']) ? $this->filters['datefrom'] : 0;
                $dateto = !empty($this->filters['dateto']) ? $this->filters['dateto'] : 0;

                $dedication_seconds = time_calculator::calculate_user_course_time(
                    $row->userid,
                    $row->courseid,
                    $datefrom,
                    $dateto
                );

                return time_calculator::format_duration($dedication_seconds);

            default:
                return isset($row->$colname) ? $row->$colname : '';
        }
    }

    /**
     * Formatear fila específica para exportación
     */
    protected function format_export_row($row) {
        // Calcular dedicación en formato detallado para exportación
        $datefrom = !empty($this->filters['datefrom']) ? $this->filters['datefrom'] : 0;
        $dateto = !empty($this->filters['dateto']) ? $this->filters['dateto'] : 0;

        $dedication_seconds = time_calculator::calculate_user_course_time(
            $row->userid,
            $row->courseid,
            $datefrom,
            $dateto
        );

        $row->dedication = time_calculator::format_duration($dedication_seconds, true);
    }
}