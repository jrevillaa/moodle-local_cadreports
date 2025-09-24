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

    /**
     * Procesar filtros desde formulario o URL
     */
    protected function process_filters() {
        // Función helper para procesar arrays de IDs
        $process_array_param = function($name, $form_data) {
            // Primero intentar desde formulario
            if ($form_data && isset($form_data->$name) && is_array($form_data->$name)) {
                return $form_data->$name;
            }

            // Luego desde URL (puede venir como string separado por comas)
            $url_value = optional_param($name, '', PARAM_RAW);
            if (!empty($url_value)) {
                if (is_array($url_value)) {
                    return array_map('intval', $url_value);
                } else {
                    // Convertir string separado por comas a array
                    return array_map('intval', explode(',', $url_value));
                }
            }

            return [];
        };

        // Obtener datos del formulario
        $form_data = $this->get_form()->get_data();

        if ($form_data) {
            // Usar datos del formulario (ya procesados y validados)
            $this->filters = [
                'courseids' => isset($form_data->courseids) ? $form_data->courseids : [],  // ✅ ARRAY
                'groupids' => isset($form_data->groupids) ? $form_data->groupids : [],    // ✅ ARRAY
                'datefrom' => isset($form_data->datefrom) ? $form_data->datefrom : 0,
                'dateto' => isset($form_data->dateto) ? $form_data->dateto : 0,
                'download' => optional_param('download', '', PARAM_ALPHA)
            ];
        } else {
            // Fallback a parámetros URL
            $this->filters = [
                'courseids' => $process_array_param('courseids', null),  // ✅ ARRAY
                'groupids' => $process_array_param('groupids', null),    // ✅ ARRAY
                'datefrom' => optional_param('datefrom', 0, PARAM_INT),
                'dateto' => optional_param('dateto', 0, PARAM_INT),
                'download' => optional_param('download', '', PARAM_ALPHA)
            ];
        }

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
            return !empty($form_data->courseids) || !empty($form_data->datefrom) || !empty($form_data->dateto);
        }

        return !empty($this->filters['courseids']) || !empty($this->filters['datefrom']) || !empty($this->filters['dateto']);
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
