<?php
namespace local_cadreports\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class access_report_form extends \moodleform {

    public function definition() {
        global $DB, $USER;

        $mform = $this->_form;

        // Curso - Autocomplete
        $courses = $this->get_user_courses();
        $mform->addElement('autocomplete', 'courseid', get_string('course'), $courses, [
            'multiple' => true,
            'placeholder' => get_string('selectcourses', 'local_cadreports'),
            'showsuggestions' => true,
            'casesensitive' => false,
            'noselectionstring' => get_string('selectcourses', 'local_cadreports')
        ]);

        // Grupos - Autocomplete (se actualiza vía AJAX)
        $mform->addElement('autocomplete', 'groupid', get_string('groups'), [], [
            'multiple' => true,
            'placeholder' => get_string('selectgroups', 'local_cadreports'),
            'showsuggestions' => true,
            'casesensitive' => false,
            'noselectionstring' => get_string('selectgroups', 'local_cadreports')
        ]);

        // Usuarios - Autocomplete (se actualiza vía AJAX)
        $mform->addElement('autocomplete', 'userid', get_string('users'), [], [
            'multiple' => true,
            'placeholder' => get_string('selectusers', 'local_cadreports'),
            'showsuggestions' => true,
            'casesensitive' => false,
            'noselectionstring' => get_string('selectusers', 'local_cadreports')
        ]);

        // Fecha desde
        $mform->addElement('date_selector', 'datefrom', get_string('from'), [
            'startyear' => date('Y') - 1,
            'stopyear' => date('Y') + 1,
            'optional' => true
        ]);

        // Fecha hasta
        $mform->addElement('date_selector', 'dateto', get_string('to'), [
            'startyear' => date('Y') - 1,
            'stopyear' => date('Y') + 1,
            'optional' => true
        ]);

        // Botones
        $buttonarray = [];
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('filter'));
        $buttonarray[] = $mform->createElement('submit', 'resetbutton', get_string('reset'));
        $buttonarray[] = $mform->createElement('submit', 'exportbutton', get_string('export', 'local_cadreports'));
        $mform->addGroup($buttonarray, 'buttonar', '', [' '], false);
        $mform->closeHeaderBefore('buttonar');

        // JavaScript para manejar cambios dinámicos
        $this->add_dynamic_js();
    }

    /**
     * Obtener cursos disponibles para el usuario
     */
    private function get_user_courses() {
        global $DB, $USER;

        $courses = [];

        // Si es admin del sitio, mostrar todos los cursos
        if (is_siteadmin()) {
            $courserecords = $DB->get_records('course', ['visible' => 1], 'fullname ASC', 'id,fullname,shortname');
        } else {
            // Solo cursos donde el usuario tiene permisos
            $courserecords = enrol_get_my_courses(['id', 'fullname', 'shortname']);
        }

        foreach ($courserecords as $course) {
            if ($course->id != SITEID) {
                $courses[$course->id] = format_string($course->fullname) . ' (' . $course->shortname . ')';
            }
        }

        return $courses;
    }

    /**
     * Agregar JavaScript para funcionalidad dinámica
     */
    private function add_dynamic_js() {
        global $PAGE;

        $PAGE->requires->js_call_amd('local_cadreports/dynamic_filters', 'init');
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Validar que la fecha desde no sea mayor que la fecha hasta
        if (!empty($data['datefrom']) && !empty($data['dateto'])) {
            if ($data['datefrom'] > $data['dateto']) {
                $errors['dateto'] = get_string('error:dateorder', 'local_cadreports');
            }
        }

        return $errors;
    }
}
