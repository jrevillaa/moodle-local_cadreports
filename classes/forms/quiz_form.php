<?php
/**
 * Formulario específico para reporte de cuestionarios
 * Plugin local_cadreports para Moodle 4.4
 */

namespace local_cadreports\forms;

use local_cadreports\base\form_base;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/cadreports/classes/base/form_base.php');

/**
 * Formulario específico del reporte de cuestionarios
 */
class quiz_form extends form_base {

    /**
     * Añadir elementos específicos del reporte de cuestionarios
     */
    protected function add_specific_elements($mform) {
        // Información sobre el reporte
        $mform->addElement('static', 'info', '',
            get_string('quizreportinfo', 'local_cadreports'));
    }

    /**
     * Validación específica del reporte de cuestionarios
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
