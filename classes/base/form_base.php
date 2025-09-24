<?php
/**
 * Clase base para formularios con autocomplete dinámico
 * Plugin local_cadreports para Moodle 4.4
 */

namespace local_cadreports\base;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

/**
 * Clase base para formularios de filtros con autocomplete dinámico
 */
abstract class form_base extends \moodleform {

    /**
     * Definición base del formulario
     */
    public function definition() {
        $mform = $this->_form;

        // Header del formulario
        $mform->addElement('header', 'filtersheader', get_string('filters', 'local_cadreports'));

        // Elementos base comunes
        $this->add_base_elements($mform);

        // Elementos específicos del reporte
        $this->add_specific_elements($mform);

        // Botones de acción
        $this->add_action_buttons(false, get_string('generatereport', 'local_cadreports'));

        // ✅ AGREGADO: JavaScript para autocomplete dinámico
        $this->add_javascript();
    }

    /**
     * Añadir elementos base comunes con autocomplete dinámico CORREGIDO
     */
    protected function add_base_elements($mform) {
        // Autocomplete de cursos (sin cambios)
        $course_options = [
            'multiple' => true,
            'noselectionstring' => get_string('selectcourses', 'local_cadreports'),
            'placeholder' => get_string('selectcourses', 'local_cadreports'),
        ];

        $courses = $this->get_all_courses_for_autocomplete();

        $mform->addElement('autocomplete', 'courseids',
            get_string('courses', 'local_cadreports'),
            $courses,
            $course_options);
        $mform->setType('courseids', PARAM_INT);

        // ✅ CORREGIDO: Autocomplete SIN ajax, se actualiza con JavaScript
        $group_options = [
            'multiple' => true,
            'noselectionstring' => get_string('selectcoursefirst', 'local_cadreports'),
            'placeholder' => get_string('selectcoursefirst', 'local_cadreports'),
            // ❌ ELIMINADO: 'ajax' => 'local_cadreports/get_groups_by_courses'
        ];

        // Iniciar vacío, se llena dinámicamente con JavaScript
        $mform->addElement('autocomplete', 'groupids',
            get_string('groups', 'local_cadreports'),
            [], // Vacío inicialmente
            $group_options);
        $mform->setType('groupids', PARAM_INT);

        // Fechas sin cambios
        $mform->addElement('date_time_selector', 'datefrom',
            get_string('datefrom', 'local_cadreports'),
            ['optional' => true]);

        $mform->addElement('date_time_selector', 'dateto',
            get_string('dateto', 'local_cadreports'),
            ['optional' => true]);
    }


    /**
     * Obtener todos los cursos para autocomplete (sin cambios)
     */
    protected function get_all_courses_for_autocomplete() {
        global $DB;

        $courses = [];

        $sql = "SELECT DISTINCT c.id, c.fullname, c.shortname 
                FROM {course} c
                JOIN {enrol} e ON e.courseid = c.id
                JOIN {user_enrolments} ue ON ue.enrolid = e.id
                WHERE c.id > :siteid AND c.visible = :visible
                ORDER BY c.fullname
                LIMIT 1000";

        $params = [
            'siteid' => SITEID,
            'visible' => 1
        ];

        $courserecords = $DB->get_records_sql($sql, $params);

        foreach ($courserecords as $course) {
            $courses[$course->id] = format_string($course->fullname) . ' (' . $course->shortname . ')';
        }

        return $courses;
    }

    /**
     * ✅ NUEVO: JavaScript para manejar autocomplete dinámico
     */
    protected function add_javascript() {
        global $PAGE;

        $PAGE->requires->js_call_amd('local_cadreports/dynamic_groups', 'init');
    }

    // Resto de métodos sin cambios...
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (!empty($data['datefrom']) && !empty($data['dateto'])) {
            if ($data['datefrom'] >= $data['dateto']) {
                $errors['dateto'] = get_string('error_daterange', 'local_cadreports');
            }
        }

        $specific_errors = $this->specific_validation($data, $files);
        return array_merge($errors, $specific_errors);
    }

    // MÉTODOS ABSTRACTOS
    abstract protected function add_specific_elements($mform);
    abstract protected function specific_validation($data, $files);
}
