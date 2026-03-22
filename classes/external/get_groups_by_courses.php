<?php
/**
 * External API para autocomplete dinámico de grupos por cursos
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
 * External API para autocomplete de grupos filtrados por cursos
 */
class get_groups_by_courses extends external_api {

    /**
     * Parámetros de entrada
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'query' => new external_value(PARAM_TEXT, 'Search query'),
            'courseids' => new external_multiple_structure(
                new external_value(PARAM_INT, 'Course ID'),
                'Course IDs to filter groups',
                VALUE_DEFAULT,
                []
            ),
        ]);
    }

    /**
     * Ejecutar la función
     */
    public static function execute($query, $courseids = []) {
        global $DB;

        // Validar parámetros
        $params = self::validate_parameters(self::execute_parameters(), [
            'query' => $query,
            'courseids' => $courseids,
        ]);

        // Verificar contexto y permisos
        $context = context_system::instance();
        self::validate_context($context);
        require_capability('local/cadreports:view', $context);

        $groups = [];

        // ✅ CLAVE: Solo buscar si hay cursos seleccionados y query mínima
        if (!empty($params['courseids']) && strlen($params['query']) >= 1) {

            // Usar get_in_or_equal para consulta segura por múltiples cursos
            list($insql, $inparams) = $DB->get_in_or_equal($params['courseids'], SQL_PARAMS_NAMED, 'course');

            $sql = "SELECT g.id, g.name, g.courseid, c.shortname as coursename
                    FROM {groups} g
                    JOIN {course} c ON c.id = g.courseid
                    WHERE g.courseid $insql
                    AND LOWER(g.name) LIKE LOWER(:query)
                    ORDER BY c.shortname, g.name
                    LIMIT 50";

            $like_query = '%' . $DB->sql_like_escape($params['query']) . '%';
            $search_params = array_merge($inparams, ['query' => $like_query]);

            $grouprecords = $DB->get_records_sql($sql, $search_params);

            foreach ($grouprecords as $group) {
                $groups[] = [
                    'value' => (int)$group->id,
                    'label' => $group->name . ' (' . $group->coursename . ')'
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
                'value' => new external_value(PARAM_INT, 'Group ID'),
                'label' => new external_value(PARAM_TEXT, 'Group label'),
            ])
        );
    }
}
