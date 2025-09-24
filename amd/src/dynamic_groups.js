/**
 * AMD module para autocomplete dinámico de grupos
 * Plugin local_cadreports para Moodle 4.4
 */

define(['jquery'], function($) {

    var DynamicGroups = {

        /**
         * Inicializar autocomplete dinámico
         */
        init: function() {
            var courseSelect = $('#id_courseids');
            var groupSelect = $('#id_groupids');

            // Escuchar cambios en la selección de cursos
            courseSelect.on('change', function() {
                DynamicGroups.updateGroupsAutocomplete(courseSelect.val());
            });
        },

        /**
         * Actualizar configuración de autocomplete de grupos
         * @param {Array} selectedCourses Array de IDs de cursos seleccionados
         */
        updateGroupsAutocomplete: function(selectedCourses) {
            var groupSelect = $('#id_groupids');

            if (!selectedCourses || selectedCourses.length === 0) {
                // Sin cursos seleccionados - deshabilitar grupos
                groupSelect.prop('disabled', true);
                groupSelect.empty();

                // Actualizar placeholder
                var autocompleteContainer = groupSelect.closest('.form-autocomplete-container');
                if (autocompleteContainer.length) {
                    var input = autocompleteContainer.find('.form-autocomplete-original-text');
                    input.attr('placeholder', 'Selecciona primero uno o más cursos');
                }
            } else {
                // Con cursos seleccionados - habilitar y configurar AJAX
                groupSelect.prop('disabled', false);

                // Actualizar placeholder
                var autocompleteContainer = groupSelect.closest('.form-autocomplete-container');
                if (autocompleteContainer.length) {
                    var input = autocompleteContainer.find('.form-autocomplete-original-text');
                    input.attr('placeholder', 'Buscar grupos en cursos seleccionados...');
                }

                // ✅ CLAVE: Pasar courseids como parámetro adicional al AJAX
                var ajaxConfig = groupSelect.data('ajax');
                if (ajaxConfig) {
                    // Agregar courseids a los parámetros AJAX
                    ajaxConfig.data = ajaxConfig.data || {};
                    ajaxConfig.data.courseids = selectedCourses;
                }
            }
        }
    };

    return DynamicGroups;
});
