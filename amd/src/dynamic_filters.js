define(['jquery', 'core/ajax', 'core/notification'], function($, Ajax, Notification) {
    'use strict';

    var DynamicFilters = {

        init: function() {
            this.bindEvents();
        },

        bindEvents: function() {
            var self = this;

            // Escuchar cambios en el selector de cursos
            $(document).on('change', '#id_courseid', function() {
                var selectedCourses = $(this).val();
                if (selectedCourses && selectedCourses.length > 0) {
                    self.updateGroups(selectedCourses);
                    self.updateUsers(selectedCourses);
                } else {
                    self.clearSelect('#id_groupid');
                    self.clearSelect('#id_userid');
                }
            });
        },

        updateGroups: function(courseIds) {
            var self = this;

            var request = Ajax.call([{
                methodname: 'local_cadreports_get_course_data',
                args: {
                    courseids: courseIds.join(','),
                    type: 'groups'
                }
            }]);

            request[0].done(function(data) {
                self.populateAutocomplete('#id_groupid', data);
            }).fail(function(error) {
                Notification.exception(error);
            });
        },

        updateUsers: function(courseIds) {
            var self = this;

            var request = Ajax.call([{
                methodname: 'local_cadreports_get_course_data',
                args: {
                    courseids: courseIds.join(','),
                    type: 'users'
                }
            }]);

            request[0].done(function(data) {
                self.populateAutocomplete('#id_userid', data);
            }).fail(function(error) {
                Notification.exception(error);
            });
        },

        populateAutocomplete: function(selector, data) {
            var $select = $(selector);

            // Limpiar opciones existentes
            $select.empty();

            // Agregar opción vacía
            $select.append($('<option></option>').attr('value', '').text(''));

            // Agregar nuevas opciones
            $.each(data, function(index, item) {
                $select.append($('<option></option>').attr('value', item.id).text(item.text));
            });

            // Reinicializar el autocomplete si existe
            if ($select.data('select2')) {
                $select.trigger('change');
            }
        },

        clearSelect: function(selector) {
            var $select = $(selector);
            $select.empty();
            $select.append($('<option></option>').attr('value', '').text(''));

            if ($select.data('select2')) {
                $select.trigger('change');
            }
        }
    };

    return DynamicFilters;
});
