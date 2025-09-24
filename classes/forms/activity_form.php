<?php
/**
 * Formulario específico para reporte de actividad de usuarios
 * Plugin local_cadreports para Moodle 4.4
 */

namespace local_cadreports\forms;

use local_cadreports\base\form_base;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/cadreports/classes/base/form_base.php');

/**
 * Formulario específico del reporte de actividad de usuarios
 */
class activity_form extends form_base {

    /**
     * Añadir elementos específicos del reporte de actividad
     */
    protected function add_specific_elements($mform) {
        // Filtro por tipo de acción
        $actions = [
            '' => get_string('allactions', 'local_cadreports'),
            'viewed' => get_string('actionviewed', 'local_cadreports'),
            'created' => get_string('actioncreated', 'local_cadreports'),
            'updated' => get_string('actionupdated', 'local_cadreports'),
            'submitted' => get_string('actionsubmitted', 'local_cadreports'),
            'deleted' => get_string('actiondeleted', 'local_cadreports'),
            'loggedin' => get_string('actionloggedin', 'local_cadreports'),
            'loggedout' => get_string('actionloggedout', 'local_cadreports')
        ];

        $mform->addElement('select', 'action',
            get_string('actionfilter', 'local_cadreports'),
            $actions);
        $mform->setType('action', PARAM_ALPHA);

        // Filtro por componente
        $components = [
            '' => get_string('allcomponents', 'local_cadreports'),
            'core' => get_string('componentcore', 'local_cadreports'),
            'mod_quiz' => get_string('componentquiz', 'local_cadreports'),
            'mod_forum' => get_string('componentforum', 'local_cadreports'),
            'mod_assign' => get_string('componentassign', 'local_cadreports'),
            'mod_scorm' => get_string('componentscorm', 'local_cadreports'),
            'mod_lesson' => get_string('componentlesson', 'local_cadreports')
        ];

        $mform->addElement('select', 'component',
            get_string('componentfilter', 'local_cadreports'),
            $components);
        $mform->setType('component', PARAM_ALPHA);

        // Información sobre el reporte
        $mform->addElement('static', 'info', '',
            get_string('activityreportinfo', 'local_cadreports'));
    }

    /**
     * Validación específica del reporte de actividad
     */
    protected function specific_validation($data, $files) {
        $errors = [];

        // Validar que al menos un filtro esté activo
        if (empty($data['courseids']) && empty($data['datefrom']) && empty($data['dateto'])) {
            $errors['courseids'] = get_string('error_nofilters', 'local_cadreports');
        }

        return $errors;
    }
}
