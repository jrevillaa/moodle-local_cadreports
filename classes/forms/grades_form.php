<?php
/**
 * Formulario específico para Registro de Notas (dos caminos de filtro)
 * - Modo "bycourse": cursos y/o grupos (multi)
 * - Modo "byuser"  : búsqueda por username/email (múltiples, con autocomplete tags)
 */

namespace local_cadreports\forms;

use local_cadreports\base\form_base;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/cadreports/classes/base/form_base.php');

class grades_form extends form_base {

    /**
     * Elementos específicos (además de los base del form_base)
     * El form_base ya agrega: courseids, groupids, datefrom, dateto, botones, etc.
     */
    protected function add_specific_elements($mform) {
        global $PAGE;

        // Selector de modo
        $mform->addElement('select', 'mode', get_string('filtermode', 'local_cadreports'), [
            'bycourse' => get_string('filtermode_bycourse', 'local_cadreports'),
            'byuser'   => get_string('filtermode_byuser', 'local_cadreports'),
        ]);
        $mform->setType('mode', PARAM_ALPHA);
        $mform->setDefault('mode', 'bycourse');

        // Autocomplete con AJAX para usuarios matriculados
        $mform->addElement('autocomplete', 'userquery', get_string('userquery', 'local_cadreports'), [],
            [
                'multiple' => true,
                'ajax' => 'local_cadreports/form_userquery_selector',
                'placeholder' => get_string('userquery_placeholder', 'local_cadreports'),
                'noselectionstring' => get_string('allusers', 'local_cadreports'),
            ]
        );
        $mform->setType('userquery', PARAM_RAW);
        $mform->addHelpButton('userquery', 'userquery', 'local_cadreports');

        // Cargar el módulo AMD para mostrar/ocultar campos
        $PAGE->requires->js_call_amd('local_cadreports/form_grades_toggle', 'init');
    }

    /**
     * Validación específica:
     * - En bycourse: se exige al menos un curso o un grupo
     * - En byuser  : se exige al menos un token en userquery (array)
     */
    protected function specific_validation($data, $files) {
        $errors = [];
        $mode = isset($data['mode']) ? $data['mode'] : 'bycourse';

        if ($mode === 'byuser') {
            $tokens = is_array($data['userquery'] ?? null) ? array_filter($data['userquery']) : [];
            if (empty($tokens)) {
                $errors['userquery'] = get_string('error_userquery_required', 'local_cadreports');
            }
        } else {
            $hascourses = !empty($data['courseids']) && is_array($data['courseids']);
            $hasgroups  = !empty($data['groupids'])  && is_array($data['groupids']);
            if (!$hascourses && !$hasgroups) {
                $errors['courseids'] = get_string('error_course_or_group_required', 'local_cadreports');
            }
        }
        return $errors;
    }
}
