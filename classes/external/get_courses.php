<?php
/**
 * External API para obtener grupos de curso
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
 * External API para obtener grupos de un curso
 */
class get_courses extends external_api {

    /**
     * Parámetros de entrada
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
        ]);
    }

    /**
     * Ejecutar la función
     */
    public static function execute($courseid) {
        global $DB;

        // Validar parámetros
        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid,
        ]);

        // Verificar contexto y permisos
        $context = context_system::instance();
        self::validate_context($context);
        require_capability('local/cadreports:view', $context);

        // Obtener grupos usando Moodle DML API
        $groups = [];
        if ($params['courseid'] > 0) {
            $sql = "SELECT id, name 
                    FROM {groups} 
                    WHERE courseid = :courseid 
                    ORDER BY name";

            $grouprecords = $DB->get_records_sql($sql, ['courseid' => $params['courseid']]);

            foreach ($grouprecords as $group) {
                $groups[] = [
                    'id' => $group->id,
                    'name' => format_string($group->name)
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
            ])
        );
    }
}
