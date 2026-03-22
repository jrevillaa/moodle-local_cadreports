<?php
/**
 * Clase base abstracta para todos los reportes CAD
 * Plugin local_cadreports para Moodle 4.4
 */

namespace local_cadreports\base;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/tablelib.php');

/**
 * Clase abstracta base para todos los reportes
 */
abstract class report_base {

    /** @var string Identificador único del reporte */
    protected $reporttype;

    /** @var \moodleform Formulario de filtros */
    protected $form;

    /** @var array Parámetros de filtros */
    protected $filters = [];

    /** @var \context_system Contexto del sistema */
    protected $context;

    /**
     * Constructor
     * @param string $reporttype Tipo de reporte
     */
    public function __construct($reporttype) {
        global $PAGE;

        $this->reporttype = $reporttype;
        $this->context = \context_system::instance();

        // Configurar página base
        $this->setup_page();

        // Verificar permisos
        $this->check_permissions();

        // Procesar filtros
        $this->process_filters();
    }

    /**
     * Configurar la página base
     */
    protected function setup_page() {
        global $PAGE;

        require_login();
        admin_externalpage_setup('local_cadreports_' . $this->reporttype);

        $PAGE->set_url('/local/cadreports/reports/' . $this->reporttype . '.php');
        $PAGE->set_title(get_string($this->reporttype . 'report', 'local_cadreports'));
        $PAGE->set_heading(get_string($this->reporttype . 'report', 'local_cadreports'));
    }

    /**
     * Verificar permisos del usuario
     */
    protected function check_permissions() {
        require_capability('local/cadreports:view', $this->context);
    }

    protected function process_filters() {
        global $CFG;
        
        // Obtener datos del formulario primero
        $form_data = $this->get_form()->get_data();

        // Inicializar filtros con valores por defecto
        $this->filters = [
            'mode' => 'bycourse',
            'courseids' => [],
            'groupids' => [],
            'userquery' => [],
            'datefrom' => 0,
            'dateto' => 0,
            'download' => ''
        ];

        if ($form_data) {
            // Usar datos del formulario (ya procesados y validados)
            if (isset($form_data->mode)) {
                $this->filters['mode'] = $form_data->mode;
            }
            if (isset($form_data->courseids)) {
                $this->filters['courseids'] = is_array($form_data->courseids) ? $form_data->courseids : [];
            }
            if (isset($form_data->groupids)) {
                $this->filters['groupids'] = is_array($form_data->groupids) ? $form_data->groupids : [];
            }
            if (isset($form_data->userquery)) {
                $userquery = $form_data->userquery;
                // Convertir a array si no lo es
                if (!is_array($userquery)) {
                    $userquery = empty($userquery) ? [] : [$userquery];
                }
                
                // Filtrar el marcador de Moodle y valores vacíos
                $userquery = array_filter($userquery, function($value) {
                    return !empty($value) && $value !== '_qf__force_multiselect_submission';
                });
                
                $this->filters['userquery'] = array_values($userquery); // Reindexar array
            }
            if (isset($form_data->datefrom)) {
                $this->filters['datefrom'] = $form_data->datefrom;
            }
            if (isset($form_data->dateto)) {
                $this->filters['dateto'] = $form_data->dateto;
            }
        } else if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // Solo usar optional_param si NO es POST (para evitar conflictos con arrays)
            // Esto maneja el caso de parámetros en URL (GET)
            $this->filters['mode'] = optional_param('mode', 'bycourse', PARAM_ALPHA);
            $this->filters['courseids'] = optional_param_array('courseids', [], PARAM_INT);
            $this->filters['groupids'] = optional_param_array('groupids', [], PARAM_INT);
            $this->filters['userquery'] = optional_param_array('userquery', [], PARAM_TEXT);
            $this->filters['datefrom'] = optional_param('datefrom', 0, PARAM_INT);
            $this->filters['dateto'] = optional_param('dateto', 0, PARAM_INT);
        }
        // Si es POST pero no hay form_data, mantener valores por defecto

        // Download siempre viene de parámetro GET (para exportación)
        $this->filters['download'] = isset($_GET['download']) ? clean_param($_GET['download'], PARAM_ALPHA) : '';

        // Permitir filtros adicionales específicos del reporte
        $additional_filters = $this->get_additional_filters();
        if (!empty($additional_filters)) {
            $this->filters = array_merge($this->filters, $additional_filters);
        }
    }

    /**
     * Verificar si hay filtros aplicados
     */
    protected function has_filters_applied() {
        $form_data = $this->get_form()->get_data();

        if ($form_data) {
            return !empty($form_data->courseids) || !empty($form_data->groupids) || !empty($form_data->userquery) || !empty($form_data->datefrom) || !empty($form_data->dateto);
        }

        return !empty($this->filters['courseids']) || !empty($this->filters['groupids']) || !empty($this->filters['userquery']) || !empty($this->filters['datefrom']) || !empty($this->filters['dateto']);
    }




    /**
     * Renderizar el reporte completo
     */
    public function render() {
        global $OUTPUT;

        // Si hay descarga, procesar sin mostrar página
        if (!empty($this->filters['download'])) {
            $this->handle_download();
            return;
        }

        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string($this->reporttype . 'report', 'local_cadreports'));

        // Mostrar formulario de filtros
        $this->render_filters_form();

        // Mostrar tabla si hay filtros aplicados
        if ($this->has_filters_applied()) {
            $this->render_table();
        } else {
            echo $OUTPUT->notification(get_string('selectfilters', 'local_cadreports'), 'info');
        }

        echo $OUTPUT->footer();
    }

    /**
     * Renderizar formulario de filtros
     */
    protected function render_filters_form() {
        echo \html_writer::start_div('cadreports-filters');
        $this->get_form()->display();
        echo \html_writer::end_div();
    }

    /**
     * Renderizar tabla principal - Usa download nativo de Moodle
     */
    protected function render_table() {
        $table = $this->get_table();
        $table->setup_table();
        $table->out(25, true); // 25 registros por página - botones de descarga automáticos
    }

    /**
     * Manejar descarga de archivos - Usa API nativa de table_sql
     */
    protected function handle_download() {
        $table = $this->get_table();
        $table->is_downloading(
            $this->filters['download'],
            $this->get_download_filename(),
            $this->get_download_title()
        );
        $table->setup_table();
        $table->out(0, false);
        die();
    }


    /**
     * Obtener nombre del archivo de descarga
     */
    protected function get_download_filename() {
        return 'reporte_' . $this->reporttype . '_' . userdate(time(), '%Y-%m-%d');
    }

    /**
     * Obtener título de descarga
     */
    protected function get_download_title() {
        return get_string($this->reporttype . 'report', 'local_cadreports') . ' - CAD';
    }

    // MÉTODOS ABSTRACTOS - Deben implementarse en cada reporte específico

    /**
     * Obtener filtros adicionales específicos del reporte
     * @return array
     */
    abstract protected function get_additional_filters();

    /**
     * Obtener formulario de filtros específico
     * @return \moodleform
     */
    abstract protected function get_form();

    /**
     * Obtener tabla específica del reporte
     * @return table_base
     */
    abstract protected function get_table();
}
