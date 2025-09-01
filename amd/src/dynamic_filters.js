define(['jquery', 'core/ajax', 'core/notification'], function($, Ajax, Notification) {
    'use strict';

    var DynamicFilters = {
        init: function() {
            this.bindEvents();
        },

        bindEvents: function() {
            var self = this;

            // Escuchar cambios en el selector de cursos
            $('#id_courseid').on('change', function() {
                var selectedCourses = $(this).val();
                if (selectedCourses && selectedCourses.length > 0) {
                    self.updateGroups(selectedCourses);
                    self.updateUsers(selectedCourses);
                } else {
                    self.clearGroups();
                    self.clearUsers();
                }
            });
        },

        updateGroups: function(courseIds) {
            var self = this;

            Ajax.call([{
                methodname: 'local_cadreports_get_course_data',
                args: {
                    courseids: courseIds.join(','),
                    type: 'groups'
                }
            }])[0].done(function(data) {
                self.populateSelect('#id_groupid', data);
            }).fail(function(error) {
                Notification.exception(error);
            });
        },

        updateUsers: function(courseIds) {
            var self = this;

            Ajax.call([{
                methodname: 'local_cadreports_get_course_data',
                args: {
                    courseids: courseIds.join(','),
                    type: 'users'
                }
            }])[0].done(function(data) {
                self.populateSelect('#id_userid', data);
            }).fail(function(error) {
                Notification.exception(error);
            });
        },

        populateSelect: function(selector, data) {
            var $select = $(selector);
            var autocomplete = $select.data('autocomplete');

            if (autocomplete) {
                // Limpiar opciones actuales
                autocomplete.clearSelection();

                // Actualizar opciones disponibles
                autocomplete.processResults(data);
            }
        },

        clearGroups: function() {
            this.populateSelect('#id_groupid', []);
        },

        clearUsers: function() {
            this.populateSelect('#id_userid', []);
        }
    };

    return DynamicFilters;
});
