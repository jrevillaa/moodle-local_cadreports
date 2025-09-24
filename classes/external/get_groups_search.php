<?php
/**
 * External API para autocomplete de grupos - Renombrado
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
 * External API para autocomplete de grupos
 */
class get_groups_search extends external_api {

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

        // Buscar grupos
        $groups = [];
        if (strlen($params['query']) >= 2) {
            $sql = "SELECT g.id, g.name, c.shortname as coursename
                    FROM {groups} g
                    JOIN {course} c ON c.id = g.courseid
                    WHERE c.visible = :visible
                    AND LOWER(g.name) LIKE LOWER(:query)
                    ORDER BY c.shortname, g.name
                    LIMIT 50";

            $like_query = '%' . $DB->sql_like_escape($params['query']) . '%';
            $group_params = [
                'visible' => 1,
                'query' => $like_query
            ];

            $grouprecords = $DB->get_records_sql($sql, $group_params);

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
                'label' => new external_value(PARAM_TEXT, 'Group name'),
            ])
        );
    }
}
