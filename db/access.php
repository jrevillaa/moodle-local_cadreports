<?php
defined('MOODLE_INTERNAL') || die();

$capabilities = array(
    'local/cadreports:view' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            'coursecreator' => CAP_ALLOW,
            'editingteacher' => CAP_PREVENT,
            'teacher' => CAP_PREVENT,
            'student' => CAP_PREVENT,
            'guest' => CAP_PREVENT,
            'user' => CAP_PREVENT,
            'frontpage' => CAP_PREVENT
        )
    ),
    'local/cadreports:export' => array(
        'riskbitmask' => RISK_PERSONAL | RISK_DATALOSS,
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            'coursecreator' => CAP_PREVENT,
            'editingteacher' => CAP_PREVENT,
            'teacher' => CAP_PREVENT,
            'student' => CAP_PREVENT,
            'guest' => CAP_PREVENT,
            'user' => CAP_PREVENT,
            'frontpage' => CAP_PREVENT
        )
    ),
    'local/cadreports:viewall' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            'coursecreator' => CAP_PREVENT,
            'editingteacher' => CAP_PREVENT,
            'teacher' => CAP_PREVENT,
            'student' => CAP_PREVENT,
            'guest' => CAP_PREVENT,
            'user' => CAP_PREVENT,
            'frontpage' => CAP_PREVENT
        ),
        'clonepermissionsfrom' => 'moodle/site:viewreports'
    )
);
