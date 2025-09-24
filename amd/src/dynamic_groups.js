/**
 * AMD module para autocomplete dinámico de grupos usando AJAX manual
 * Plugin local_cadreports para Moodle 4.4
 */

define(['jquery', 'core/ajax', 'core/notification'], function($, Ajax, Notification) {

    var DynamicGroups = {

        /**
         * Inicializar autocomplete dinámico
         */
        init: function() {
            var courseSelect = $('#id_courseids');

            // Escuchar cambios en la selección de cursos
            courseSelect.on('change', function() {
                DynamicGroups.updateGroupOptions(courseSelect.val());
            });

            // Actualizar al cargar si ya hay cursos seleccionados
            if (courseSelect.val() && courseSelect.val().length > 0) {
                DynamicGroups.updateGroupOptions(courseSelect.val());
            }
        },

        /**
         * Actualizar opciones del select de grupos
         * @param {Array} selectedCourses Array de IDs de cursos seleccionados
         */
        updateGroupOptions: function(selectedCourses) {
            var groupSelect = $('#id_groupids');

            if (!selectedCourses || selectedCourses.length === 0) {
                // Sin cursos seleccionados
                DynamicGroups.clearGroupOptions(groupSelect);
                DynamicGroups.updatePlaceholder(groupSelect, 'Selecciona primero uno o más cursos');
            } else {
                // Con cursos seleccionados - cargar grupos via AJAX
                DynamicGroups.loadGroupsForCourses(selectedCourses, groupSelect);
                DynamicGroups.updatePlaceholder(groupSelect, 'Cargando grupos...');
            }
        },

        /**
         * Limpiar opciones de grupos
         * @param {jQuery} groupSelect Elemento select de grupos
         */
        clearGroupOptions: function(groupSelect) {
            groupSelect.empty();
            groupSelect.prop('disabled', true);
        },

        /**
         * Actualizar placeholder del autocomplete
         * @param {jQuery} groupSelect Elemento select de grupos
         * @param {string} text Texto del placeholder
         */
        updatePlaceholder: function(groupSelect, text) {
            var container = groupSelect.closest('.form-autocomplete-container');
            if (container.length) {
                var input = container.find('.form-autocomplete-original-text');
                input.attr('placeholder', text);
            }
        },

        /**
         * Cargar grupos para cursos específicos
         * @param {Array} courseIds Array de IDs de cursos
         * @param {jQuery} groupSelect Elemento select de grupos
         */
        loadGroupsForCourses: function(courseIds, groupSelect) {
            // Convertir a integers
            var courseIdsInt = courseIds.map(function(id) {
                return parseInt(id, 10);
            });

            // Llamada AJAX usando core/ajax de Moodle
            Ajax.call([{
                methodname: 'local_cadreports_get_groups_for_courses',
                args: {
                    courseids: courseIdsInt
                },
                done: function(groups) {
                    DynamicGroups.populateGroupSelect(groupSelect, groups);
                },
                fail: function(error) {
                    Notification.exception(error);
                    DynamicGroups.clearGroupOptions(groupSelect);
                    DynamicGroups.updatePlaceholder(groupSelect, 'Error cargando grupos');
                }
            }]);
        },

        /**
         * Poblar el select de grupos con las opciones
         * @param {jQuery} groupSelect Elemento select de grupos
         * @param {Array} groups Array de grupos del servidor
         */
        populateGroupSelect: function(groupSelect, groups) {
            // Limpiar opciones actuales
            groupSelect.empty();

            if (groups.length === 0) {
                groupSelect.prop('disabled', true);
                DynamicGroups.updatePlaceholder(groupSelect, 'No hay grupos en los cursos seleccionados');
            } else {
                // Agregar opciones de grupos
                $.each(groups, function(index, group) {
                    var option = new Option(
                        group.name + ' (' + group.coursename + ')',
                        group.id,
                        false,
                        false
                    );
                    groupSelect.append(option);
                });

                groupSelect.prop('disabled', false);
                DynamicGroups.updatePlaceholder(groupSelect, 'Buscar y seleccionar grupos...');

                // Refrescar el autocomplete para que detecte las nuevas opciones
                groupSelect.trigger('change.select2');
            }
        }
    };

    return DynamicGroups;
});
