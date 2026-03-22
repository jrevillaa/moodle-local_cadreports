/**
 * JavaScript simple para multiselect de cursos y grupos
 * Plugin local_cadreports para Moodle 4.4
 */

define(['jquery', 'core/ajax', 'core/notification'], function($, Ajax, Notification) {

    var FormMultiselect = {

        /**
         * Inicializar multiselect
         */
        init: function() {
            var courseSelect = $('#id_courseids');
            var groupSelect = $('#id_groupids');

            // Cuando cambian los cursos seleccionados, actualizar grupos
            courseSelect.on('change', function() {
                FormMultiselect.updateGroups(courseSelect.val(), groupSelect);
            });

            // Mejorar apariencia de selects múltiples
            FormMultiselect.enhanceMultiselects();
        },

        /**
         * Actualizar grupos basado en cursos seleccionados
         * @param {Array} selectedCourses Array de IDs de cursos seleccionados
         * @param {jQuery} groupSelect Elemento jQuery del select de grupos
         */
        updateGroups: function(selectedCourses, groupSelect) {
            // Limpiar grupos actuales
            groupSelect.empty();

            if (!selectedCourses || selectedCourses.length === 0) {
                groupSelect.append('<option value="">' +
                    'Selecciona primero uno o más cursos' + '</option>');
                return;
            }

            // Cargar grupos para los cursos seleccionados
            Ajax.call([{
                methodname: 'local_cadreports_get_groups_for_courses',
                args: {courseids: selectedCourses.map(function(id) { return parseInt(id); })},
                done: function(groups) {
                    if (groups.length === 0) {
                        groupSelect.append('<option value="">' +
                            'No hay grupos para los cursos seleccionados' + '</option>');
                    } else {
                        $.each(groups, function(index, group) {
                            groupSelect.append('<option value="' + group.id + '">' +
                                group.name + ' (' + group.coursename + ')</option>');
                        });
                    }
                },
                fail: function(error) {
                    Notification.exception(error);
                    groupSelect.append('<option value="">' +
                        'Error cargando grupos' + '</option>');
                }
            }]);
        },

        /**
         * Mejorar apariencia de selects múltiples
         */
        enhanceMultiselects: function() {
            // Agregar CSS personalizado para hacer los selects más usables
            $('<style>')
                .prop('type', 'text/css')
                .html(`
                    .cadreports-multiselect {
                        min-height: 120px !important;
                        font-size: 14px;
                    }
                    .cadreports-multiselect option {
                        padding: 4px 8px;
                    }
                    .cadreports-multiselect option:hover {
                        background-color: #e3f2fd;
                    }
                `)
                .appendTo('head');

            $('#id_courseids, #id_groupids').addClass('cadreports-multiselect');
        }
    };

    return FormMultiselect;
});
