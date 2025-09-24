<?php
/**
 * Formulario específico para reporte de accesos
 * Plugin local_cadreports para Moodle 4.4
 */

namespace local_cadreports\forms;

use local_cadreports\base\form_base;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/cadreports/classes/base/form_base.php');

/**
 * Formulario específico del reporte de accesos
 */
class access_form extends form_base {

    /**
     * No hay elementos adicionales específicos para el reporte de accesos
     * Solo usa los elementos base (curso, grupo, fechas)
     */
    protected function add_specific_elements($mform) {
        // El reporte de accesos solo usa filtros base
        // Otros reportes pueden añadir filtros específicos aquí
    }

    /**
     * Validación específica del reporte de accesos
     */
    protected function specific_validation($data, $files) {
        $errors = [];

        // Validar que al menos un filtro esté activo para el reporte de accesos
        if (empty($data['courseid']) && empty($data['datefrom']) && empty($data['dateto'])) {
            $errors['courseid'] = get_string('error_nofilters', 'local_cadreports');
        }

        return $errors;
    }
}