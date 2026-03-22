/**
 * AMD module para autocompletar usuarios matriculados
 *
 * @module     local_cadreports/form_userquery_selector
 * @copyright  2025 CAD Peru
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['core/ajax'], function (Ajax) {
    return {
        /**
         * Buscar usuarios con matrícula vigente
         *
         * @param {String} selector El selector CSS del campo
         * @param {String} query El texto de búsqueda
         * @returns {Promise} Promise que resuelve con array de {value, label}
         */
        transport: function (selector, query) {
            // Llamar al servicio web
            var promise = Ajax.call([{
                methodname: 'local_cadreports_search_enrolled_users',
                args: {
                    query: query
                }
            }])[0];

            // Transformar la respuesta al formato esperado
            return promise.then(function (results) {
                var options = [];

                // Procesar cada usuario
                if (results && Array.isArray(results)) {
                    results.forEach(function (user) {
                        options.push({
                            value: user.username,
                            label: user.fullname + ' (' + user.username + ') - ' + user.email
                        });
                    });
                }

                return options;
            });
        },

        /**
         * Procesar resultados (ya procesados en transport)
         *
         * @param {String} selector El selector CSS del campo
         * @param {Array} results Resultados del transport
         * @returns {Array} Resultados sin modificar
         */
        processResults: function (selector, results) {
            return results;
        }
    };
});
