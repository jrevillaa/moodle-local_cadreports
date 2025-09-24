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
        // Obtener columnas base y añadir columna de dedicación y accesos
        $base_columns = $this->get_base_columns();

        // ✅ ACTUALIZADO: Agregar columnas de accesos
        $access_columns = [
            'dedication' => get_string('dedication', 'local_cadreports'),
            'course_accesses' => get_string('courseaccesses', 'local_cadreports'), // ✅ NUEVO
            'last_course_access' => get_string('lastcourseaccess', 'local_cadreports') // ✅ NUEVO
        ];

        $all_columns = array_merge($base_columns, $access_columns);

        // Configurar que algunas columnas no son ordenables
        $this->no_sorting('rownum');

        return [array_keys($all_columns), array_values($all_columns)];
    }


    /**
     * Construir SQL específico del reporte de accesos
     */
    protected function build_specific_sql() {
        // Usar SQL base común y extender para accesos
        list($fields, $from, $where, $params) = $this->build_base_sql();

        // ✅ CORREGIDO: Extender campos SIN funciones Window (compatibilidad MariaDB)
        $fields .= ", COALESCE(dedication_calc.total_time, 0) as dedication,
                  COALESCE(access_count.total_accesses, 0) as course_accesses,
                  COALESCE(ula.timeaccess, 0) as last_course_access";

        // ✅ SIMPLIFICADO: Subconsulta para dedicación sin LEAD() ni OVER()
        $from .= " LEFT JOIN (
                   SELECT 
                       userid, 
                       courseid,
                       COUNT(*) * " . get_config('local_cadreports', 'session_gap', 1800) . " as total_time
                   FROM {logstore_standard_log}
                   WHERE action = 'viewed' 
                   AND target IN ('course', 'course_module')";

        // Aplicar filtro de fechas en la subconsulta si está presente
        if (!empty($this->filters['datefrom'])) {
            $from .= " AND timecreated >= :dedication_datefrom";
            $params['dedication_datefrom'] = $this->filters['datefrom'];
        }

        if (!empty($this->filters['dateto'])) {
            $from .= " AND timecreated <= :dedication_dateto";
            $params['dedication_dateto'] = $this->filters['dateto'];
        }

        $from .= " GROUP BY userid, courseid
               ) dedication_calc ON dedication_calc.userid = u.id AND dedication_calc.courseid = c.id";

        // ✅ CORREGIDO: Subconsulta para contar accesos al curso
        $from .= " LEFT JOIN (
                   SELECT 
                       userid,
                       courseid,
                       COUNT(*) as total_accesses
                   FROM {logstore_standard_log}
                   WHERE action = 'viewed' 
                   AND target = 'course'";

        // Aplicar filtro de fechas para contar accesos
        if (!empty($this->filters['datefrom'])) {
            $from .= " AND timecreated >= :access_count_datefrom";
            $params['access_count_datefrom'] = $this->filters['datefrom'];
        }

        if (!empty($this->filters['dateto'])) {
            $from .= " AND timecreated <= :access_count_dateto";
            $params['access_count_dateto'] = $this->filters['dateto'];
        }

        $from .= " GROUP BY userid, courseid
               ) access_count ON access_count.userid = u.id AND access_count.courseid = c.id";

        // ✅ ÚLTIMO ACCESO: Usar tabla user_lastaccess
        $from .= " LEFT JOIN {user_lastaccess} ula ON ula.userid = u.id AND ula.courseid = c.id";

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
        if ($base_result !== null && !in_array($colname, ['dedication', 'course_accesses', 'last_course_access'])) {
            return $base_result;
        }

        // Procesar columnas específicas de accesos
        switch ($colname) {
            case 'dedication':
                return \local_cadreports\utils\time_calculator::format_duration($row->dedication);

            case 'course_accesses':
                // ✅ NUEVO: Mostrar cantidad de accesos al curso
                return (int)$row->course_accesses;

            case 'last_course_access':
                // ✅ NUEVO: Mostrar último acceso al curso
                if (!empty($row->last_course_access) && $row->last_course_access > 0) {
                    return userdate($row->last_course_access, get_string('strftimedatetimeshort', 'core_langconfig'));
                }
                return get_string('never', 'core');

            default:
                return isset($row->$colname) ? $row->$colname : '';
        }
    }


    /**
     * Formatear fila específica para exportación
     */
    protected function format_export_row($row) {
        // Formatear dedicación para exportación usando formato detallado
        $row->dedication = \local_cadreports\utils\time_calculator::format_duration($row->dedication, true);

        // ✅ NUEVO: Formatear accesos para exportación
        $row->course_accesses = (int)$row->course_accesses;

        // ✅ NUEVO: Formatear último acceso para exportación
        if (!empty($row->last_course_access) && $row->last_course_access > 0) {
            $row->last_course_access = userdate($row->last_course_access, '%d/%m/%Y %H:%M');
        } else {
            $row->last_course_access = 'Nunca';
        }
    }

}