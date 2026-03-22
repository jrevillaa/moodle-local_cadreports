/**
 * AMD module para autocompletar usuarios matriculados
 *
 * @module     local_cadreports/form_userquery_selector
 * @copyright  2025 Tu Nombre
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/ajax'], function($, Ajax) {
    return {
        /**
         * Buscar usuarios con matrícula vigente
         *
         * @param {String} selector El selector CSS del campo
         * @param {String} query El texto de búsqueda
         * @returns {Promise}
         */
        transport: function(selector, query) {
            return Ajax.call([{
                methodname: 'local_cadreports_search_enrolled_users',
                args: {
                    query: query
                }
            }])[0];
        },

        /**
         * Procesar resultados para el autocomplete
         *
         * @param {String} selector El selector CSS del campo
         * @param {Array} results Array de objetos con datos de usuarios
         * @returns {Array} Array de objetos con value y label para el autocomplete
         */
        processResults: function(selector, results) {
            var users = [];
            if (results && results.length) {
                results.forEach(function(user) {
                    users.push({
                        value: user.username,
                        label: user.fullname + ' (' + user.username + ') - ' + user.email
                    });
                });
            }
            return users;
        }
    };
});
