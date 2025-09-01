<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/tablelib.php');

class access_report_table extends table_sql {

    public function __construct($uniqueid) {
        parent::__construct($uniqueid);

        // Configurar columnas
        $columns = ['coursename', 'groupname', 'lastname', 'firstname', 'idnumber', 'access_time', 'duration'];
        $headers = [
            get_string('course'),
            'Grupo',
            get_string('lastname'),
            get_string('firstname'),
            'DNI',
            'Fecha/Hora',
            'Tiempo (min)'
        ];

        $this->define_columns($columns);
        $this->define_headers($headers);

        // Configurar propiedades
        $this->sortable(true, 'coursename', SORT_ASC);
        $this->collapsible(false);
        $this->pageable(true);
        $this->is_downloadable(true);
        $this->show_download_buttons_at([TABLE_P_BOTTOM]);
        $this->set_attribute('class', 'table table-striped table-hover cadreports-table');
    }

    /**
     * Formatear columna de duración
     */
    public function col_duration($row) {
        if ($this->is_downloading()) {
            return round($row->duration / 60, 2);
        }

        $minutes = round($row->duration / 60, 2);
        $class = '';
        if ($minutes < 5) {
            $class = 'badge bg-danger';
        } elseif ($minutes < 15) {
            $class = 'badge bg-warning';
        } else {
            $class = 'badge bg-success';
        }

        return html_writer::tag('span', $minutes . ' min', ['class' => $class]);
    }

    /**
     * Formatear columna de fecha/hora
     */
    public function col_access_time($row) {
        if ($this->is_downloading()) {
            return $row->access_time;
        }

        $timestamp = strtotime($row->access_time);
        return html_writer::tag('span',
            userdate($timestamp, get_string('strftimedatetimeshort')),
            ['class' => 'text-nowrap']
        );
    }

    /**
     * Formatear columna de nombre completo
     */
    public function col_lastname($row) {
        if ($this->is_downloading()) {
            return $row->lastname;
        }

        return html_writer::tag('strong', $row->lastname);
    }

    /**
     * Formatear columna de DNI
     */
    public function col_idnumber($row) {
        if (empty($row->idnumber)) {
            return $this->is_downloading() ? '' : html_writer::tag('em', 'Sin DNI', ['class' => 'text-muted']);
        }
        return $row->idnumber;
    }
}
