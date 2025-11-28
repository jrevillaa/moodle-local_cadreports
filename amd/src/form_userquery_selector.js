/**
 * AMD module para autocompletar usuarios matriculados
 *
 * @module     local_cadreports/form_userquery_selector
 * @copyright  2025 CAD Peru
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/ajax', 'core/log'], function($, Ajax, Log) {
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
         * @param {Array} results Array de objetos con datos de usuarios (puede venir envuelto)
         * @returns {Array} Array de objetos con value y label para el autocomplete
         */
        processResults: function(selector, results) {
            var users = [];
            var dataArray = results;

            // DEBUG: Ver estructura de la respuesta
            Log.debug('Autocomplete received:', results);

            // Si la respuesta viene envuelta en un objeto con 'data', extraerla
            // Formato: [{error: false,  [...]}] o [{ [...]}]
            //if (Array.isArray(results) && results.length > 0 && results[0].data) {
                dataArray = results[0].data;
                Log.debug('Extracted ', dataArray);
            //}

            // Procesar los usuarios
            if (dataArray && Array.isArray(dataArray) && dataArray.length > 0) {
                dataArray.forEach(function(user) {
                    users.push({
                        value: user.username,
                        label: user.fullname + ' (' + user.username + ') - ' + user.email
                    });
                });
            }

            Log.debug('Processed users:', users);
            return users;
        }
    };
});
