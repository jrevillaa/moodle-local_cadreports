<?php
/**
 * Forum event observer
 *
 * @package    local_cadreports
 * @copyright  2024 CAD
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cadreports\observer;

use local_cadreports\notification\forum_notification;

defined('MOODLE_INTERNAL') || die();

/**
 * Observer para eventos de foros
 */
class forum_observer {
    
    /**
     * Observer para evento post_created
     * Se ejecuta cuando un usuario crea un post en un foro
     *
     * @param \mod_forum\event\post_created $event
     */
    public static function post_created(\mod_forum\event\post_created $event) {
        global $DB;
        
        // Verificar si las notificaciones están habilitadas
        if (!get_config('local_cadreports', 'forum_notifications_enabled')) {
            return;
        }
        
        // Obtener datos del evento
        $postid = $event->objectid;
        $userid = $event->userid;
        $courseid = $event->courseid;
        $forumid = $event->other['forumid'];
        $discussionid = $event->other['discussionid'];
        
        // Obtener información completa
        $user = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);
        $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
        $forum = $DB->get_record('forum', ['id' => $forumid], '*', MUST_EXIST);
        $post = $DB->get_record('forum_posts', ['id' => $postid], '*', MUST_EXIST);
        $discussion = $DB->get_record('forum_discussions', ['id' => $discussionid], '*', MUST_EXIST);
        
        // Verificar si el usuario es alumno (excluir profesores/administradores)
        if (self::is_student($userid, $courseid)) {
            // Enviar notificación
            forum_notification::send_notification($user, $course, $forum, $post, $discussion);
        }
    }
    
    /**
     * Verificar si el usuario es alumno (no profesor/admin)
     *
     * @param int $userid ID del usuario
     * @param int $courseid ID del curso
     * @return bool True si es alumno, false si es staff
     */
    private static function is_student($userid, $courseid) {
        global $DB;
        
        $context = \context_course::instance($courseid);
        
        // Roles de staff que NO deben generar notificaciones
        $staff_roles = ['manager', 'coursecreator', 'editingteacher', 'teacher'];
        
        foreach ($staff_roles as $rolename) {
            $role = $DB->get_record('role', ['shortname' => $rolename]);
            if ($role && user_has_role_assignment($userid, $role->id, $context->id)) {
                return false;
            }
        }
        
        return true;
    }
}
