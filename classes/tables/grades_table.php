<?php
/**
 * Tabla específica para reporte de notas
 * Plugin local_cadreports para Moodle 4.4
 */

namespace local_cadreports\tables;

use local_cadreports\base\table_base;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/cadreports/classes/base/table_base.php');

/**
 * Tabla específica para mostrar notas
 */
class grades_table extends table_base {

    /**
     * Configurar columnas específicas del reporte de notas
     */
    protected function setup_specific_columns() {
        // Obtener columnas base y añadir columnas de notas
        $base_columns = $this->get_base_columns();

        // Agregar columnas específicas de notas
        $grades_columns = [
            'activityname' => get_string('activityname', 'local_cadreports'),
            'activitytype' => get_string('activitytype', 'local_cadreports'),
            'grade' => get_string('grade', 'core_grades'),
            'maxgrade' => get_string('maxgrade', 'local_cadreports'),
            'percentage' => get_string('percentage', 'local_cadreports'),
            'timemodified' => get_string('timemodified', 'local_cadreports'),
            'modifiedby' => get_string('modifiedby', 'local_cadreports'),
            'finalgrade' => get_string('finalgrade', 'local_cadreports')
        ];

        $all_columns = array_merge($base_columns, $grades_columns);

        // Configurar que algunas columnas no son ordenables
        $this->no_sorting('rownum', 'percentage');

        return [array_keys($all_columns), array_values($all_columns)];
    }

    /**
     * Construir SQL específico del reporte de notas
     */
    protected function build_specific_sql() {
        // Usar SQL base común y extender para notas
        list($fields, $from, $where, $params) = $this->build_base_sql();

        // Extender campos para incluir información de calificaciones
        $fields .= ", gi.itemname as activityname,
                     gi.itemtype,
                     gi.itemmodule as activitytype,
                     gg.finalgrade as grade,
                     gi.grademax as maxgrade,
                     gg.timemodified,
                     gg.usermodified,
                     modifier.username as modifiedby,
                     course_grade.finalgrade as finalgrade";

        // Extender FROM para incluir tablas de calificaciones
        $from .= " JOIN {grade_items} gi ON gi.courseid = c.id
                   LEFT JOIN {grade_grades} gg ON gg.itemid = gi.id AND gg.userid = u.id
                   LEFT JOIN {user} modifier ON modifier.id = gg.usermodified
                   LEFT JOIN (
                       SELECT gg2.userid, gg2.finalgrade, gi2.courseid
                       FROM {grade_grades} gg2 
                       JOIN {grade_items} gi2 ON gi2.id = gg2.itemid 
                       WHERE gi2.itemtype = 'course'
                   ) course_grade ON course_grade.userid = u.id AND course_grade.courseid = c.id";

        // Extender WHERE para filtrar calificaciones válidas
        $where .= " AND gi.itemtype IN ('mod', 'manual', 'course')";

        // Aplicar filtros comunes
        $this->apply_common_filters($where, $params);

        // Aplicar filtros específicos de notas
        $this->apply_grades_filters($where, $params);

        $this->set_sql($fields, $from, $where, $params);
    }

    /**
     * Aplicar filtros específicos del reporte de notas
     */
    private function apply_grades_filters(&$where, &$params) {
        // Filtro por tipo de actividad
        if (!empty($this->filters['activitytype'])) {
            if ($this->filters['activitytype'] === 'manual') {
                $where .= " AND gi.itemtype = :itemtype";
                $params['itemtype'] = 'manual';
            } else {
                $where .= " AND gi.itemmodule = :itemmodule";
                $params['itemmodule'] = $this->filters['activitytype'];
            }
        }

        // Filtro por solo calificaciones modificadas
        if (!empty($this->filters['modified_only'])) {
            $where .= " AND gg.usermodified IS NOT NULL AND gg.usermodified != gg.userid";
        }

        // Filtro por rango de fechas de modificación
        if (!empty($this->filters['datefrom'])) {
            $where .= " AND gg.timemodified >= :grade_datefrom";
            $params['grade_datefrom'] = $this->filters['datefrom'];
        }

        if (!empty($this->filters['dateto'])) {
            $where .= " AND gg.timemodified <= :grade_dateto";
            $params['grade_dateto'] = $this->filters['dateto'];
        }
    }

    /**
     * Procesar columnas específicas del reporte de notas
     */
    public function other_cols($colname, $row) {
        // Primero procesar columnas base
        $base_result = parent::other_cols($colname, $row);
        if ($base_result !== null && !in_array($colname, ['grade', 'percentage', 'timemodified', 'modifiedby', 'finalgrade', 'activityname', 'activitytype', 'maxgrade'])) {
            return $base_result;
        }

        // Procesar columnas específicas de notas
        switch ($colname) {
            case 'grade':
                if ($row->grade !== null) {
                    return number_format($row->grade, 2);
                }
                return '-';

            case 'maxgrade':
                return number_format($row->maxgrade, 2);

            case 'percentage':
                if ($row->grade !== null && $row->maxgrade > 0) {
                    $percentage = ($row->grade / $row->maxgrade) * 100;
                    return number_format($percentage, 1) . '%';
                }
                return '-';

            case 'timemodified':
                if ($row->timemodified) {
                    return userdate($row->timemodified, get_string('strftimedatetimeshort', 'core_langconfig'));
                }
                return '-';

            case 'modifiedby':
                return $row->modifiedby ? $row->modifiedby : get_string('system', 'core');

            case 'finalgrade':
                if ($row->finalgrade !== null) {
                    return number_format($row->finalgrade, 2);
                }
                return '-';

            case 'activityname':
                // Si es calificación del curso, mostrar nombre especial
                if ($row->itemtype === 'course') {
                    return get_string('coursetotal', 'core_grades');
                }
                return $row->activityname;

            case 'activitytype':
                if ($row->itemtype === 'manual') {
                    return get_string('manualgrade', 'local_cadreports');
                } else if ($row->itemtype === 'course') {
                    return get_string('coursetotal', 'core_grades');
                }
                return $row->activitytype;

            default:
                return isset($row->$colname) ? $row->$colname : '';
        }
    }

    /**
     * Formatear fila específica para exportación
     */
    protected function format_export_row($row) {
        // Formatear fechas para exportación
        if ($row->timemodified) {
            $row->timemodified = userdate($row->timemodified, '%d/%m/%Y %H:%M');
        }

        // Formatear porcentaje para exportación
        if ($row->grade !== null && $row->maxgrade > 0) {
            $percentage = ($row->grade / $row->maxgrade) * 100;
            $row->percentage = number_format($percentage, 1) . '%';
        } else {
            $row->percentage = '-';
        }

        // Formatear notas con 2 decimales
        if ($row->grade !== null) {
            $row->grade = number_format($row->grade, 2);
        }
        if ($row->finalgrade !== null) {
            $row->finalgrade = number_format($row->finalgrade, 2);
        }
        if ($row->maxgrade !== null) {
            $row->maxgrade = number_format($row->maxgrade, 2);
        }
    }
}
