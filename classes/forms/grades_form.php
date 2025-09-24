<?php
/**
 * Formulario específico para reporte de notas - TODAS las actividades
 * Plugin local_cadreports para Moodle 4.4
 */

namespace local_cadreports\forms;

use local_cadreports\base\form_base;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/cadreports/classes/base/form_base.php');

/**
 * Formulario específico del reporte de notas
 */
class grades_form extends form_base {

    /**
     * ✅ SIMPLIFICADO: No hay filtros específicos, mostrar TODAS las actividades
     */
    protected function add_specific_elements($mform) {
        // Sin filtros adicionales - mostrar todas las actividades y notas finales
        $mform->addElement('static', 'info', '',
            get_string('allactivitiesinfo', 'local_cadreports'));
    }

    /**
     * Validación específica del reporte de notas
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
