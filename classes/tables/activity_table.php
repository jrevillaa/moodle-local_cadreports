<?php
/**
 * Tabla específica para reporte de actividad de usuarios
 * Plugin local_cadreports para Moodle 4.4
 */

namespace local_cadreports\tables;

use local_cadreports\base\table_base;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/cadreports/classes/base/table_base.php');

/**
 * Tabla específica para mostrar actividad de usuarios
 */
class activity_table extends table_base {

    /**
     * Configurar columnas específicas del reporte de actividad
     */
    protected function setup_specific_columns() {
        // Obtener columnas base y añadir columnas de actividad
        $base_columns = $this->get_base_columns();

        // Agregar columnas específicas de actividad de usuarios
        $activity_columns = [
            'action' => get_string('action', 'local_cadreports'),
            'component' => get_string('component', 'local_cadreports'),
            'target' => get_string('target', 'local_cadreports'),
            'target_name' => get_string('targetname', 'local_cadreports'),
            'event_description' => get_string('eventdescription', 'local_cadreports'),
            'datetime' => get_string('datetime', 'local_cadreports')
        ];

        $all_columns = array_merge($base_columns, $activity_columns);

        // Configurar ordenación por defecto por fecha (más reciente primero)
        $this->no_sorting('rownum');
        $this->sortable(true, 'datetime', SORT_DESC);

        return [array_keys($all_columns), array_values($all_columns)];
    }

    /**
     * Construir SQL específico del reporte de actividad de usuarios
     */
    protected function build_specific_sql() {
        global $DB;

        $params = [];

        // SQL con campos reales de logstore_standard_log
        $fields = "CONCAT(u.id, '_', c.id, '_', COALESCE(gr.id, 0), '_', l.id) as uniqueid,
               u.id as userid, 
               c.id as courseid,
               c.fullname as coursefullname,
               c.shortname as courseshortname,
               COALESCE(gr.name, '') as groupname,
               u.firstname,
               u.lastname, 
               u.username,
               u.email,
               l.action,
               l.component,
               l.target,
               l.eventname,
               l.objecttable,
               l.objectid,
               l.contextlevel,
               l.contextinstanceid,
               l.timecreated as datetime,
               COALESCE(target_info.name, '') as target_name,
               COALESCE(target_info.modname, '') as target_type";

        // FROM con subconsulta más robusta para nombres de actividades
        $from = "{course} c
             JOIN {enrol} e ON e.courseid = c.id
             JOIN {user_enrolments} ue ON ue.enrolid = e.id  
             JOIN {user} u ON u.id = ue.userid
             LEFT JOIN {groups_members} gm ON gm.userid = u.id AND gm.groupid IN (
                 SELECT id FROM {groups} WHERE courseid = c.id
             )
             LEFT JOIN {groups} gr ON gr.id = gm.groupid
             JOIN {logstore_standard_log} l ON l.userid = u.id AND l.courseid = c.id
             LEFT JOIN (
                 SELECT 
                     cm.id as cmid,
                     cm.course,
                     m.name as modname,
                     CASE 
                         WHEN m.name = 'quiz' THEN (SELECT name FROM {quiz} WHERE id = cm.instance)
                         WHEN m.name = 'assign' THEN (SELECT name FROM {assign} WHERE id = cm.instance)
                         WHEN m.name = 'forum' THEN (SELECT name FROM {forum} WHERE id = cm.instance)
                         WHEN m.name = 'scorm' THEN (SELECT name FROM {scorm} WHERE id = cm.instance)
                         WHEN m.name = 'lesson' THEN (SELECT name FROM {lesson} WHERE id = cm.instance)
                         WHEN m.name = 'resource' THEN (SELECT name FROM {resource} WHERE id = cm.instance)
                         WHEN m.name = 'url' THEN (SELECT name FROM {url} WHERE id = cm.instance)
                         WHEN m.name = 'page' THEN (SELECT name FROM {page} WHERE id = cm.instance)
                         ELSE 'Actividad'
                     END as name
                 FROM {course_modules} cm
                 JOIN {modules} m ON m.id = cm.module
             ) target_info ON target_info.cmid = l.contextinstanceid 
                          AND l.contextlevel = 70
                          AND target_info.course = c.id";

        // WHERE base con filtros de logs válidos
        $where = "u.deleted = 0 AND u.suspended = 0 AND ue.status = 0
              AND l.timecreated > 0
              AND l.component != ''
              AND l.action != ''";

        // Aplicar filtros comunes
        $this->apply_common_filters($where, $params);

        // Aplicar filtros específicos de actividad
        $this->apply_activity_filters($where, $params);

        $this->set_sql($fields, $from, $where, $params);
    }

    /**
     * Aplicar filtros específicos del reporte de actividad
     */
    private function apply_activity_filters(&$where, &$params) {
        // Filtro por acción específica
        if (!empty($this->filters['action'])) {
            $where .= " AND l.action = :action_filter";
            $params['action_filter'] = $this->filters['action'];
        }

        // Filtro por componente específico
        if (!empty($this->filters['component'])) {
            $where .= " AND l.component = :component_filter";
            $params['component_filter'] = $this->filters['component'];
        }

        // Filtro por rango de fechas de actividad
        if (!empty($this->filters['datefrom'])) {
            $where .= " AND l.timecreated >= :activity_datefrom";
            $params['activity_datefrom'] = $this->filters['datefrom'];
        }

        if (!empty($this->filters['dateto'])) {
            $where .= " AND l.timecreated <= :activity_dateto";
            $params['activity_dateto'] = $this->filters['dateto'];
        }
    }

    /**
     * Procesar columnas específicas del reporte de actividad
     */
    public function other_cols($colname, $row) {
        // Primero procesar columnas base
        $base_result = parent::other_cols($colname, $row);
        if ($base_result !== null && !in_array($colname, [
                'action', 'component', 'target', 'target_name', 'event_description', 'datetime'
            ])) {
            return $base_result;
        }

        // Procesar columnas específicas de actividad
        switch ($colname) {
            case 'action':
                return $this->format_action($row->action);

            case 'component':
                return $this->format_component($row->component);

            case 'target':
                return $this->format_target($row->target);

            case 'target_name':
                return $this->get_target_name($row);

            case 'event_description':
                return $this->get_event_description($row);

            case 'datetime':
                // ✅ USAR UTILITY METHOD
                return $this->safe_format_timestamp($row->datetime, null, false);

            default:
                return isset($row->$colname) ? $row->$colname : '';
        }
    }

    /**
     * Formatear fila específica para exportación
     */
    protected function format_export_row($row) {
        $row->action = $this->format_action($row->action);
        $row->component = $this->format_component($row->component);
        $row->target = $this->format_target($row->target);
        $row->target_name = $this->get_target_name($row);
        $row->event_description = $this->get_event_description($row);

        // ✅ USAR UTILITY METHOD para exportación
        $row->datetime = $this->safe_format_timestamp($row->datetime, null, true);
    }

    // ✅ ELIMINADO: método format_row() para evitar conflictos

    /**
     * Formatear acción para mostrar
     */
    private function format_action($action) {
        $actions_map = [
            'viewed' => 'Visto',
            'created' => 'Creado',
            'updated' => 'Actualizado',
            'submitted' => 'Enviado',
            'deleted' => 'Eliminado',
            'loggedin' => 'Inició Sesión',
            'loggedout' => 'Cerró Sesión',
            'started' => 'Iniciado',
            'abandoned' => 'Abandonado',
            'finished' => 'Finalizado'
        ];

        return isset($actions_map[$action]) ? $actions_map[$action] : ucfirst($action);
    }

    /**
     * Formatear componente para mostrar
     */
    private function format_component($component) {
        $components_map = [
            'core' => 'Sistema',
            'mod_quiz' => 'Cuestionario',
            'mod_forum' => 'Foro',
            'mod_assign' => 'Tarea',
            'mod_scorm' => 'SCORM',
            'mod_lesson' => 'Lección',
            'mod_resource' => 'Recurso',
            'mod_folder' => 'Carpeta',
            'mod_url' => 'URL',
            'mod_page' => 'Página'
        ];

        return isset($components_map[$component]) ? $components_map[$component] : $component;
    }

    /**
     * Formatear target para mostrar
     */
    private function format_target($target) {
        $targets_map = [
            'course' => 'Curso',
            'course_module' => 'Actividad',
            'user' => 'Usuario',
            'post' => 'Publicación',
            'submission' => 'Entrega',
            'attempt' => 'Intento',
            'grade' => 'Calificación'
        ];

        return isset($targets_map[$target]) ? $targets_map[$target] : ucfirst($target);
    }

    /**
     * Obtener nombre específico del target
     */
    private function get_target_name($row) {
        // Si tenemos nombre desde la subconsulta, usarlo
        if (!empty($row->target_name)) {
            return $row->target_name;
        }

        // Fallbacks según el tipo de target y contextlevel
        switch ($row->target) {
            case 'course':
                return $row->coursefullname;
            case 'user':
                // Si es un usuario específico, obtener su nombre
                if (!empty($row->objectid) && $row->objecttable == 'user') {
                    global $DB;
                    $user = $DB->get_record('user', ['id' => $row->objectid], 'firstname,lastname');
                    if ($user) {
                        return $user->firstname . ' ' . $user->lastname;
                    }
                }
                return $row->firstname . ' ' . $row->lastname;
            case 'course_module':
            case 'activity':
                if (!empty($row->target_type)) {
                    return $this->format_component('mod_' . $row->target_type);
                }
                return 'Actividad';
            default:
                return ucfirst($row->target);
        }
    }

    /**
     * Obtener descripción del evento basada en eventname
     */
    private function get_event_description($row) {
        // Generar descripción basada en el eventname
        $event_descriptions = [
            '\\core\\event\\course_viewed' => 'Accedió al curso',
            '\\mod_quiz\\event\\course_module_viewed' => 'Vió el cuestionario',
            '\\mod_quiz\\event\\attempt_started' => 'Inició intento de cuestionario',
            '\\mod_quiz\\event\\attempt_submitted' => 'Envió intento de cuestionario',
            '\\mod_quiz\\event\\attempt_reviewed' => 'Revisó intento de cuestionario',
            '\\mod_forum\\event\\course_module_viewed' => 'Accedió al foro',
            '\\mod_forum\\event\\discussion_created' => 'Creó discusión en foro',
            '\\mod_forum\\event\\post_created' => 'Publicó en foro',
            '\\mod_assign\\event\\course_module_viewed' => 'Vió la tarea',
            '\\mod_assign\\event\\submission_created' => 'Envió tarea',
            '\\mod_assign\\event\\submission_updated' => 'Actualizó envío de tarea',
            '\\core\\event\\user_loggedin' => 'Inició sesión',
            '\\core\\event\\user_loggedout' => 'Cerró sesión'
        ];

        if (isset($event_descriptions[$row->eventname])) {
            $description = $event_descriptions[$row->eventname];
            $target_name = $this->get_target_name($row);

            if ($target_name !== '-' && !empty($target_name)) {
                return $description . ': ' . $target_name;
            }
            return $description;
        }

        // Descripción genérica
        $action = $this->format_action($row->action);
        $target = $this->format_target($row->target);
        $target_name = $this->get_target_name($row);

        return "$action $target" . ($target_name !== '-' ? ": $target_name" : '');
    }
}
