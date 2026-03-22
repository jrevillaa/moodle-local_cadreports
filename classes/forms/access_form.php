<?php
/**
 * Formulario específico para reporte de accesos y dedicación
 * Filtros: (Curso y Grupo) o (Participante), Rango Fecha
 *
 * @package    local_cadreports
 * @copyright  2024 Jair Revilla <jrevilla492@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cadreports\forms;

use local_cadreports\base\form_base;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/cadreports/classes/base/form_base.php');

/**
 * Formulario específico del reporte de accesos
 * Usa la misma estructura que grades_form con modo bycourse/byuser
 */
class access_form extends form_base {

    /**
     * Elementos específicos del formulario de accesos
     * ✅ NOTA: mode y userquery ahora están en form_base.php, no duplicar aquí
     *
     * @param object $mform Instancia del formulario
     */
    protected function add_specific_elements($mform) {
        // ✅ Ya no es necesario agregar 'mode' ni 'userquery' aquí
        // Estos campos ya están en form_base.php
        
        // Si necesitas agregar campos específicos solo para accesos, agrégalos aquí
        // Por ahora, este formulario no tiene campos adicionales específicos
    }

    /**
     * Validación específica del formulario
     * ✅ NOTA: La validación de mode ya está en form_base.php
     *
     * @param array $data Datos del formulario
     * @param array $files Archivos subidos (no usado)
     * @return array Errores de validación
     */
    protected function specific_validation($data, $files) {
        $errors = [];
        
        // Agregar validaciones específicas del reporte de accesos aquí si es necesario
        // Por ahora, la validación base es suficiente
        
        return $errors;
    }
}
