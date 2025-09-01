<?php
defined('MOODLE_INTERNAL') || die();

abstract class cadreports_base {

    protected $courseid;
    protected $groupid;
    protected $userid;
    protected $datefrom;
    protected $dateto;

    public function __construct($params = []) {
        $this->courseid = isset($params['courseid']) ? $params['courseid'] : 0;
        $this->groupid = isset($params['groupid']) ? $params['groupid'] : 0;
        $this->userid = isset($params['userid']) ? $params['userid'] : 0;
        $this->datefrom = isset($params['datefrom']) ? $params['datefrom'] : '';
        $this->dateto = isset($params['dateto']) ? $params['dateto'] : '';
    }

    /**
     * Obtener opciones de cursos para filtros
     */
    protected function get_course_options() {
        $courses = get_courses();
        $options = [0 => get_string('allcourses', 'local_cadreports')];

        foreach ($courses as $course) {
            if ($course->id > 1) {
                $options[$course->id] = format_string($course->fullname);
            }
        }

        return $options;
    }

    /**
     * Obtener opciones de grupos para un curso
     */
    protected function get_group_options($courseid = null) {
        $courseid = $courseid ?: $this->courseid;
        $options = [0 => get_string('allgroups', 'local_cadreports')];

        if ($courseid) {
            $groups = groups_get_all_groups($courseid);
            foreach ($groups as $group) {
                $options[$group->id] = format_string($group->name);
            }
        }

        return $options;
    }

    /**
     * Obtener opciones de usuarios para un curso/grupo
     */
    protected function get_user_options($courseid = null, $groupid = null) {
        $courseid = $courseid ?: $this->courseid;
        $groupid = $groupid ?: $this->groupid;
        $options = [0 => get_string('allusers', 'local_cadreports')];

        if ($courseid) {
            $context = context_course::instance($courseid);
            $users = get_enrolled_users($context, '', $groupid, 'u.id, u.firstname, u.lastname',
                'u.lastname, u.firstname');
            foreach ($users as $user) {
                $options[$user->id] = fullname($user);
            }
        }

        return $options;
    }

    /**
     * Renderizar formulario de filtros
     */
    protected function render_filters_form($baseurl) {
        global $OUTPUT;

        $formhtml = '';

        // Card container
        $formhtml .= html_writer::start_tag('div', ['class' => 'cadreports-filters card mb-4']);
        $formhtml .= html_writer::start_tag('div', ['class' => 'card-header']);
        $formhtml .= html_writer::tag('h5', get_string('filters', 'local_cadreports'), ['class' => 'mb-0']);
        $formhtml .= html_writer::end_tag('div');

        $formhtml .= html_writer::start_tag('div', ['class' => 'card-body']);
        $formhtml .= html_writer::start_tag('form', ['method' => 'GET', 'class' => 'needs-validation']);
        $formhtml .= html_writer::start_tag('div', ['class' => 'row g-3']);

        // Filtro de cursos
        $formhtml .= html_writer::start_tag('div', ['class' => 'col-md-4']);
        $formhtml .= html_writer::tag('label', get_string('course'), ['for' => 'courseid', 'class' => 'form-label']);
        $formhtml .= html_writer::select($this->get_course_options(), 'courseid', $this->courseid, false,
            ['class' => 'form-select']);
        $formhtml .= html_writer::end_tag('div');

        // Filtro de grupos
        $formhtml .= html_writer::start_tag('div', ['class' => 'col-md-4']);
        $formhtml .= html_writer::tag('label', get_string('group'), ['for' => 'groupid', 'class' => 'form-label']);
        $formhtml .= html_writer::select($this->get_group_options(), 'groupid', $this->groupid, false,
            ['class' => 'form-select']);
        $formhtml .= html_writer::end_tag('div');

        // Fechas
        $formhtml .= html_writer::start_tag('div', ['class' => 'col-md-2']);
        $formhtml .= html_writer::tag('label', get_string('from'), ['class' => 'form-label']);
        $formhtml .= html_writer::empty_tag('input', [
            'type' => 'date',
            'name' => 'datefrom',
            'value' => $this->datefrom,
            'class' => 'form-control'
        ]);
        $formhtml .= html_writer::end_tag('div');

        $formhtml .= html_writer::start_tag('div', ['class' => 'col-md-2']);
        $formhtml .= html_writer::tag('label', get_string('to'), ['class' => 'form-label']);
        $formhtml .= html_writer::empty_tag('input', [
            'type' => 'date',
            'name' => 'dateto',
            'value' => $this->dateto,
            'class' => 'form-control'
        ]);
        $formhtml .= html_writer::end_tag('div');

        $formhtml .= html_writer::end_tag('div'); // row

        // Botones
        $formhtml .= html_writer::start_tag('div', ['class' => 'mt-3 d-flex gap-2']);
        $formhtml .= html_writer::empty_tag('input', [
            'type' => 'submit',
            'value' => get_string('generate_report', 'local_cadreports'),
            'class' => 'btn btn-primary'
        ]);
        $formhtml .= html_writer::link($baseurl, get_string('clear_filters', 'local_cadreports'),
            ['class' => 'btn btn-outline-secondary']);
        $formhtml .= html_writer::end_tag('div');

        $formhtml .= html_writer::end_tag('form');
        $formhtml .= html_writer::end_tag('div'); // card-body
        $formhtml .= html_writer::end_tag('div'); // card

        return $formhtml;
    }

    /**
     * Método abstracto para obtener datos del reporte
     */
    abstract protected function get_report_data();

    /**
     * Método abstracto para configurar tabla
     */
    abstract protected function setup_table();
}
