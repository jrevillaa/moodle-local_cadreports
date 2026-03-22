/**
 * Módulo AMD para mostrar/ocultar campos del formulario según el modo seleccionado
 *
 * @module     local_cadreports/form_grades_toggle
 * @copyright  2025 Tu Nombre
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery'], function($) {

    /**
     * Ocultar o mostrar un grupo de campos según el modo
     *
     * @param {Array} fieldIds Array de IDs de campos (fitem_id_*)
     * @param {Boolean} show True para mostrar, false para ocultar
     */
    const setFieldsVisibility = function(fieldIds, show) {
        fieldIds.forEach(function(fieldId) {
            const $field = $('#' + fieldId);
            if ($field.length) {
                if (show) {
                    $field.show();
                    $field.find('input, textarea, select').prop('disabled', false);
                } else {
                    $field.hide();
                    $field.find('input, textarea, select').prop('disabled', true);
                }
            }
        });
    };

    /**
     * Actualizar visibilidad de campos según el modo seleccionado
     */
    const toggleFields = function() {
        const mode = $('select[name="mode"]').val();
        const isByCourse = (mode === 'bycourse');

        // Mostrar campos de curso/grupo solo en modo "bycourse"
        setFieldsVisibility(['fitem_id_courseids', 'fitem_id_groupids'], isByCourse);

        // Ocultar siempre las fechas (no se usan en este reporte)
        setFieldsVisibility(['fitem_id_datefrom', 'fitem_id_dateto'], false);

        // Mostrar campo de usuarios solo en modo "byuser"
        setFieldsVisibility(['fitem_id_userquery'], !isByCourse);
    };

    /**
     * Inicializar el módulo
     */
    const init = function() {
        // Ejecutar al cargar la página
        toggleFields();

        // Ejecutar cuando cambie el selector de modo
        $('select[name="mode"]').on('change', toggleFields);
    };

    return {
        init: init
    };
});
