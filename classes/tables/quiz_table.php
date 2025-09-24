<?php
/**
 * Tabla específica para reporte de cuestionarios
 * Plugin local_cadreports para Moodle 4.4
 */

namespace local_cadreports\tables;

use local_cadreports\base\table_base;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/cadreports/classes/base/table_base.php');

/**
 * Tabla específica para mostrar cuestionarios
 */
class quiz_table extends table_base {

    /**
     * Configurar columnas específicas del reporte de cuestionarios
     */
    protected function setup_specific_columns() {
        // Obtener columnas base y añadir columnas de cuestionarios
        $base_columns = $this->get_base_columns();

        // Agregar columnas específicas de cuestionarios
        $quiz_columns = [
            'quizname' => get_string('quizname', 'local_cadreports'),
            'quizidnumber' => get_string('quizidnumber', 'local_cadreports'),
            'attempts_made' => get_string('attemptsmade', 'local_cadreports'),
            'attempts_allowed' => get_string('attemptsallowed', 'local_cadreports'),
            'best_grade' => get_string('bestgrade', 'local_cadreports'),
            'latest_attempt' => get_string('latestattempt', 'local_cadreports')
        ];

        $all_columns = array_merge($base_columns, $quiz_columns);

        // Configurar que algunas columnas no son ordenables
        $this->no_sorting('rownum');

        return [array_keys($all_columns), array_values($all_columns)];
    }

    /**
     * Construir SQL específico del reporte de cuestionarios
     */
    protected function build_specific_sql() {
        global $DB;

        $params = [];

        // ✅ CORREGIDO: SQL con course_modules para obtener idnumber
        $fields = "CONCAT(u.id, '_', c.id, '_', COALESCE(gr.id, 0), '_', q.id) as uniqueid,
               u.id as userid, 
               c.id as courseid,
               c.fullname as coursefullname,
               c.shortname as courseshortname,
               COALESCE(gr.name, '') as groupname,
               u.firstname,
               u.lastname, 
               u.username,
               u.email,
               q.name as quizname,
               COALESCE(cm.idnumber, '') as quizidnumber,
               COALESCE(attempts_count.attempts_made, 0) as attempts_made,
               q.attempts as attempts_allowed,
               COALESCE(best_attempt.best_grade, 0) as best_grade,
               COALESCE(latest_attempt.timemodified, 0) as latest_attempt";

        // ✅ CORREGIDO: FROM con course_modules JOIN para idnumber
        $from = "{course} c
             JOIN {enrol} e ON e.courseid = c.id
             JOIN {user_enrolments} ue ON ue.enrolid = e.id  
             JOIN {user} u ON u.id = ue.userid
             LEFT JOIN {groups_members} gm ON gm.userid = u.id AND gm.groupid IN (
                 SELECT id FROM {groups} WHERE courseid = c.id
             )
             LEFT JOIN {groups} gr ON gr.id = gm.groupid
             JOIN {quiz} q ON q.course = c.id
             LEFT JOIN {course_modules} cm ON cm.course = c.id 
                       AND cm.module = (SELECT id FROM {modules} WHERE name = 'quiz') 
                       AND cm.instance = q.id
             LEFT JOIN (
                 SELECT 
                     userid, 
                     quiz,
                     COUNT(*) as attempts_made
                 FROM {quiz_attempts}
                 WHERE state IN ('finished', 'abandoned')";

        // Aplicar filtros de fecha para intentos
        if (!empty($this->filters['datefrom'])) {
            $from .= " AND timemodified >= :attempts_datefrom";
            $params['attempts_datefrom'] = $this->filters['datefrom'];
        }

        if (!empty($this->filters['dateto'])) {
            $from .= " AND timemodified <= :attempts_dateto";
            $params['attempts_dateto'] = $this->filters['dateto'];
        }

        $from .= "     GROUP BY userid, quiz
             ) attempts_count ON attempts_count.userid = u.id AND attempts_count.quiz = q.id
             LEFT JOIN (
                 SELECT 
                     userid,
                     quiz,
                     MAX(sumgrades) as best_grade
                 FROM {quiz_attempts}
                 WHERE state = 'finished'";

        // Aplicar filtros de fecha para mejor nota
        if (!empty($this->filters['datefrom'])) {
            $from .= " AND timemodified >= :best_grade_datefrom";
            $params['best_grade_datefrom'] = $this->filters['datefrom'];
        }

        if (!empty($this->filters['dateto'])) {
            $from .= " AND timemodified <= :best_grade_dateto";
            $params['best_grade_dateto'] = $this->filters['dateto'];
        }

        $from .= "     GROUP BY userid, quiz
             ) best_attempt ON best_attempt.userid = u.id AND best_attempt.quiz = q.id
             LEFT JOIN (
                 SELECT 
                     userid,
                     quiz,
                     MAX(timemodified) as timemodified
                 FROM {quiz_attempts}
                 WHERE state IN ('finished', 'abandoned')";

        // Aplicar filtros de fecha para último intento
        if (!empty($this->filters['datefrom'])) {
            $from .= " AND timemodified >= :latest_attempt_datefrom";
            $params['latest_attempt_datefrom'] = $this->filters['datefrom'];
        }

        if (!empty($this->filters['dateto'])) {
            $from .= " AND timemodified <= :latest_attempt_dateto";
            $params['latest_attempt_dateto'] = $this->filters['dateto'];
        }

        $from .= "     GROUP BY userid, quiz
             ) latest_attempt ON latest_attempt.userid = u.id AND latest_attempt.quiz = q.id";

        // WHERE base
        $where = "u.deleted = 0 AND u.suspended = 0 AND ue.status = 0";

        // Aplicar filtros comunes
        $this->apply_common_filters($where, $params);

        $this->set_sql($fields, $from, $where, $params);
    }



    /**
     * Procesar columnas específicas del reporte de cuestionarios
     */
    public function other_cols($colname, $row) {
        // Primero procesar columnas base
        $base_result = parent::other_cols($colname, $row);
        if ($base_result !== null && !in_array($colname, [
                'quizname', 'quizidnumber', 'attempts_made', 'attempts_allowed',
                'best_grade', 'latest_attempt'
            ])) {
            return $base_result;
        }

        // Procesar columnas específicas de cuestionarios
        switch ($colname) {
            case 'quizname':
                return !empty($row->quizname) ? $row->quizname : '-';

            case 'quizidnumber':
                return !empty($row->quizidnumber) ? $row->quizidnumber : '-';

            case 'attempts_made':
                return (int)$row->attempts_made;

            case 'attempts_allowed':
                if ($row->attempts_allowed == 0) {
                    return get_string('unlimited', 'core');
                }
                return (int)$row->attempts_allowed;

            case 'best_grade':
                if ($row->best_grade > 0) {
                    return number_format((float)$row->best_grade, 2);
                }
                return '-';

            case 'latest_attempt':
                // ✅ CORREGIDO: Validar que sea timestamp numérico válido
                if (!empty($row->latest_attempt) && is_numeric($row->latest_attempt) && $row->latest_attempt > 0) {
                    return userdate((int)$row->latest_attempt, get_string('strftimedatetimeshort', 'core_langconfig'));
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
        global $DB;

        // Formatear campos básicos
        $row->quizname = !empty($row->quizname) ? $row->quizname : '-';
        $row->quizidnumber = !empty($row->quizidnumber) ? $row->quizidnumber : '-';
        $row->attempts_made = (int)$row->attempts_made;

        if ($row->attempts_allowed == 0) {
            $row->attempts_allowed = 'Ilimitados';
        } else {
            $row->attempts_allowed = (int)$row->attempts_allowed;
        }

        if ($row->best_grade > 0) {
            $row->best_grade = number_format((float)$row->best_grade, 2);
        } else {
            $row->best_grade = '-';
        }

        // ✅ ALTERNATIVA: Obtener timestamp original si latest_attempt ya fue procesado
        if (is_string($row->latest_attempt) && $row->latest_attempt !== 'Nunca' && $row->latest_attempt !== get_string('never', 'core')) {
            // Ya está formateado, dejarlo como está
            // No hacer nada
        } else {
            // Si por alguna razón llegó aquí sin procesar, procesarlo
            if (!empty($row->latest_attempt) && is_numeric($row->latest_attempt) && $row->latest_attempt > 0) {
                $row->latest_attempt = userdate((int)$row->latest_attempt, '%d/%m/%Y %H:%M');
            } else {
                $row->latest_attempt = 'Nunca';
            }
        }
    }


}
