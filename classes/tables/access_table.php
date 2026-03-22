<?php
/**
 * Tabla específica para reporte de accesos y dedicación
 * Columnas: Grupo / Curso / Apellidos / Nombres / DNI / Número de Ingresos / Último Acceso
 * Los cálculos de ingresos y último acceso respetan el rango de fechas opcional
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
 * Tabla específica para mostrar accesos y dedicación de participantes
 * Agrupa todos los cursos con el mismo código de grupo
 * Calcula ingresos y último acceso según rango de fechas opcional
 */
class access_table extends table_base {

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
     * Configurar columnas específicas del reporte de accesos
     * Columnas: Grupo / Curso / Apellidos / Nombres / DNI / Número de Ingresos / Último Acceso
     *
     * @return array Array con columnas y headers
     */
    protected function setup_specific_columns() {
        $cols = [
            'groupname'        => get_string('group'),
            'coursename'       => get_string('course'),
            'lastname'         => get_string('lastname'),
            'firstname'        => get_string('firstname'),
            'dni'              => 'DNI',
            'course_accesses'  => get_string('courseaccesses', 'local_cadreports'),
            'last_course_access' => get_string('lastcourseaccess', 'local_cadreports')
        ];

        // Configurar columnas no ordenables
        $this->no_sorting('groupname');

        return [array_keys($cols), array_values($cols)];
    }

    /**
     * Construir SQL específico del reporte de accesos
     * Incluye agregación de grupos con el mismo código
     * Aplica rango de fechas opcional a los cálculos de ingresos y último acceso
     */
    protected function build_specific_sql() {
        global $DB;

        // Subconsulta para agregar grupos por usuario-curso (evita duplicados)
        $concat = $DB->sql_concat('g.name', "' ('", 'g.idnumber', "')'");
        $grouplabel = "CASE WHEN g.idnumber IS NULL OR g.idnumber = '' THEN g.name ELSE {$concat} END";
        $groupagg = $DB->sql_group_concat('DISTINCT '.$grouplabel, ', ');

        // Subconsulta para obtener grupos del usuario en el curso
        $groupsubquery = "
            SELECT gm.userid, g.courseid, {$groupagg} AS groupnames
            FROM {groups_members} gm
            JOIN {groups} g ON g.id = gm.groupid
            GROUP BY gm.userid, g.courseid
        ";

        // Identificador único por usuario-curso
        $unique = $DB->sql_concat('u.id', "'_'", 'c.id');

        // Campos a seleccionar
        $fields = "
            {$unique}    AS uniqueid,
            u.id         AS userid,
            u.firstname  AS firstname,
            u.lastname   AS lastname,
            u.username   AS dni,
            c.id         AS courseid,
            c.fullname   AS coursename,
            COALESCE(grp.groupnames, '-') AS groupname,
            COALESCE(access_count.total_accesses, 0) AS course_accesses,
            COALESCE(last_access.last_time, 0) AS last_course_access
        ";

        // FROM con JOINs necesarios
        $from = "
            {enrol} e
            JOIN {user_enrolments} ue ON ue.enrolid = e.id
            JOIN {user} u             ON u.id = ue.userid
            JOIN {course} c           ON c.id = e.courseid

            LEFT JOIN ({$groupsubquery}) grp ON grp.userid = u.id AND grp.courseid = c.id
        ";

        // ===== SUBCONSULTA PARA CONTAR ACCESOS (con rango de fechas opcional) =====
        $access_subquery = "
            SELECT 
                userid,
                courseid,
                COUNT(*) as total_accesses
            FROM {logstore_standard_log}
            WHERE action = 'viewed' 
            AND target = 'course'
        ";

        $access_params = [];

        // Aplicar filtro de fecha DESDE (datefrom)
        if (!empty($this->filters['datefrom'])) {
            $access_subquery .= " AND timecreated >= :access_count_datefrom";
            $access_params['access_count_datefrom'] = $this->filters['datefrom'];
        }

        // Aplicar filtro de fecha HASTA (dateto)
        if (!empty($this->filters['dateto'])) {
            $access_subquery .= " AND timecreated <= :access_count_dateto";
            $access_params['access_count_dateto'] = $this->filters['dateto'];
        }

        $access_subquery .= " GROUP BY userid, courseid";

        // Agregar subconsulta de accesos al FROM
        $from .= " LEFT JOIN ({$access_subquery}) access_count ON access_count.userid = u.id AND access_count.courseid = c.id";

        // ===== SUBCONSULTA PARA ÚLTIMO ACCESO (con rango de fechas opcional) =====
        $lastaccess_subquery = "
            SELECT 
                userid,
                courseid,
                MAX(timecreated) as last_time
            FROM {logstore_standard_log}
            WHERE action = 'viewed' 
            AND target = 'course'
        ";

        $lastaccess_params = [];

        // Aplicar filtro de fecha DESDE (datefrom)
        if (!empty($this->filters['datefrom'])) {
            $lastaccess_subquery .= " AND timecreated >= :last_access_datefrom";
            $lastaccess_params['last_access_datefrom'] = $this->filters['datefrom'];
        }

        // Aplicar filtro de fecha HASTA (dateto)
        if (!empty($this->filters['dateto'])) {
            $lastaccess_subquery .= " AND timecreated <= :last_access_dateto";
            $lastaccess_params['last_access_dateto'] = $this->filters['dateto'];
        }

        $lastaccess_subquery .= " GROUP BY userid, courseid";

        // Agregar subconsulta de último acceso al FROM
        $from .= " LEFT JOIN ({$lastaccess_subquery}) last_access ON last_access.userid = u.id AND last_access.courseid = c.id";

        // Condiciones WHERE base
        $where = "c.id <> :siteid AND u.deleted = 0 AND ue.status = 0";
        $params = array_merge(['siteid' => SITEID], $access_params, $lastaccess_params);

        // === Aplicar filtros de modo (bycourse / byuser) ===
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

        // ✅ NUEVO: Excluir usuarios sin accesos (solo mostrar usuarios con al menos un ingreso)
        $where .= " AND COALESCE(access_count.total_accesses, 0) > 0";

        // Configurar SQL en la tabla
        $this->set_sql($fields, $from, $where, $params);

        // SQL para contar registros (paginación)
        $countsql = "SELECT COUNT(DISTINCT {$unique}) FROM {$from} WHERE {$where}";
        $this->set_count_sql($countsql, $params);
    }

    /**
     * Procesar columnas específicas del reporte
     *
     * @param string $colname Nombre de la columna
     * @param object $row Fila de datos
     * @return string Valor formateado
     */
    public function other_cols($colname, $row) {
        $dash = '-';

        switch ($colname) {
            case 'groupname':
                $val = (string)($row->groupname ?? '');
                return ($val !== '' && $val !== '-') ? ($this->is_downloading() ? $val : format_string($val)) : $dash;

            case 'coursename':
                $val = (string)($row->coursename ?? '');
                return $val !== '' ? format_string($val) : $dash;

            case 'lastname':
                $val = (string)($row->lastname ?? '');
                return $val !== '' ? s($val) : $dash;

            case 'firstname':
                $val = (string)($row->firstname ?? '');
                return $val !== '' ? s($val) : $dash;

            case 'dni':
                $val = (string)($row->dni ?? '');
                return $val !== '' ? s($val) : $dash;

            case 'course_accesses':
                return (int)$row->course_accesses;

            case 'last_course_access':
                if (!empty($row->last_course_access) && $row->last_course_access > 0) {
                    return $this->is_downloading()
                        ? userdate($row->last_course_access, '%d/%m/%Y %H:%M:%S')
                        : userdate($row->last_course_access, get_string('strftimedatetimeshort', 'core_langconfig'));
                }
                return $this->is_downloading() ? 'Nunca' : get_string('never', 'core');
        }

        return parent::other_cols($colname, $row);
    }

    /**
     * Formatear fila para exportación
     *
     * @param object $row Fila de datos
     */
    protected function format_export_row($row) {
        $dash = '-';

        $row->groupname = isset($row->groupname) && $row->groupname !== '' && $row->groupname !== '-' ? (string)$row->groupname : $dash;
        $row->coursename = isset($row->coursename) && $row->coursename !== '' ? (string)$row->coursename : $dash;
        $row->lastname = isset($row->lastname) && $row->lastname !== '' ? (string)$row->lastname : $dash;
        $row->firstname = isset($row->firstname) && $row->firstname !== '' ? (string)$row->firstname : $dash;
        $row->dni = isset($row->dni) && $row->dni !== '' ? (string)$row->dni : $dash;
        $row->course_accesses = (int)$row->course_accesses;

        if (!empty($row->last_course_access) && $row->last_course_access > 0) {
            $row->last_course_access = userdate($row->last_course_access, '%d/%m/%Y %H:%M:%S');
        } else {
            $row->last_course_access = 'Nunca';
        }
    }

    /**
     * Convierte una cadena de texto en tokens
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
