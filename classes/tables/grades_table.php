<?php
/**
 * Tabla específica para reporte de notas - CORREGIDA
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

        // ✅ CORREGIDO: Agregar columnas específicas de notas (con idnumber)
        $grades_columns = [
            'activityname' => get_string('activityname', 'local_cadreports'),
            'activityidnumber' => get_string('activityidnumber', 'local_cadreports'), // ✅ NUEVO
            'activitytype' => get_string('activitytype', 'local_cadreports'),
            'grade' => get_string('gradefinal', 'local_cadreports'), // ✅ CORREGIDO: string no deprecado
            'maxgrade' => get_string('maxgrade', 'local_cadreports'),
            'percentage' => get_string('percentage', 'local_cadreports'),
            'timemodified' => get_string('timemodified', 'local_cadreports'),
            'modifiedby' => get_string('modifiedby', 'local_cadreports'), // ✅ USERNAME del que modificó
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
        global $DB;

        // ✅ CORREGIDO: SQL con referencias de columna correctas
        $fields = "CONCAT(u.id, '_', c.id, '_', COALESCE(gr.id, 0), '_', gi.id) as uniqueid,
               u.id as userid, 
               c.id as courseid,
               c.fullname as coursefullname,
               c.shortname as courseshortname,
               COALESCE(gr.name, '') as groupname,
               u.firstname,
               u.lastname, 
               u.username,
               u.email,
               gi.itemname as activityname,
               gi.idnumber as activityidnumber,
               gi.itemtype,
               gi.itemmodule as activitytype,
               gg.finalgrade as grade,
               gi.grademax as maxgrade,
               gg.timemodified,
               gg.usermodified,
               modifier.username as modifiedby,
               course_grade.finalgrade as finalgrade";

        // ✅ CORREGIDO: FROM mantiene aliases consistentes
        $from = "{course} c
             JOIN {enrol} e ON e.courseid = c.id
             JOIN {user_enrolments} ue ON ue.enrolid = e.id  
             JOIN {user} u ON u.id = ue.userid
             LEFT JOIN {groups_members} gm ON gm.userid = u.id AND gm.groupid IN (
                 SELECT id FROM {groups} WHERE courseid = c.id
             )
             LEFT JOIN {groups} gr ON gr.id = gm.groupid
             JOIN {grade_items} gi ON gi.courseid = c.id
             LEFT JOIN {grade_grades} gg ON gg.itemid = gi.id AND gg.userid = u.id
             LEFT JOIN {user} modifier ON modifier.id = gg.usermodified
             LEFT JOIN (
                 SELECT gg2.userid, gg2.finalgrade, gi2.courseid
                 FROM {grade_grades} gg2 
                 JOIN {grade_items} gi2 ON gi2.id = gg2.itemid 
                 WHERE gi2.itemtype = 'course'
             ) course_grade ON course_grade.userid = u.id AND course_grade.courseid = c.id";

        // ✅ CORREGIDO: WHERE más específico
        $where = "u.deleted = 0 AND u.suspended = 0 AND ue.status = 0
              AND gi.itemtype IN ('mod', 'manual', 'course')
              AND (gg.finalgrade IS NOT NULL OR gi.itemtype = 'course')";

        $params = [];

        // Aplicar filtros comunes
        $this->apply_common_filters($where, $params);

        // Aplicar filtros específicos de notas
        $this->apply_grades_filters($where, $params);

        $this->set_sql($fields, $from, $where, $params);
    }


    /**
     * ✅ NUEVO: Aplicar filtros específicos del reporte de notas
     */
    private function apply_grades_filters(&$where, &$params) {
        // Solo mostrar registros que tienen calificación o son curso total
        // Ya está incluido en el WHERE base

        // Filtro por rango de fechas de modificación de notas
        if (!empty($this->filters['datefrom'])) {
            $where .= " AND (gg.timemodified >= :grade_datefrom OR gi.itemtype = 'course')";
            $params['grade_datefrom'] = $this->filters['datefrom'];
        }

        if (!empty($this->filters['dateto'])) {
            $where .= " AND (gg.timemodified <= :grade_dateto OR gi.itemtype = 'course')";
            $params['grade_dateto'] = $this->filters['dateto'];
        }
    }

    /**
     * Procesar columnas específicas del reporte de notas
     */
    public function other_cols($colname, $row) {
        // Primero procesar columnas base
        $base_result = parent::other_cols($colname, $row);
        if ($base_result !== null && !in_array($colname, [
                'grade', 'percentage', 'timemodified', 'modifiedby', 'finalgrade',
                'activityname', 'activitytype', 'maxgrade', 'activityidnumber'
            ])) {
            return $base_result;
        }

        // Procesar columnas específicas de notas
        switch ($colname) {
            case 'grade':
                if ($row->grade !== null && $row->grade !== '') {
                    return number_format((float)$row->grade, 2);
                }
                return '-';

            case 'maxgrade':
                if ($row->maxgrade !== null && $row->maxgrade !== '') {
                    return number_format((float)$row->maxgrade, 2);
                }
                return '-';

            case 'percentage':
                if ($row->grade !== null && $row->grade !== '' && $row->maxgrade > 0) {
                    $percentage = ((float)$row->grade / (float)$row->maxgrade) * 100;
                    return number_format($percentage, 1) . '%';
                }
                return '-';

            case 'timemodified':
                // ✅ CORREGIDO: Verificar que sea un timestamp válido
                if (!empty($row->timemodified) && is_numeric($row->timemodified) && $row->timemodified > 0) {
                    return userdate((int)$row->timemodified, get_string('strftimedatetimeshort', 'core_langconfig'));
                }
                return '-';

            case 'modifiedby':
                if (!empty($row->modifiedby)) {
                    return $row->modifiedby;
                } else if (!empty($row->usermodified) && $row->usermodified != $row->userid) {
                    return $row->usermodified; // Mostrar el ID del usuario que modificó
                } else {
                    return 'Sistema';
                }

            case 'finalgrade':
                if ($row->finalgrade !== null && $row->finalgrade !== '') {
                    return number_format((float)$row->finalgrade, 2);
                }
                return '-';

            case 'activityname':
                if ($row->itemtype === 'course') {
                    return 'Total del curso';
                }
                return !empty($row->activityname) ? $row->activityname : '-';

            case 'activityidnumber':
                return !empty($row->activityidnumber) ? $row->activityidnumber : '-';

            case 'activitytype':
                if ($row->itemtype === 'manual') {
                    return 'Calificación Manual';
                } else if ($row->itemtype === 'course') {
                    return 'Total del curso';
                }
                return !empty($row->activitytype) ? $row->activitytype : '-';

            default:
                return isset($row->$colname) ? $row->$colname : '';
        }
    }

    /**
     * Formatear fila específica para exportación
     */
    protected function format_export_row($row) {
        // ✅ CORREGIDO: Formatear fechas verificando timestamp válido
        if (!empty($row->timemodified) && is_numeric($row->timemodified) && $row->timemodified > 0) {
            $row->timemodified = userdate((int)$row->timemodified, '%d/%m/%Y %H:%M');
        } else {
            $row->timemodified = '-';
        }

        // Formatear porcentaje para exportación
        if ($row->grade !== null && $row->grade !== '' && $row->maxgrade > 0) {
            $percentage = ((float)$row->grade / (float)$row->maxgrade) * 100;
            $row->percentage = number_format($percentage, 1) . '%';
        } else {
            $row->percentage = '-';
        }

        // Formatear notas con 2 decimales
        if ($row->grade !== null && $row->grade !== '') {
            $row->grade = number_format((float)$row->grade, 2);
        } else {
            $row->grade = '-';
        }

        if ($row->finalgrade !== null && $row->finalgrade !== '') {
            $row->finalgrade = number_format((float)$row->finalgrade, 2);
        } else {
            $row->finalgrade = '-';
        }

        if ($row->maxgrade !== null && $row->maxgrade !== '') {
            $row->maxgrade = number_format((float)$row->maxgrade, 2);
        } else {
            $row->maxgrade = '-';
        }

        // Formatear modifiedby para exportación
        if (!empty($row->modifiedby)) {
            // Ya está en formato correcto
        } else if (!empty($row->usermodified) && $row->usermodified != $row->userid) {
            $row->modifiedby = $row->usermodified;
        } else {
            $row->modifiedby = 'Sistema';
        }

        // Formatear otros campos
        $row->activityidnumber = !empty($row->activityidnumber) ? $row->activityidnumber : '-';

        if ($row->itemtype === 'course') {
            $row->activityname = 'Total del curso';
            $row->activitytype = 'Total del curso';
        } else {
            if (empty($row->activityname)) $row->activityname = '-';
            if ($row->itemtype === 'manual') {
                $row->activitytype = 'Calificación Manual';
            } else if (empty($row->activitytype)) {
                $row->activitytype = '-';
            }
        }
    }



}
