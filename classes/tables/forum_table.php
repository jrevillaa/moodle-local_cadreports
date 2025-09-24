<?php
/**
 * Tabla específica para reporte de participación en foros
 * Plugin local_cadreports para Moodle 4.4
 */

namespace local_cadreports\tables;

use local_cadreports\base\table_base;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/cadreports/classes/base/table_base.php');

/**
 * Tabla específica para mostrar participación en foros
 */
class forum_table extends table_base {

    /**
     * Configurar columnas específicas del reporte de foros
     */
    protected function setup_specific_columns() {
        // Obtener columnas base y añadir columnas de foros
        $base_columns = $this->get_base_columns();

        // Agregar columnas específicas de participación en foros
        $forum_columns = [
            'forum_name' => get_string('forumname', 'local_cadreports'),
            'forum_idnumber' => get_string('forumidnumber', 'local_cadreports'),
            'participation_date' => get_string('participationdate', 'local_cadreports'),
            'staff_response_status' => get_string('staffresponsestatus', 'local_cadreports'),
            'staff_response_date' => get_string('staffresponsedate', 'local_cadreports')
        ];

        $all_columns = array_merge($base_columns, $forum_columns);

        // Configurar ordenación
        $this->no_sorting('rownum');
        $this->sortable(true, 'participation_date', SORT_DESC);

        return [array_keys($all_columns), array_values($all_columns)];
    }

    /**
     * Construir SQL específico del reporte de participación en foros
     */
    protected function build_specific_sql() {
        global $DB;

        $params = [];

        // SQL específico para participación en foros con restricciones de grupo
        $fields = "CONCAT(u.id, '_', c.id, '_', COALESCE(gr.id, 0), '_', f.id, '_', COALESCE(fp.id, 0)) as uniqueid,
                   u.id as userid, 
                   c.id as courseid,
                   c.fullname as coursefullname,
                   c.shortname as courseshortname,
                   COALESCE(gr.name, '') as groupname,
                   u.firstname,
                   u.lastname, 
                   u.username,
                   u.email,
                   f.name as forum_name,
                   COALESCE(cm.idnumber, '') as forum_idnumber,
                   COALESCE(user_participation.latest_post, 0) as participation_date,
                   CASE 
                       WHEN staff_response.response_count > 0 THEN 'Respondido'
                       WHEN user_participation.post_count > 0 THEN 'Sin respuesta'
                       ELSE 'Sin participación'
                   END as staff_response_status,
                   COALESCE(staff_response.latest_response, 0) as staff_response_date";

        // FROM con JOINs complejos para manejar restricciones de grupo
        $from = "{course} c
                 JOIN {enrol} e ON e.courseid = c.id
                 JOIN {user_enrolments} ue ON ue.enrolid = e.id  
                 JOIN {user} u ON u.id = ue.userid
                 LEFT JOIN {groups_members} gm ON gm.userid = u.id AND gm.groupid IN (
                     SELECT id FROM {groups} WHERE courseid = c.id
                 )
                 LEFT JOIN {groups} gr ON gr.id = gm.groupid
                 JOIN {forum} f ON f.course = c.id
                 JOIN {course_modules} cm ON cm.course = c.id 
                       AND cm.module = (SELECT id FROM {modules} WHERE name = 'forum') 
                       AND cm.instance = f.id
                 LEFT JOIN {forum_posts} fp ON fp.userid = u.id 
                       AND fp.discussion IN (
                           SELECT id FROM {forum_discussions} 
                           WHERE forum = f.id
                       )
                 LEFT JOIN (
                     SELECT 
                         fp2.userid,
                         fd2.forum,
                         COUNT(*) as post_count,
                         MAX(fp2.created) as latest_post
                     FROM {forum_posts} fp2
                     JOIN {forum_discussions} fd2 ON fd2.id = fp2.discussion
                     WHERE fp2.parent > 0"; // Solo posts de respuesta, no posts iniciales

        // Aplicar filtro de fechas para participación
        if (!empty($this->filters['datefrom'])) {
            $from .= " AND fp2.created >= :participation_datefrom";
            $params['participation_datefrom'] = $this->filters['datefrom'];
        }

        if (!empty($this->filters['dateto'])) {
            $from .= " AND fp2.created <= :participation_dateto";
            $params['participation_dateto'] = $this->filters['dateto'];
        }

        $from .= "     GROUP BY fp2.userid, fd2.forum
                 ) user_participation ON user_participation.userid = u.id 
                                    AND user_participation.forum = f.id
                 LEFT JOIN (
                     SELECT 
                         fd3.forum,
                         COUNT(DISTINCT fp3.id) as response_count,
                         MAX(fp3.created) as latest_response
                     FROM {forum_posts} fp3
                     JOIN {forum_discussions} fd3 ON fd3.id = fp3.discussion
                     JOIN {user} staff_user ON staff_user.id = fp3.userid
                     JOIN {role_assignments} ra ON ra.userid = staff_user.id
                     JOIN {role} r ON r.id = ra.roleid
                     WHERE r.shortname IN ('manager', 'coursecreator', 'editingteacher', 'teacher')
                     AND ra.contextid IN (
                         SELECT ctx.id FROM {context} ctx 
                         WHERE ctx.contextlevel = 50 
                         AND ctx.instanceid = fd3.forum
                     )";

        // Aplicar filtro de fechas para respuestas del staff
        if (!empty($this->filters['datefrom'])) {
            $from .= " AND fp3.created >= :staff_response_datefrom";
            $params['staff_response_datefrom'] = $this->filters['datefrom'];
        }

        if (!empty($this->filters['dateto'])) {
            $from .= " AND fp3.created <= :staff_response_dateto";
            $params['staff_response_dateto'] = $this->filters['dateto'];
        }

        $from .= "     GROUP BY fd3.forum
                 ) staff_response ON staff_response.forum = f.id";

        // WHERE base con restricciones de visibilidad
        $where = "u.deleted = 0 AND u.suspended = 0 AND ue.status = 0
                  AND cm.visible = 1
                  AND (
                      cm.groupmode = 0 
                      OR gr.id IS NOT NULL 
                      OR cm.groupmode = 1
                  )";

        // Aplicar filtros comunes
        $this->apply_common_filters($where, $params);

        // Aplicar filtros específicos de foros
        $this->apply_forum_filters($where, $params);

        $this->set_sql($fields, $from, $where, $params);
    }

    /**
     * Aplicar filtros específicos del reporte de foros
     */
    private function apply_forum_filters(&$where, &$params) {
        // Filtro por estado de participación
        if (!empty($this->filters['participation_status'])) {
            switch ($this->filters['participation_status']) {
                case 'participated':
                    $where .= " AND user_participation.post_count > 0";
                    break;
                case 'not_participated':
                    $where .= " AND (user_participation.post_count IS NULL OR user_participation.post_count = 0)";
                    break;
                case 'responded_by_staff':
                    $where .= " AND staff_response.response_count > 0";
                    break;
            }
        }
    }

    /**
     * Procesar columnas específicas del reporte de foros
     */
    public function other_cols($colname, $row) {
        // Primero procesar columnas base
        $base_result = parent::other_cols($colname, $row);
        if ($base_result !== null && !in_array($colname, [
                'forum_name', 'forum_idnumber', 'participation_date',
                'staff_response_status', 'staff_response_date'
            ])) {
            return $base_result;
        }

        // Procesar columnas específicas de foros
        switch ($colname) {
            case 'forum_name':
                return !empty($row->forum_name) ? $row->forum_name : '-';

            case 'forum_idnumber':
                return !empty($row->forum_idnumber) ? $row->forum_idnumber : '-';

            case 'participation_date':
                return $this->safe_format_timestamp($row->participation_date, null, false);

            case 'staff_response_status':
                return $this->get_response_status_display($row->staff_response_status);

            case 'staff_response_date':
                return $this->safe_format_timestamp($row->staff_response_date, null, false);

            default:
                return isset($row->$colname) ? $row->$colname : '';
        }
    }

    /**
     * Obtener texto de estado de respuesta con estilo
     */
    private function get_response_status_display($status) {
        $status_map = [
            'Respondido' => '<span class="badge badge-success">Respondido</span>',
            'Sin respuesta' => '<span class="badge badge-warning">Sin respuesta</span>',
            'Sin participación' => '<span class="badge badge-secondary">Sin participación</span>'
        ];

        // Para exportación, sin HTML
        if ($this->is_downloading()) {
            return $status;
        }

        return isset($status_map[$status]) ? $status_map[$status] : $status;
    }

    /**
     * Formatear fila específica para exportación
     */
    protected function format_export_row($row) {
        $row->forum_name = !empty($row->forum_name) ? $row->forum_name : '-';
        $row->forum_idnumber = !empty($row->forum_idnumber) ? $row->forum_idnumber : '-';

        // Formatear fechas para exportación
        $row->participation_date = $this->safe_format_timestamp($row->participation_date, null, true);
        $row->staff_response_date = $this->safe_format_timestamp($row->staff_response_date, null, true);

        // Estado sin HTML para exportación
        $row->staff_response_status = strip_tags($row->staff_response_status);
    }
}
