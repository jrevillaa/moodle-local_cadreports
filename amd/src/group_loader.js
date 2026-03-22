/**
 * AMD module para carga dinámica de grupos usando AJAX nativo de Moodle
 * Plugin local_cadreports para Moodle 4.4
 */

define(['jquery', 'core/ajax', 'core/notification', 'core/str'], function($, Ajax, Notification, Str) {

    var GroupLoader = {

        /**
         * Inicializar el cargador de grupos
         */
        init: function() {
            $('#id_courseid').on('change', this.loadGroups);
        },

        /**
         * Cargar grupos del curso seleccionado usando core/ajax
         */
        loadGroups: function() {
            var courseid = $(this).val();
            var groupselect = $('#id_groupid');

            // Obtener string usando core/str y luego limpiar opciones
            Str.get_string('allgroups', 'local_cadreports').then(function(allgroupstext) {
                groupselect.empty().append('<option value="0">' + allgroupstext + '</option>');

                if (courseid > 0) {
                    // Usar Ajax.call nativo de Moodle
                    Ajax.call([{
                        methodname: 'local_cadreports_get_course_groups',
                        args: {courseid: parseInt(courseid)},
                        done: function(groups) {
                            $.each(groups, function(index, group) {
                                groupselect.append('<option value="' + group.id + '">' +
                                    group.name + '</option>');
                            });
                            groupselect.prop('disabled', false);
                        },
                        fail: function(error) {
                            Notification.exception(error);
                            groupselect.prop('disabled', true);
                        }
                    }]);
                } else {
                    groupselect.prop('disabled', true);
                }
            }).catch(function(error) {
                Notification.exception(error);
            });
        }
    };

    return GroupLoader;
});
