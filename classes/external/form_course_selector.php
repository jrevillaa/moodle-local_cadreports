<?php
/**
 * External API para autocomplete de cursos
 * Plugin local_cadreports para Moodle 4.4
 */

namespace local_cadreports\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

use external_api;
use external_function_parameters;
use external_value;
use external_multiple_structure;
use external_single_structure;
use context_system;

/**
 * External API para autocomplete de cursos
 */
class form_course_selector extends external_api {

    /**
     * Parámetros de entrada
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'query' => new external_value(PARAM_TEXT, 'Search query'),
        ]);
    }

    /**
     * Ejecutar la función
     */
    public static function execute($query) {
        global $DB;

        // Validar parámetros
        $params = self::validate_parameters(self::execute_parameters(), [
            'query' => $query,
        ]);

        // Verificar contexto y permisos
        $context = context_system::instance();
        self::validate_context($context);
        require_capability('local/cadreports:view', $context);

        // Buscar cursos
        $courses = [];
        if (strlen($params['query']) >= 2) {
            $sql = "SELECT DISTINCT c.id, c.fullname, c.shortname 
                    FROM {course} c
                    JOIN {enrol} e ON e.courseid = c.id
                    JOIN {user_enrolments} ue ON ue.enrolid = e.id
                    WHERE c.id > :siteid 
                    AND c.visible = :visible
                    AND (LOWER(c.fullname) LIKE LOWER(:query1) 
                         OR LOWER(c.shortname) LIKE LOWER(:query2))
                    ORDER BY c.fullname
                    LIMIT 50";

            $like_query = '%' . $DB->sql_like_escape($params['query']) . '%';
            $course_params = [
                'siteid' => SITEID,
                'visible' => 1,
                'query1' => $like_query,
                'query2' => $like_query
            ];

            $courserecords = $DB->get_records_sql($sql, $course_params);

            foreach ($courserecords as $course) {
                $courses[] = [
                    'value' => $course->id,
                    'label' => format_string($course->fullname) . ' (' . $course->shortname . ')'
                ];
            }
        }

        return $courses;
    }

    /**
     * Estructura de retorno
     */
    public static function execute_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'value' => new external_value(PARAM_INT, 'Course ID'),
                'label' => new external_value(PARAM_TEXT, 'Course name'),
            ])
        );
    }
}
