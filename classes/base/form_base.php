<?php
/**
 * Clase base para formularios con multiselect simple
 * Plugin local_cadreports para Moodle 4.4
 */

namespace local_cadreports\base;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

/**
 * Clase base para formularios de filtros
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

        // JavaScript para funcionalidad
        $this->add_javascript();
    }

    /**
     * Añadir elementos base comunes - USANDO SELECT MÚLTIPLE SIMPLE
     */
    protected function add_base_elements($mform) {
        // ✅ CAMBIO: Select múltiple simple para cursos
        $courses = $this->get_courses();
        $select_courses = $mform->addElement('select', 'courseids',
            get_string('courses', 'local_cadreports'),
            $courses);
        $select_courses->setMultiple(true);
        $select_courses->setSize(min(8, count($courses))); // Mostrar hasta 8 opciones
        $mform->setType('courseids', PARAM_INT);

        // ✅ CAMBIO: Select múltiple simple para grupos (se actualiza via AJAX)
        $groups = ['' => get_string('selectcoursefirst', 'local_cadreports')];
        $select_groups = $mform->addElement('select', 'groupids',
            get_string('groups', 'local_cadreports'),
            $groups);
        $select_groups->setMultiple(true);
        $select_groups->setSize(6);
        $mform->setType('groupids', PARAM_INT);

        // Fecha desde
        $mform->addElement('date_time_selector', 'datefrom',
            get_string('datefrom', 'local_cadreports'),
            ['optional' => true]);

        // Fecha hasta
        $mform->addElement('date_time_selector', 'dateto',
            get_string('dateto', 'local_cadreports'),
            ['optional' => true]);
    }

    /**
     * Obtener lista de cursos para select
     */
    protected function get_courses() {
        global $DB;

        $courses = [];

        $sql = "SELECT DISTINCT c.id, c.fullname, c.shortname 
                FROM {course} c
                JOIN {enrol} e ON e.courseid = c.id
                JOIN {user_enrolments} ue ON ue.enrolid = e.id
                WHERE c.id > :siteid AND c.visible = :visible
                ORDER BY c.fullname";

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
     * JavaScript para cargar grupos cuando se seleccionan cursos
     */
    protected function add_javascript() {
        global $PAGE;

        $PAGE->requires->js_call_amd('local_cadreports/form_multiselect', 'init');
    }

    /**
     * Validación base común
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Validar rango de fechas
        if (!empty($data['datefrom']) && !empty($data['dateto'])) {
            if ($data['datefrom'] >= $data['dateto']) {
                $errors['dateto'] = get_string('error_daterange', 'local_cadreports');
            }
        }

        // Validaciones específicas
        $specific_errors = $this->specific_validation($data, $files);
        return array_merge($errors, $specific_errors);
    }

    // MÉTODOS ABSTRACTOS
    abstract protected function add_specific_elements($mform);
    abstract protected function specific_validation($data, $files);
}
