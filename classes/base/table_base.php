<?php
/**
 * Clase base para tablas reutilizable - Extiende table_sql nativo de Moodle
 * Plugin local_cadreports para Moodle 4.4
 */

namespace local_cadreports\base;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/tablelib.php');

/**
 * Clase base extendida de table_sql con funcionalidades comunes
 */
abstract class table_base extends \table_sql {

    /** @var array Filtros aplicados */
    protected $filters = [];

    /** @var string Prefijo único para IDs de tabla */
    protected $tableprefix = 'cadreports';

    /**
     * Constructor
     * @param string $uniqueid ID único de la tabla
     * @param array $filters Filtros aplicados
     */
    public function __construct($uniqueid, $filters = []) {
        parent::__construct($this->tableprefix . '_' . $uniqueid);
        $this->filters = $filters;
    }

    /**
     * Configuración base común para todas las tablas
     */
    protected function setup_base_config() {
        // Configuración estándar de tabla
        $this->sortable(true);
        $this->pageable(true);
        $this->is_collapsible = false;
        $this->initialbars(false);

        // CSS classes comunes
        $this->set_attribute('class', 'generaltable cadreports-table table-striped table-hover');

        // Configurar exportación usando core de Moodle
        $this->is_downloadable(true);
        $this->show_download_buttons_at([TABLE_P_BOTTOM]);
    }

    /**
     * Obtener columnas base comunes (orden, curso, grupo, usuario)
     */
    protected function get_base_columns() {
        return [
            'rownum' => get_string('order', 'local_cadreports'),
            'coursefullname' => get_string('coursefullname', 'local_cadreports'),
            'courseshortname' => get_string('courseshortname', 'local_cadreports'),
            'groupname' => get_string('group', 'local_cadreports'),
            'firstname' => get_string('firstname', 'local_cadreports'),
            'lastname' => get_string('lastname', 'local_cadreports'),
            'username' => get_string('username', 'local_cadreports'),
            'email' => get_string('email', 'local_cadreports')
        ];
    }

    /**
     * Construir base SQL común para obtener usuarios, cursos y grupos
     */
    protected function build_base_sql() {
        $fields = "DISTINCT u.id as userid, 
                   c.id as courseid,
                   c.fullname as coursefullname,
                   c.shortname as courseshortname,
                   COALESCE(g.name, '') as groupname,
                   u.firstname,
                   u.lastname, 
                   u.username,
                   u.email";

        $from = "{course} c
                 JOIN {enrol} e ON e.courseid = c.id
                 JOIN {user_enrolments} ue ON ue.enrolid = e.id  
                 JOIN {user} u ON u.id = ue.userid
                 LEFT JOIN {groups_members} gm ON gm.userid = u.id AND gm.groupid IN (
                     SELECT id FROM {groups} WHERE courseid = c.id
                 )
                 LEFT JOIN {groups} g ON g.id = gm.groupid";

        $where = "u.deleted = 0 AND u.suspended = 0 AND ue.status = 0";
        $params = [];

        return [$fields, $from, $where, $params];
    }

    /**
     * Aplicar filtros comunes (curso, grupo, fechas)
     */
    protected function apply_common_filters(&$where, &$params) {
        // Filtro por curso
        if (!empty($this->filters['courseid'])) {
            $where .= " AND c.id = :courseid";
            $params['courseid'] = $this->filters['courseid'];
        }

        // Filtro por grupo
        if (!empty($this->filters['groupid'])) {
            $where .= " AND g.id = :groupid";
            $params['groupid'] = $this->filters['groupid'];
        }

        // Solo usuarios con actividad en el rango de fechas (si se especifica)
        if (!empty($this->filters['datefrom']) || !empty($this->filters['dateto'])) {
            $where .= " AND EXISTS (
                SELECT 1 FROM {logstore_standard_log} l 
                WHERE l.userid = u.id AND l.courseid = c.id";

            if (!empty($this->filters['datefrom'])) {
                $where .= " AND l.timecreated >= :datefrom";
                $params['datefrom'] = $this->filters['datefrom'];
            }

            if (!empty($this->filters['dateto'])) {
                $where .= " AND l.timecreated <= :dateto";
                $params['dateto'] = $this->filters['dateto'];
            }

            $where .= ")";
        }
    }

    /**
     * Procesamiento común de columnas base
     */
    public function other_cols($colname, $row) {
        static $rowcount = 0;

        switch ($colname) {
            case 'rownum':
                $rowcount++;
                return $rowcount;

            case 'groupname':
                return empty($row->groupname) ? get_string('nogroup', 'local_cadreports') : $row->groupname;

            case 'coursefullname':
                // Enlace al curso si el usuario tiene permisos
                if (has_capability('moodle/course:view', \context_course::instance($row->courseid))) {
                    $url = new \moodle_url('/course/view.php', ['id' => $row->courseid]);
                    return \html_writer::link($url, $row->coursefullname);
                }
                return $row->coursefullname;

            case 'email':
                // Solo mostrar email si el usuario tiene permisos
                if (has_capability('moodle/course:viewhiddenuserfields', $this->get_context())) {
                    return $row->email;
                }
                return '-';

            default:
                return isset($row->$colname) ? $row->$colname : '';
        }
    }

    /**
     * Obtener contexto apropiado para verificar permisos
     */
    protected function get_context() {
        return \context_system::instance();
    }

    /**
     * Formatear filas para exportación
     */
    public function format_row($row) {
        // Procesar columnas específicas para exportación
        if ($this->is_downloading()) {
            // Limpiar HTML de enlaces para exportación
            if (isset($row->coursefullname)) {
                $row->coursefullname = strip_tags($row->coursefullname);
            }

            // Formatear datos específicos del reporte
            $this->format_export_row($row);
        }

        return parent::format_row($row);
    }

    // MÉTODOS ABSTRACTOS - Cada tabla específica debe implementar

    /**
     * Configurar columnas específicas del reporte
     * @return array [columns, headers]
     */
    abstract protected function setup_specific_columns();

    /**
     * Construir SQL específico del reporte
     * @return void
     */
    abstract protected function build_specific_sql();

    /**
     * Formatear fila para exportación (específico de cada reporte)
     * @param object $row
     */
    abstract protected function format_export_row($row);

    /**
     * Configurar tabla completa (llama a métodos base y específicos)
     */
    public function setup_table() {
        // Configuración base
        $this->setup_base_config();

        // Configurar columnas específicas
        list($columns, $headers) = $this->setup_specific_columns();
        $this->define_columns($columns);
        $this->define_headers($headers);

        // Construir SQL específico
        $this->build_specific_sql();
    }
}
