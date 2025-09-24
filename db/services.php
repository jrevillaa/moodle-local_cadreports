<?php
defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_cadreports_get_course_data' => [
        'classname' => 'local_cadreports\external\get_course_data',
        'methodname' => 'execute',
        'description' => 'Get groups or users for selected courses',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'local/cadreports:view',
    ],
];
