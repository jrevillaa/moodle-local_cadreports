<?php
/**
 * Utilidad reutilizable para cálculo de tiempo de dedicación
 * Plugin local_cadreports para Moodle 4.4
 */

namespace local_cadreports\utils;

defined('MOODLE_INTERNAL') || die();

/**
 * Calculadora de tiempo de dedicación basada en logs
 */
class time_calculator {

    /** @var int Tiempo máximo entre eventos para considerar misma sesión (30 minutos) */
    const SESSION_GAP = 1800;

    /** @var array Eventos considerados para cálculo de tiempo */
    const VALID_ACTIONS = ['viewed', 'submitted', 'updated', 'created', 'attempted'];

    /**
     * Calcular tiempo total de dedicación de un usuario en un curso
     *
     * @param int $userid ID del usuario
     * @param int $courseid ID del curso
     * @param int $datefrom Timestamp fecha desde (opcional)
     * @param int $dateto Timestamp fecha hasta (opcional)
     * @return int Tiempo en segundos
     */
    public static function calculate_user_course_time($userid, $courseid, $datefrom = 0, $dateto = 0) {
        global $DB;

        $params = [
            'userid' => $userid,
            'courseid' => $courseid
        ];

        $timewhere = "";
        if ($datefrom > 0) {
            $timewhere .= " AND timecreated >= :datefrom";
            $params['datefrom'] = $datefrom;
        }
        if ($dateto > 0) {
            $timewhere .= " AND timecreated <= :dateto";
            $params['dateto'] = $dateto;
        }

        $actions = "'" . implode("','", self::VALID_ACTIONS) . "'";

        // Obtener eventos ordenados por tiempo
        $sql = "SELECT timecreated
                FROM {logstore_standard_log} 
                WHERE userid = :userid 
                AND courseid = :courseid 
                AND action IN ($actions)
                $timewhere
                ORDER BY timecreated ASC";

        $events = $DB->get_records_sql($sql, $params);

        return self::process_events_to_time($events);
    }

    /**
     * Procesar array de eventos y calcular tiempo total
     *
     * @param array $events Array de objetos con timecreated
     * @return int Tiempo total en segundos
     */
    private static function process_events_to_time($events) {
        if (empty($events)) {
            return 0;
        }

        $events = array_values($events);
        $total_time = 0;
        $session_start = null;
        $session_end = null;

        foreach ($events as $event) {
            if ($session_start === null) {
                // Iniciar nueva sesión
                $session_start = $event->timecreated;
                $session_end = $event->timecreated;
            } else {
                $gap = $event->timecreated - $session_end;

                if ($gap <= self::SESSION_GAP) {
                    // Continuar sesión actual
                    $session_end = $event->timecreated;
                } else {
                    // Gap muy largo, cerrar sesión anterior e iniciar nueva
                    $session_duration = $session_end - $session_start;
                    $total_time += $session_duration;

                    $session_start = $event->timecreated;
                    $session_end = $event->timecreated;
                }
            }
        }

        // Añadir última sesión
        if ($session_start !== null) {
            $session_duration = $session_end - $session_start;
            $total_time += $session_duration;
        }

        return $total_time;
    }

    /**
     * Formatear tiempo en formato legible (días, horas, minutos, segundos)
     *
     * @param int $seconds Tiempo en segundos
     * @param bool $detailed Si usar formato detallado para exportación
     * @return string Tiempo formateado
     */
    public static function format_duration($seconds, $detailed = false) {
        if ($seconds <= 0) {
            return '0s';
        }

        $days = intval($seconds / 86400);
        $seconds = $seconds % 86400;

        $hours = intval($seconds / 3600);
        $seconds = $seconds % 3600;

        $minutes = intval($seconds / 60);
        $seconds = $seconds % 60;

        if ($detailed) {
            // Formato detallado para exportación
            return sprintf('%d días, %d horas, %d minutos, %d segundos',
                $days, $hours, $minutes, $seconds);
        } else {
            // Formato compacto para visualización
            $parts = [];
            if ($days > 0) $parts[] = $days . 'd';
            if ($hours > 0) $parts[] = $hours . 'h';
            if ($minutes > 0) $parts[] = $minutes . 'm';
            if ($seconds > 0 || empty($parts)) $parts[] = $seconds . 's';

            return implode(' ', $parts);
        }
    }
}
