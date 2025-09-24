<?php
/**
 * External API para obtener grupos de múltiples cursos
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
 * External API para obtener grupos de múltiples cursos
 */
class get_groups_for_courses extends external_api {

    /**
     * Parámetros de entrada
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'courseids' => new external_multiple_structure(
                new external_value(PARAM_INT, 'Course ID')
            ),
        ]);
    }

    /**
     * Ejecutar la función
     */
    public static function execute($courseids) {
        global $DB;

        // Validar parámetros
        $params = self::validate_parameters(self::execute_parameters(), [
            'courseids' => $courseids,
        ]);

        // Verificar contexto y permisos
        $context = context_system::instance();
        self::validate_context($context);
        require_capability('local/cadreports:view', $context);

        $groups = [];

        if (!empty($params['courseids'])) {
            // Usar get_in_or_equal para consulta segura
            list($insql, $inparams) = $DB->get_in_or_equal($params['courseids'], SQL_PARAMS_NAMED);

            $sql = "SELECT g.id, g.name, g.courseid, c.shortname as coursename
                    FROM {groups} g
                    JOIN {course} c ON c.id = g.courseid
                    WHERE g.courseid $insql
                    ORDER BY c.shortname, g.name";

            $grouprecords = $DB->get_records_sql($sql, $inparams);

            foreach ($grouprecords as $group) {
                $groups[] = [
                    'id' => (int)$group->id,
                    'name' => $group->name,
                    'courseid' => (int)$group->courseid,
                    'coursename' => $group->coursename
                ];
            }
        }

        return $groups;
    }

    /**
     * Estructura de retorno
     */
    public static function execute_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'id' => new external_value(PARAM_INT, 'Group ID'),
                'name' => new external_value(PARAM_TEXT, 'Group name'),
                'courseid' => new external_value(PARAM_INT, 'Course ID'),
                'coursename' => new external_value(PARAM_TEXT, 'Course short name'),
            ])
        );
    }
}
