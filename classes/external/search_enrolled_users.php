<?php
/**
 * External service para buscar usuarios matriculados
 */

namespace local_cadreports\external;

use external_api;
use external_function_parameters;
use external_value;
use external_multiple_structure;
use external_single_structure;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

class search_enrolled_users extends external_api {

    /**
     * Parámetros de entrada
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'query' => new external_value(PARAM_TEXT, 'Search query', VALUE_DEFAULT, '')
        ]);
    }

    /**
     * Buscar usuarios con matrícula vigente como alumno
     */
    public static function execute($query) {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), [
            'query' => $query
        ]);

        $query = trim($params['query']);
        $results = [];

        // Rol de estudiante (normalmente ID 5, pero mejor buscarlo)
        $studentrole = $DB->get_record('role', ['shortname' => 'student']);
        if (!$studentrole) {
            return $results;
        }

        // Construir consulta SQL
        $sql = "
            SELECT DISTINCT u.id, u.username, u.firstname, u.lastname, u.email,
                   " . $DB->sql_concat('u.firstname', "' '", 'u.lastname') . " AS fullname
            FROM {user} u
            JOIN {user_enrolments} ue ON ue.userid = u.id
            JOIN {enrol} e ON e.id = ue.enrolid
            JOIN {context} ctx ON ctx.instanceid = e.courseid AND ctx.contextlevel = 50
            JOIN {role_assignments} ra ON ra.userid = u.id AND ra.contextid = ctx.id
            WHERE u.deleted = 0
              AND u.suspended = 0
              AND ue.status = 0
              AND ra.roleid = :roleid
        ";

        $sqlparams = ['roleid' => $studentrole->id];

        // Si hay texto de búsqueda, filtrar por username, email, nombre
        if ($query !== '') {
            $sql .= " AND (" . $DB->sql_like('u.username', ':username', false) . "
                         OR " . $DB->sql_like('u.email', ':email', false) . "
                         OR " . $DB->sql_like('u.firstname', ':firstname', false) . "
                         OR " . $DB->sql_like('u.lastname', ':lastname', false) . ")";
            
            $searchparam = '%' . $DB->sql_like_escape($query) . '%';
            $sqlparams['username'] = $searchparam;
            $sqlparams['email'] = $searchparam;
            $sqlparams['firstname'] = $searchparam;
            $sqlparams['lastname'] = $searchparam;
        }

        $sql .= " ORDER BY u.lastname, u.firstname LIMIT 100";

        $users = $DB->get_records_sql($sql, $sqlparams);

        foreach ($users as $user) {
            $results[] = [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'fullname' => fullname($user),
            ];
        }

        return $results;
    }

    /**
     * Estructura de salida
     */
    public static function execute_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'id' => new external_value(PARAM_INT, 'User ID'),
                'username' => new external_value(PARAM_TEXT, 'Username'),
                'email' => new external_value(PARAM_EMAIL, 'Email'),
                'fullname' => new external_value(PARAM_TEXT, 'Full name'),
            ])
        );
    }
}
