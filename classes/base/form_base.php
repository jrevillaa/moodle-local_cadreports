<?php
/**
 * Clase base para formularios reutilizable
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

        // JavaScript adicional
        $this->add_javascript();
    }

    /**
     * Añadir elementos base comunes (curso, grupo, fechas)
     */
    protected function add_base_elements($mform) {
        // Selector de curso
        $courses = $this->get_courses();
        $mform->addElement('select', 'courseid', get_string('course', 'local_cadreports'), $courses);
        $mform->setType('courseid', PARAM_INT);

        // Selector de grupo (se actualiza via AJAX)
        $groups = [0 => get_string('allgroups', 'local_cadreports')];
        $mform->addElement('select', 'groupid', get_string('group', 'local_cadreports'), $groups);
        $mform->setType('groupid', PARAM_INT);
        $mform->disabledIf('groupid', 'courseid', 'eq', 0);

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

    /**
     * Obtener lista de cursos disponibles
     */
    protected function get_courses() {
        global $DB;

        $courses = [0 => get_string('allcourses', 'local_cadreports')];

        $sql = "SELECT DISTINCT c.id, c.fullname, c.shortname 
                FROM {course} c
                JOIN {enrol} e ON e.courseid = c.id
                JOIN {user_enrolments} ue ON ue.enrolid = e.id
                WHERE c.id > 1 AND c.visible = 1
                ORDER BY c.fullname";

        $courserecords = $DB->get_records_sql($sql);

        foreach ($courserecords as $course) {
            $courses[$course->id] = $course->fullname . ' (' . $course->shortname . ')';
        }

        return $courses;
    }

    /**
     * JavaScript base para actualización de grupos
     */
    protected function add_javascript() {
        global $PAGE;

        $PAGE->requires->js_amd_inline('
            require(["jquery"], function($) {
                $("#id_courseid").change(function() {
                    var courseid = $(this).val();
                    var groupselect = $("#id_groupid");
                    
                    groupselect.empty().append(\'<option value="0">' .
            get_string('allgroups', 'local_cadreports') . '</option>\');
                    
                    if (courseid > 0) {
                        $.ajax({
                            url: M.cfg.wwwroot + "/local/cadreports/ajax/get_groups.php",
                            type: "GET",
                             {courseid: courseid},
                            dataType: "json",
                            success: function(groups) {
                                $.each(groups, function(id, name) {
                                    if (id > 0) {
                                        groupselect.append(\'<option value="\' + id + \'">\' + name + \'</option>\');
                                    }
                                });
                                groupselect.prop("disabled", false);
                            },
                            error: function() {
                                groupselect.prop("disabled", true);
                            }
                        });
                    } else {
                        groupselect.prop("disabled", true);
                    }
                });
            });
        ');
    }

    // MÉTODOS ABSTRACTOS - Implementar en formularios específicos

    /**
     * Añadir elementos específicos del formulario
     * @param object $mform Objeto formulario
     */
    abstract protected function add_specific_elements($mform);

    /**
     * Validación específica del formulario
     * @param array $data Datos del formulario
     * @param array $files Archivos (si los hay)
     * @return array Errores específicos
     */
    abstract protected function specific_validation($data, $files);
}
