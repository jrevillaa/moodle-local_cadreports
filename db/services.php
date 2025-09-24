<?php
/**
 * Web services para local_cadreports
 * Plugin local_cadreports para Moodle 4.4
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_cadreports_get_course_groups' => [
        'classname'   => 'local_cadreports\external\get_course_groups',
        'methodname'  => 'execute',
        'description' => 'Get groups from a course',
        'type'        => 'read',
        'ajax'        => true,
        'capabilities' => 'local/cadreports:view',
    ],
    'local_cadreports_get_groups_for_courses' => [
        'classname'   => 'local_cadreports\external\get_groups_for_courses',
        'methodname'  => 'execute',
        'description' => 'Get groups for multiple courses',
        'type'        => 'read',
        'ajax'        => true,
        'capabilities' => 'local/cadreports:view',
    ],
];
