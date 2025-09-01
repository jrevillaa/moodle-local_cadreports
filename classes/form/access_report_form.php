<?php
namespace local_cadreports\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class access_report_form extends \moodleform {

    public function definition() {
        global $DB, $PAGE;

        $mform = $this->_form;

        // Curso - Autocomplete
        $courses = $this->get_user_courses();
        $mform->addElement('autocomplete', 'courseid', get_string('course'), $courses, [
            'multiple' => true,
            'placeholder' => get_string('selectcourses', 'local_cadreports'),
        ]);

        // Grupos - Inicialmente vacío, se pobla vía AJAX
        $mform->addElement('autocomplete', 'groupid', get_string('groups'), [], [
            'multiple' => true,
            'placeholder' => get_string('selectgroups', 'local_cadreports'),
        ]);

        // Usuarios - Inicialmente vacío, se pobla vía AJAX
        $mform->addElement('autocomplete', 'userid', get_string('users'), [], [
            'multiple' => true,
            'placeholder' => get_string('selectusers', 'local_cadreports'),
        ]);

        // Fecha desde
        $mform->addElement('date_selector', 'datefrom', get_string('datefrom', 'local_cadreports'), [
            'optional' => true
        ]);

        // Fecha hasta
        $mform->addElement('date_selector', 'dateto', get_string('dateto', 'local_cadreports'), [
            'optional' => true
        ]);

        // Botones
        $buttonarray = [];
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('filter'));
        $buttonarray[] = $mform->createElement('submit', 'resetbutton', get_string('reset'));
        $buttonarray[] = $mform->createElement('submit', 'exportbutton', get_string('export', 'local_cadreports'));
        $mform->addGroup($buttonarray, 'buttonar', '', [' '], false);

        // Cargar JavaScript para funcionalidad dinámica
        $PAGE->requires->js_call_amd('local_cadreports/dynamic_filters', 'init');
    }

    private function get_user_courses() {
        global $DB, $USER;

        $courses = [];

        if (is_siteadmin()) {
            $courserecords = $DB->get_records('course', ['visible' => 1], 'fullname ASC', 'id,fullname,shortname');
        } else {
            $courserecords = enrol_get_my_courses(['id', 'fullname', 'shortname']);
        }

        foreach ($courserecords as $course) {
            if ($course->id != SITEID) {
                $courses[$course->id] = format_string($course->fullname) . ' (' . $course->shortname . ')';
            }
        }

        return $courses;
    }
}
