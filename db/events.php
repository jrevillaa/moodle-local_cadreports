<?php
/**
 * Event observers for local_cadreports
 *
 * @package    local_cadreports
 * @copyright  2024 CAD
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$observers = [
    [
        'eventname' => '\mod_forum\event\post_created',
        'callback' => 'local_cadreports\observer\forum_observer::post_created',
        'includefile' => null,
        'priority' => 0,
        'internal' => true,
    ],
];
