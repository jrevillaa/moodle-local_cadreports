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
     * El form_base ya agrega: mode, courseids, groupids, userquery, datefrom, dateto, botones, etc.
     * 
     * ✅ NOTA: mode y userquery ahora están en form_base.php, no duplicar aquí
     */
    protected function add_specific_elements($mform) {
        // ✅ Ya no es necesario agregar 'mode' ni 'userquery' aquí
        // Estos campos ya están en form_base.php
        
        // Si necesitas agregar campos específicos solo para grades, agrégalos aquí
        // Por ahora, este formulario no tiene campos adicionales específicos
    }

    /**
     * Validación específica de grades
     * ✅ NOTA: La validación de mode ya está en form_base.php
     * Solo agregar validaciones específicas de este reporte aquí si es necesario
     */
    protected function specific_validation($data, $files) {
        $errors = [];
        
        // Agregar validaciones específicas del reporte de notas aquí si es necesario
        // Por ahora, la validación base es suficiente
        
        return $errors;
    }
}
