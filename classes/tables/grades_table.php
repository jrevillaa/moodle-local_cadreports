<?php
/**
 * Tabla específica para Registro de Notas
 * CORREGIDO: Elimina duplicados y muestra todos los usuarios matriculados
 */

namespace local_cadreports\tables;

use local_cadreports\base\table_base;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/cadreports/classes/base/table_base.php');

class grades_table extends table_base {

    /** @var array */
    protected $filters;

    public function __construct(string $uniqueid, array $filters) {
        parent::__construct($uniqueid);
        $this->filters = $filters ?? [];
        $this->setup_table();
    }

    protected function setup_specific_columns() {
        $cols = [
            'groupname'   => get_string('group'),
            'coursename'  => get_string('course'),
            'courseshort' => get_string('shortname'),
            'lastname'    => get_string('lastname'),
            'firstname'   => get_string('firstname'),
            'dni'         => 'DNI',
            'finalgrade'  => 'Nota'
        ];
        return [array_keys($cols), array_values($cols)];
    }

    protected function build_specific_sql() {
        global $DB;

        // Subconsulta para agregar grupos por usuario-curso (evita duplicados en el JOIN principal)
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

        $unique = $DB->sql_concat('u.id', "'_'", 'c.id');

        $fields = "
            {$unique}    AS uniqueid,
            u.id         AS userid,
            u.firstname  AS firstname,
            u.lastname   AS lastname,
            u.username   AS dni,
            c.id         AS courseid,
            c.fullname   AS coursename,
            c.shortname  AS courseshort,
            gg.finalgrade AS finalgrade,
            COALESCE(grp.groupnames, '-') AS groupname
        ";

        // JOIN principal: enrol -> user_enrolments -> user
        // LEFT JOIN: grupos (subconsulta) y notas
        $from = "
            {enrol} e
            JOIN {user_enrolments} ue ON ue.enrolid = e.id
            JOIN {user} u             ON u.id = ue.userid
            JOIN {course} c           ON c.id = e.courseid

            LEFT JOIN ({$groupsubquery}) grp ON grp.userid = u.id AND grp.courseid = c.id

            LEFT JOIN {grade_items} gi    ON gi.courseid = c.id AND gi.itemtype = 'course'
            LEFT JOIN {grade_grades} gg   ON gg.itemid = gi.id AND gg.userid = u.id
        ";

        $where = "c.id <> :siteid AND u.deleted = 0 AND ue.status = 0";
        $params = ['siteid' => SITEID];

        // === Filtros ===
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
                $courseids = array_values(array_filter($this->filters['courseids'], 'is_numeric'));
                if ($courseids) {
                    list($insql, $inparams) = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED, 'cid');
                    $where .= " AND c.id {$insql}";
                    $params = array_merge($params, $inparams);
                }
            }

            // Filtrar por grupos: usa subconsulta para NO excluir usuarios sin grupo
            if (!empty($this->filters['groupids']) && is_array($this->filters['groupids'])) {
                $groupids = array_values(array_filter($this->filters['groupids'], 'is_numeric'));
                if ($groupids) {
                    // Subconsulta: usuarios que están en los grupos especificados
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

        // No necesitas GROUP BY porque la subconsulta ya agrupa los grupos
        $this->set_sql($fields, $from, $where, $params);

        // COUNT para paginación
        $countsql = "SELECT COUNT(DISTINCT {$unique}) FROM {$from} WHERE {$where}";
        $this->set_count_sql($countsql, $params);
    }

    public function other_cols($colname, $row) {
        $dash = '-';
        switch ($colname) {
            case 'groupname':
                $val = (string)($row->groupname ?? '');
                return ($val !== '' && $val !== '-') ? ($this->is_downloading() ? $val : format_string($val)) : $dash;

            case 'coursename':
                $val = (string)($row->coursename ?? '');
                return $val !== '' ? format_string($val) : $dash;

            case 'courseshort':
                $val = (string)($row->courseshort ?? '');
                return $val !== '' ? s($val) : $dash;

            case 'lastname':
                $val = (string)($row->lastname ?? '');
                return $val !== '' ? s($val) : $dash;

            case 'firstname':
                $val = (string)($row->firstname ?? '');
                return $val !== '' ? s($val) : $dash;

            case 'dni':
                $val = (string)($row->dni ?? '');
                return $val !== '' ? s($val) : $dash;

            case 'finalgrade':
                if ($row->finalgrade === null || $row->finalgrade === '') {
                    return $dash;
                }
                return number_format((float)$row->finalgrade, 2);
        }
        return parent::other_cols($colname, $row);
    }

    protected function format_export_row($row) {
        $dash = '-';
        $finalgrade = ($row->finalgrade === null || $row->finalgrade === '') ? $dash : number_format((float)$row->finalgrade, 2);

        return [
            'groupname'   => isset($row->groupname) && $row->groupname !== '' && $row->groupname !== '-' ? (string)$row->groupname : $dash,
            'coursename'  => isset($row->coursename) && $row->coursename !== '' ? (string)$row->coursename : $dash,
            'courseshort' => isset($row->courseshort) && $row->courseshort !== '' ? (string)$row->courseshort : $dash,
            'lastname'    => isset($row->lastname) && $row->lastname !== '' ? (string)$row->lastname : $dash,
            'firstname'   => isset($row->firstname) && $row->firstname !== '' ? (string)$row->firstname : $dash,
            'dni'         => isset($row->dni) && $row->dni !== '' ? (string)$row->dni : $dash,
            'finalgrade'  => $finalgrade,
        ];
    }

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
