<?php
/**
 * Tabla específica para Exportación de Notas a ERP
 * Genera estructura: proyecto, dni, nota
 *
 * @package    local_cadreports
 * @copyright  2024 Jair Revilla <jrevilla492@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cadreports\tables;

use local_cadreports\base\table_base;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/cadreports/classes/base/table_base.php');

/**
 * Clase de tabla para exportación a ERP
 * Extiende table_base y define columnas específicas
 */
class erp_export_table extends table_base {

    /** @var array Filtros aplicados */
    protected $filters;

    /**
     * Constructor de la tabla
     *
     * @param string $uniqueid Identificador único de la tabla
     * @param array $filters Filtros aplicados al reporte
     */
    public function __construct(string $uniqueid, array $filters) {
        parent::__construct($uniqueid);
        $this->filters = $filters ?? [];
        $this->setup_table();
    }

    /**
     * Define las columnas específicas de la tabla de exportación a ERP
     * Columnas: proyecto, dni, nota
     *
     * @return array Array con columnas y headers
     */
    protected function setup_specific_columns() {
        $cols = [
            'proyecto' => get_string('proyecto', 'local_cadreports'),
            'dni'      => get_string('dni', 'local_cadreports'),
            'nota'     => get_string('nota', 'local_cadreports')
        ];
        return [array_keys($cols), array_values($cols)];
    }

    /**
     * Construye la consulta SQL específica para obtener datos de exportación a ERP
     * Incluye lógica para extraer código de proyecto del nombre del grupo
     */
    protected function build_specific_sql() {
        global $DB;

        // Identificador único por usuario-curso
        $unique = $DB->sql_concat('u.id', "'_'", 'c.id');

        // Campos a seleccionar
        $fields = "
            {$unique}    AS uniqueid,
            u.id         AS userid,
            u.username   AS dni,
            c.id         AS courseid,
            g.name       AS groupname,
            gg.finalgrade AS finalgrade,
            gi.grademax   AS grademax
        ";

        // FROM con JOINs necesarios
        $from = "
            {enrol} e
            JOIN {user_enrolments} ue ON ue.enrolid = e.id
            JOIN {user} u             ON u.id = ue.userid
            JOIN {course} c           ON c.id = e.courseid

            LEFT JOIN {groups_members} gm ON gm.userid = u.id
            LEFT JOIN {groups} g          ON g.id = gm.groupid AND g.courseid = c.id

            LEFT JOIN {grade_items} gi    ON gi.courseid = c.id AND gi.itemtype = 'course'
            LEFT JOIN {grade_grades} gg   ON gg.itemid = gi.id AND gg.userid = u.id
        ";

        // Condiciones WHERE base
        // ✅ NUEVO: Excluir usuarios sin grupo O sin nota
        $where = "c.id <> :siteid 
                  AND u.deleted = 0 
                  AND ue.status = 0
                  AND g.id IS NOT NULL
                  AND gg.finalgrade IS NOT NULL";
        $params = ['siteid' => SITEID];

        // === Aplicar filtros ===
        $mode = $this->filters['mode'] ?? 'bycourse';

        if ($mode === 'byuser') {
            // Búsqueda por DNI (username) o email
            $tokens = [];
            if (!empty($this->filters['userquery'])) {
                if (is_array($this->filters['userquery'])) {
                    foreach ($this->filters['userquery'] as $t) {
                        $t = trim((string)$t);
                        if ($t !== '') { $tokens[] = $t; }
                    }
                } else {
                    $tokens = $this->explode_tokens((string)$this->filters['userquery']);
                }
            }
            if (!empty($tokens)) {
                $likes = [];
                foreach ($tokens as $i => $tok) {
                    $p1 = "u_un_{$i}";
                    $p2 = "u_em_{$i}";
                    $likes[] = "(u.username LIKE :{$p1} OR u.email LIKE :{$p2})";
                    $params[$p1] = '%'.$DB->sql_like_escape($tok).'%';
                    $params[$p2] = '%'.$DB->sql_like_escape($tok).'%';
                }
                $where .= " AND (".implode(' OR ', $likes).")";
            } else {
                $where .= " AND 1=0";
            }
        } else {
            // Modo bycourse: filtrar por cursos
            if (!empty($this->filters['courseids']) && is_array($this->filters['courseids'])) {
                // ✅ CORREGIDO: Detectar si se seleccionó "Todos los cursos" (ID -1)
                if (!in_array(-1, $this->filters['courseids'])) {
                    $courseids = array_values(array_filter($this->filters['courseids'], function($id) {
                        return is_numeric($id) && $id > 0;
                    }));
                    
                    if ($courseids) {
                        list($insql, $inparams) = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED, 'cid');
                        $where .= " AND c.id {$insql}";
                        $params = array_merge($params, $inparams);
                    }
                }
            }

            // Filtrar por grupos
            if (!empty($this->filters['groupids']) && is_array($this->filters['groupids'])) {
                $groupids = array_values(array_filter($this->filters['groupids'], 'is_numeric'));
                if ($groupids) {
                    list($insql, $inparams) = $DB->get_in_or_equal($groupids, SQL_PARAMS_NAMED, 'gid');
                    $where .= " AND u.id IN (
                        SELECT gm2.userid
                        FROM {groups_members} gm2
                        WHERE gm2.groupid {$insql}
                    )";
                    $params = array_merge($params, $inparams);
                }
            }
        }

        // Configurar SQL en la tabla
        $this->set_sql($fields, $from, $where, $params);

        // SQL para contar registros (paginación)
        $countsql = "SELECT COUNT(DISTINCT {$unique}) FROM {$from} WHERE {$where}";
        $this->set_count_sql($countsql, $params);
    }

    /**
     * Formatea las columnas personalizadas de la tabla
     *
     * @param string $colname Nombre de la columna
     * @param object $row Fila de datos
     * @return string Valor formateado
     */
    public function other_cols($colname, $row) {
        $dash = '-';

        switch ($colname) {
            case 'proyecto':
                return $this->format_proyecto($row->groupname ?? '');

            case 'dni':
                $val = (string)($row->dni ?? '');
                return $val !== '' ? s($val) : $dash;

            case 'nota':
                return $this->format_nota($row->finalgrade ?? null, $row->grademax ?? null);
        }

        return parent::other_cols($colname, $row);
    }

    /**
     * Formatea la fila para exportación
     *
     * @param object $row Fila de datos
     * @return array Array con datos formateados para exportación
     */
    protected function format_export_row($row) {
        $dash = '-';

        return [
            'proyecto' => $this->format_proyecto($row->groupname ?? ''),
            'dni'      => isset($row->dni) && $row->dni !== '' ? (string)$row->dni : $dash,
            'nota'     => $this->format_nota($row->finalgrade ?? null, $row->grademax ?? null),
        ];
    }

    /**
     * Extrae el código del proyecto del nombre del grupo
     * Formato esperado: G_DD/MM/YYYY_CXXXXXXXX
     * Si no sigue el formato, devuelve el nombre completo del grupo
     *
     * @param string $groupname Nombre del grupo
     * @return string Código del proyecto o nombre del grupo
     */
    private function format_proyecto($groupname) {
        if (empty($groupname)) {
            return '-';
        }

        // Intentar extraer el código del proyecto
        $parts = explode('_', $groupname);

        // Verificar si sigue la estructura esperada: G_fecha_código
        if (count($parts) == 5) {
            // El código del proyecto es la última parte
            $projectcode = end($parts);

            // Validar que el código empiece con 'C' seguido de números
            if (preg_match('/^C\d+$/', $projectcode)) {
                return $projectcode;
            }
        }

        // Si no sigue la estructura, devolver el nombre completo del grupo
        return $groupname;
    }

    /**
     * Formatea la nota final del usuario
     * Normaliza a escala de 20 y redondea a entero
     *
     * @param float|null $finalgrade Nota final del usuario
     * @param float|null $grademax Nota máxima del curso
     * @return string Nota formateada o guión si no hay nota
     */
    private function format_nota($finalgrade, $grademax) {
        if ($finalgrade === null || $finalgrade === '' || $grademax === null || $grademax == 0) {
            return '-';
        }

        // Normalizar a escala de 20 si es necesario
        $nota = (float)$finalgrade;
        $max = (float)$grademax;

        if ($max != 20) {
            $nota = ($nota / $max) * 20;
        }

        // Redondear a entero
        return (string)round($nota);
    }

    /**
     * Convierte una cadena de texto en tokens
     * Separa por comas, saltos de línea o punto y coma
     *
     * @param string $text Texto a procesar
     * @return array Array de tokens
     */
    private function explode_tokens(string $text): array {
        $raw = preg_split('/[,\n;]+/', (string)$text);
        $tokens = [];
        foreach ($raw as $t) {
            $t = trim($t);
            if ($t !== '') $tokens[] = $t;
        }
        return $tokens;
    }
}
