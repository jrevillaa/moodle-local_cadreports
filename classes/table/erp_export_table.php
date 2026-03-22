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
 * Tabla para el reporte de exportación de notas a ERP
 *
 * Esta clase define la estructura y formato de la tabla que muestra
 * los datos de notas para exportación al sistema ERP.
 *
 * @package    local_cadreports
 * @copyright  2024 Jair Revilla <jrevilla492@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cadreports\table;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/tablelib.php');

/**
 * Clase para la tabla de exportación de notas a ERP
 *
 * Extiende table_sql para aprovechar funcionalidades de paginación,
 * ordenamiento y exportación nativa de Moodle.
 *
 * @package    local_cadreports
 * @copyright  2024 Jair Revilla <jrevilla492@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class erp_export_table extends \table_sql {

    /**
     * Constructor de la tabla
     *
     * @param string $uniqueid Identificador único para la tabla
     */
    public function __construct($uniqueid) {
        parent::__construct($uniqueid);

        // Definir columnas de la tabla.
        $columns = ['proyecto', 'dni', 'nota'];
        $headers = [
            get_string('proyecto', 'local_cadreports'),
            get_string('dni', 'local_cadreports'),
            get_string('nota', 'local_cadreports')
        ];

        $this->define_columns($columns);
        $this->define_headers($headers);

        // Configuración de la tabla.
        $this->collapsible(false);
        $this->sortable(true, 'dni', SORT_ASC);
        $this->pageable(true);
        $this->is_downloadable(true);
        $this->show_download_buttons_at([TABLE_P_BOTTOM]);

        // No mostrar columnas adicionales en descarga.
        $this->no_sorting('proyecto');
        $this->no_sorting('dni');
        $this->no_sorting('nota');
    }

    /**
     * Formatea la columna 'proyecto'
     *
     * Extrae el código del proyecto del nombre del grupo.
     * Si el grupo sigue la estructura "G_DD/MM/YYYY_CXXXXXXXX",
     * extrae el código "CXXXXXXXX", de lo contrario muestra el nombre completo.
     *
     * @param object $row Fila de datos
     * @return string Código del proyecto o nombre del grupo
     */
    public function col_proyecto($row) {
        if (empty($row->groupname)) {
            return '-';
        }

        // Intentar extraer el código del proyecto.
        $parts = explode('_', $row->groupname);

        // Verificar si sigue la estructura esperada: G_fecha_código.
        if (count($parts) >= 3) {
            // El código del proyecto es la última parte.
            $projectcode = end($parts);

            // Validar que el código empiece con 'C' seguido de números.
            if (preg_match('/^C\d+$/', $projectcode)) {
                return $projectcode;
            }
        }

        // Si no sigue la estructura, devolver el nombre completo del grupo.
        return $row->groupname;
    }

    /**
     * Formatea la columna 'dni'
     *
     * Muestra el username del usuario como DNI.
     *
     * @param object $row Fila de datos
     * @return string Username del usuario
     */
    public function col_dni($row) {
        return $row->dni;
    }

    /**
     * Formatea la columna 'nota'
     *
     * Calcula y muestra la nota final del usuario en el curso.
     * La nota se redondea a entero según las prácticas comunes de calificación.
     *
     * @param object $row Fila de datos
     * @return string Nota final redondeada o guión si no hay nota
     */
    public function col_nota($row) {
        if (empty($row->finalgrade) || empty($row->grademax)) {
            return '-';
        }

        // Calcular la nota en escala de 0-20 (o la escala configurada).
        $finalgrade = $row->finalgrade;
        $grademax = $row->grademax;

        // Si la nota máxima no es 20, normalizar.
        if ($grademax != 20) {
            $finalgrade = ($finalgrade / $grademax) * 20;
        }

        // Redondear a entero.
        return round($finalgrade);
    }

    /**
     * Permite descargar los datos en formato CSV/Excel
     *
     * Sobrescribe el método para personalizar el formato de exportación
     * si es necesario en el futuro.
     *
     * @param string $format Formato de descarga
     * @param string $filename Nombre del archivo
     * @param string $sheettitle Título de la hoja
     */
    public function is_downloading($format = null, $filename = '', $sheettitle = '') {
        if ($format !== null) {
            $this->download = $format;
            $this->filename = $filename;
            $this->sheettitle = $sheettitle;
        }
        return $this->download;
    }
}
