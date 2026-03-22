<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Formulario de filtros para el reporte de exportación de notas a ERP
 *
 * Este formulario permite filtrar los datos del reporte por curso, grupo y usuario.
 * Extiende moodleform para aprovechar las funcionalidades de validación y procesamiento
 * de formularios de Moodle.
 *
 * @package    local_cadreports
 * @copyright  2024 Jair Revilla <jrevilla492@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cadreports\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Clase para el formulario de filtros del reporte de exportación a ERP
 *
 * Permite seleccionar curso, grupo (filtrado por curso) y usuario (filtrado por curso/grupo)
 * para generar reportes personalizados de notas para el sistema ERP.
 *
 * @package    local_cadreports
 * @copyright  2024 Jair Revilla <jrevilla492@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class erp_export_filter_form extends \moodleform {

    /**
     * Define los elementos del formulario
     *
     * Este método crea todos los campos del formulario de filtros:
     * - Selector de curso (obligatorio)
     * - Selector de grupo (opcional, filtrado por curso)
     * - Selector de usuario (opcional, filtrado por curso/grupo)
     */
    public function definition() {
        global $DB;

        $mform = $this->_form;

        // Agregar título al formulario.
        $mform->addElement('header', 'filtersheader', get_string('filter', 'local_cadreports'));

        // ===== FILTRO DE CURSO ===== //
        $courses = $this->get_available_courses();
        $mform->addElement('select', 'courseid', get_string('course', 'local_cadreports'), $courses);
        $mform->addRule('courseid', get_string('required'), 'required', null, 'client');
        $mform->setType('courseid', PARAM_INT);

        // ===== FILTRO DE GRUPO ===== //
        $mform->addElement('select', 'groupid', get_string('group', 'local_cadreports'),
            [0 => get_string('allgroups', 'local_cadreports')]);
        $mform->setType('groupid', PARAM_INT);
        $mform->setDefault('groupid', 0);

        // ===== FILTRO DE USUARIO ===== //
        $mform->addElement('select', 'userid', get_string('selectuser', 'local_cadreports'),
            [0 => get_string('allusers', 'local_cadreports')]);
        $mform->setType('userid', PARAM_INT);
        $mform->setDefault('userid', 0);

        // ===== BOTONES DE ACCIÓN ===== //
        $buttonarray = [];
        $buttonarray[] = $mform->createElement('submit', 'submitbutton',
            get_string('filter', 'local_cadreports'));
        $buttonarray[] = $mform->createElement('cancel', 'cancel',
            get_string('reset'));

        $mform->addGroup($buttonarray, 'buttonar', '', [' '], false);
        $mform->closeHeaderBefore('buttonar');
    }

    /**
     * Obtiene la lista de cursos disponibles para el usuario actual
     *
     * Devuelve un array con los cursos accesibles por el usuario, excluyendo
     * el sitio principal (course id = 1). Solo incluye cursos visibles.
     *
     * @return array Array asociativo con id => nombre del curso
     */
    protected function get_available_courses() {
        global $DB;

        // Obtener todos los cursos visibles (excepto el sitio).
        $courses = $DB->get_records_select('course',
            'id > 1 AND visible = 1',
            null,
            'fullname ASC',
            'id, fullname');

        $courseoptions = [0 => get_string('selectcourse', 'local_cadreports')];

        foreach ($courses as $course) {
            // Verificar si el usuario tiene acceso al curso.
            $context = \context_course::instance($course->id);
            if (has_capability('moodle/course:view', $context)) {
                $courseoptions[$course->id] = format_string($course->fullname);
            }
        }

        return $courseoptions;
    }

    /**
     * Valida los datos enviados por el formulario
     *
     * Realiza validaciones adicionales sobre los datos del formulario:
     * - Verifica que el curso seleccionado existe y es válido
     * - Valida que el grupo pertenece al curso seleccionado (si aplica)
     * - Valida que el usuario está inscrito en el curso (si aplica)
     *
     * @param array $data Array con los datos del formulario
     * @param array $files Array con archivos subidos (no usado en este form)
     * @return array Array con errores de validación (vacío si no hay errores)
     */
    public function validation($data, $files) {
        global $DB;

        $errors = parent::validation($data, $files);

        // Validar que el curso existe.
        if (!empty($data['courseid'])) {
            $course = $DB->get_record('course', ['id' => $data['courseid']]);
            if (!$course) {
                $errors['courseid'] = get_string('invalidcourseid', 'error');
            }

            // Si hay grupo seleccionado, validar que pertenece al curso.
            if (!empty($data['groupid'])) {
                $group = $DB->get_record('groups', [
                    'id' => $data['groupid'],
                    'courseid' => $data['courseid']
                ]);

                if (!$group) {
                    $errors['groupid'] = get_string('invalidgroupid', 'error');
                }
            }

            // Si hay usuario seleccionado, validar que está inscrito en el curso.
            if (!empty($data['userid'])) {
                $context = \context_course::instance($data['courseid']);
                $enrolled = is_enrolled($context, $data['userid'], '', true);

                if (!$enrolled) {
                    $errors['userid'] = get_string('usernotincourse', 'error');
                }
            }
        }

        return $errors;
    }

    /**
     * Procesa los datos del formulario después de la validación
     *
     * Este método se ejecuta después de que el formulario es validado exitosamente.
     * Puede ser usado para realizar acciones adicionales con los datos.
     *
     * @return object Objeto con los datos del formulario
     */
    public function get_data() {
        $data = parent::get_data();

        if ($data) {
            // Normalizar valores vacíos a 0.
            $data->groupid = !empty($data->groupid) ? $data->groupid : 0;
            $data->userid = !empty($data->userid) ? $data->userid : 0;
        }

        return $data;
    }
}
