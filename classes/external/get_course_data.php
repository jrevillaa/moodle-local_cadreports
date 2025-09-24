<?php
namespace local_cadreports\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

use external_api;
use external_function_parameters;
use external_value;
use external_multiple_structure;
use external_single_structure;

class get_course_data extends external_api {

    public static function execute_parameters() {
        return new external_function_parameters([
            'courseids' => new external_value(PARAM_TEXT, 'Comma separated course IDs'),
            'type' => new external_value(PARAM_ALPHA, 'Type of  groups or users')
        ]);
    }

    public static function execute($courseids, $type) {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), [
            'courseids' => $courseids,
            'type' => $type
        ]);

        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('local/cadreports:view', $context);

        $courseids = explode(',', $params['courseids']);
        $courseids = array_map('intval', $courseids);
        $courseids = array_filter($courseids);

        $result = [];

        switch ($params['type']) {
            case 'groups':
                $result = self::get_groups_for_courses($courseids);
                break;
            case 'users':
                $result = self::get_users_for_courses($courseids);
                break;
        }

        return $result;
    }

    public static function execute_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'id' => new external_value(PARAM_INT, 'ID'),
                'text' => new external_value(PARAM_TEXT, 'Display text')
            ])
        );
    }

    private static function get_groups_for_courses($courseids) {
        global $DB;

        if (empty($courseids)) {
            return [];
        }

        list($insql, $params) = $DB->get_in_or_equal($courseids);

        $sql = "SELECT g.id, g.name, g.courseid, c.shortname as coursename
                FROM {groups} g
                JOIN {course} c ON c.id = g.courseid
                WHERE g.courseid $insql
                ORDER BY c.shortname, g.name";

        $groups = $DB->get_records_sql($sql, $params);

        $result = [];
        foreach ($groups as $group) {
            $result[] = [
                'id' => $group->id,
                'text' => $group->coursename . ': ' . format_string($group->name)
            ];
        }

        return $result;
    }

    private static function get_users_for_courses($courseids) {
        global $DB;

        if (empty($courseids)) {
            return [];
        }

        list($insql, $params) = $DB->get_in_or_equal($courseids);

        $sql = "SELECT DISTINCT u.id, u.firstname, u.lastname, u.email
                FROM {user} u
                JOIN {user_enrolments} ue ON ue.userid = u.id
                JOIN {enrol} e ON e.id = ue.enrolid
                WHERE e.courseid $insql
                AND u.deleted = 0
                AND u.suspended = 0
                AND ue.status = 0
                ORDER BY u.lastname, u.firstname";

        $users = $DB->get_records_sql($sql, $params);

        $result = [];
        foreach ($users as $user) {
            $result[] = [
                'id' => $user->id,
                'text' => fullname($user) . ' (' . $user->email . ')'
            ];
        }

        return $result;
    }
}
