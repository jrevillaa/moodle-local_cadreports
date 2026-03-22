<?php
/**
 * Formulario específico para reporte de participación en foros
 * Plugin local_cadreports para Moodle 4.4
 */

namespace local_cadreports\forms;

use local_cadreports\base\form_base;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/cadreports/classes/base/form_base.php');

/**
 * Formulario específico del reporte de participación en foros
 */
class forum_form extends form_base {

    /**
     * Añadir elementos específicos del reporte de foros
     */
    protected function add_specific_elements($mform) {
        // Filtro por estado de participación
        $participation_options = [
            '' => get_string('allparticipation', 'local_cadreports'),
            'participated' => get_string('participated', 'local_cadreports'),
            'not_participated' => get_string('notparticipated', 'local_cadreports'),
            'responded_by_staff' => get_string('respondedbycstaff', 'local_cadreports')
        ];

        $mform->addElement('select', 'participation_status',
            get_string('participationstatus', 'local_cadreports'),
            $participation_options);
        $mform->setType('participation_status', PARAM_ALPHA);

        // Información sobre el reporte
        $mform->addElement('static', 'info', '',
            get_string('forumreportinfo', 'local_cadreports'));
    }

    /**
     * Validación específica del reporte de foros
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
