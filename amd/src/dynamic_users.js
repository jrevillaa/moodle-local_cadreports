/**
 * AMD module para cargar usuarios dinámicamente (similar a dynamic_groups.js)
 * Plugin local_cadreports para Moodle 4.4
 */

define(['jquery', 'core/ajax', 'core/notification'], function ($, Ajax, Notification) {

    var DynamicUsers = {
        searchTimeout: null,
        minSearchLength: 2, // Mínimo de caracteres para buscar

        /**
         * Inicializar carga dinámica de usuarios
         */
        init: function () {
            var userSelect = $('#id_userquery');

            // Esperar a que Select2 esté inicializado
            setTimeout(function () {
                // ✅ Cargar usuarios iniciales (primeros 50)
                DynamicUsers.loadUsers('', userSelect);

                // ✅ Escuchar cuando el usuario abre el dropdown
                userSelect.on('select2:open', function () {
                    var searchField = $('.select2-search__field');

                    // ✅ Cargar resultados al abrir si está vacío
                    if (userSelect.find('option').length === 0) {
                        DynamicUsers.loadUsers('', userSelect);
                    }

                    // ✅ Buscar mientras el usuario escribe
                    searchField.off('keyup').on('keyup', function () {
                        clearTimeout(DynamicUsers.searchTimeout);
                        var query = $(this).val();

                        // Buscar con cualquier texto (incluso vacío para mostrar todos)
                        DynamicUsers.searchTimeout = setTimeout(function () {
                            DynamicUsers.loadUsers(query, userSelect);
                        }, 300); // Esperar 300ms después de que el usuario deje de escribir
                    });
                });
            }, 500);
        },

        /**
         * Cargar usuarios vía AJAX
         * @param {string} query Texto de búsqueda
         * @param {jQuery} userSelect Elemento select de usuarios
         */
        loadUsers: function (query, userSelect) {
            Ajax.call([{
                methodname: 'local_cadreports_search_enrolled_users',
                args: {
                    query: query
                },
                done: function (users) {
                    DynamicUsers.populateUserSelect(userSelect, users);
                },
                fail: function (error) {
                    Notification.exception(error);
                }
            }]);
        },

        /**
         * Poblar el select con usuarios
         * @param {jQuery} userSelect Elemento select de usuarios
         * @param {Array} users Array de usuarios
         */
        populateUserSelect: function (userSelect, users) {
            // Obtener valores actualmente seleccionados
            var currentValues = userSelect.val() || [];

            // Limpiar opciones existentes pero mantener las seleccionadas
            userSelect.find('option').each(function () {
                var optionValue = $(this).val();
                if (currentValues.indexOf(optionValue) === -1) {
                    $(this).remove();
                }
            });

            if (users.length === 0) {
                // No agregar nada si no hay usuarios
                userSelect.trigger('change.select2');
                return;
            }

            // Agregar cada usuario como opción (solo si no existe ya)
            $.each(users, function (index, user) {
                // Verificar si la opción ya existe
                if (userSelect.find('option[value="' + user.username + '"]').length === 0) {
                    var label = user.fullname + ' (' + user.username + ') - ' + user.email;
                    var option = new Option(label, user.username, false, false);
                    userSelect.append(option);
                }
            });

            // Actualizar select2
            userSelect.trigger('change.select2');
        }
    };

    return DynamicUsers;
});
