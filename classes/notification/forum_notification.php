<?php
/**
 * Forum notification handler
 *
 * @package    local_cadreports
 * @copyright  2024 CAD
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cadreports\notification;

defined('MOODLE_INTERNAL') || die();

/**
 * Clase para manejar notificaciones de participación en foros
 */
class forum_notification {
    
    /**
     * Enviar notificación de participación en foro
     *
     * @param object $user Usuario que participó
     * @param object $course Curso
     * @param object $forum Foro
     * @param object $post Post creado
     * @param object $discussion Discusión
     */
    public static function send_notification($user, $course, $forum, $post, $discussion) {
        global $CFG;
        
        // Obtener correos destinatarios desde configuración
        $recipients = get_config('local_cadreports', 'forum_notification_emails');
        
        if (empty($recipients)) {
            return; // No hay destinatarios configurados
        }
        
        // Parsear correos (separados por coma, punto y coma o salto de línea)
        $emails = self::parse_emails($recipients);
        
        if (empty($emails)) {
            return;
        }
        
        // Preparar datos para el correo
        $data = self::prepare_email_data($user, $course, $forum, $post, $discussion);
        
        // Enviar correo a cada destinatario
        foreach ($emails as $email) {
            self::send_email($email, $data);
        }
    }
    
    /**
     * Parsear string de correos a array
     *
     * @param string $recipients String con correos
     * @return array Array de correos válidos
     */
    private static function parse_emails($recipients) {
        // Separar por coma, punto y coma o salto de línea
        $emails = preg_split('/[,;\n\r]+/', $recipients);
        
        // Limpiar y validar
        $valid_emails = [];
        foreach ($emails as $email) {
            $email = trim($email);
            if (validate_email($email)) {
                $valid_emails[] = $email;
            }
        }
        
        return $valid_emails;
    }
    
    /**
     * Preparar datos para el correo
     *
     * @param object $user Usuario
     * @param object $course Curso
     * @param object $forum Foro
     * @param object $post Post
     * @param object $discussion Discusión
     * @return array Datos formateados
     */
    private static function prepare_email_data($user, $course, $forum, $post, $discussion) {
        global $CFG;
        
        // Link directo al post
        $posturl = new \moodle_url('/mod/forum/discuss.php', [
            'd' => $discussion->id
        ]);
        $posturl->set_anchor('p' . $post->id);
        
        // Link al foro
        $forumurl = new \moodle_url('/mod/forum/view.php', [
            'f' => $forum->id
        ]);
        
        // Tipo de participación
        $participation_type = ($post->parent == 0) 
            ? get_string('new_discussion', 'local_cadreports')
            : get_string('reply_to_discussion', 'local_cadreports');
        
        return [
            'student_name' => fullname($user),
            'student_email' => $user->email,
            'student_username' => $user->username,
            'course_name' => format_string($course->fullname),
            'forum_name' => format_string($forum->name),
            'discussion_name' => format_string($discussion->name),
            'participation_type' => $participation_type,
            'post_subject' => format_string($post->subject),
            'post_message' => self::get_message_excerpt($post->message),
            'post_date' => userdate($post->created, get_string('strftimedatetimeshort')),
            'post_url' => $posturl->out(false),
            'forum_url' => $forumurl->out(false),
            'site_name' => format_string($CFG->fullname ?? 'Moodle'),
        ];
    }
    
    /**
     * Obtener extracto del mensaje (primeros 200 caracteres)
     *
     * @param string $message Mensaje completo
     * @return string Extracto
     */
    private static function get_message_excerpt($message) {
        $text = strip_tags($message);
        $text = trim($text);
        
        if (strlen($text) > 200) {
            $text = substr($text, 0, 200) . '...';
        }
        
        return $text;
    }
    
    /**
     * Enviar correo
     *
     * @param string $to_email Correo destinatario
     * @param array $data Datos del correo
     */
    private static function send_email($to_email, $data) {
        global $CFG;
        
        // Crear usuario "from" (noreply)
        $from = \core_user::get_noreply_user();
        
        // Crear usuario "to" temporal
        $to = new \stdClass();
        $to->email = $to_email;
        $to->firstname = '';
        $to->lastname = '';
        $to->maildisplay = true;
        $to->mailformat = 1; // HTML
        $to->id = -1;
        $to->firstnamephonetic = '';
        $to->lastnamephonetic = '';
        $to->middlename = '';
        $to->alternatename = '';
        
        // Asunto del correo
        $subject = get_string('forum_notification_subject', 'local_cadreports', [
            'student' => $data['student_name'],
            'forum' => $data['forum_name']
        ]);
        
        // Cuerpo del correo (HTML)
        $messagehtml = self::get_email_html($data);
        
        // Cuerpo del correo (texto plano)
        $messagetext = self::get_email_text($data);
        
        // Enviar correo
        email_to_user($to, $from, $subject, $messagetext, $messagehtml);
    }
    
    /**
     * Generar HTML del correo
     *
     * @param array $data Datos del correo
     * @return string HTML
     */
    private static function get_email_html($data) {
        $html = '
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #0066cc; color: white; padding: 15px; border-radius: 5px 5px 0 0; }
                .content { background-color: #f9f9f9; padding: 20px; border: 1px solid #ddd; }
                .info-row { margin: 10px 0; }
                .label { font-weight: bold; color: #555; }
                .value { color: #000; }
                .button { display: inline-block; padding: 10px 20px; background-color: #0066cc; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0; }
                .excerpt { background-color: #fff; padding: 15px; border-left: 4px solid #0066cc; margin: 15px 0; font-style: italic; }
                .footer { text-align: center; color: #777; font-size: 12px; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>🔔 Nueva Participación en Foro</h2>
                </div>
                <div class="content">
                    <div class="info-row">
                        <span class="label">Alumno:</span>
                        <span class="value">' . htmlspecialchars($data['student_name']) . ' (' . htmlspecialchars($data['student_username']) . ')</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Curso:</span>
                        <span class="value">' . htmlspecialchars($data['course_name']) . '</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Foro:</span>
                        <span class="value">' . htmlspecialchars($data['forum_name']) . '</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Tipo:</span>
                        <span class="value">' . htmlspecialchars($data['participation_type']) . '</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Discusión:</span>
                        <span class="value">' . htmlspecialchars($data['discussion_name']) . '</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Fecha:</span>
                        <span class="value">' . htmlspecialchars($data['post_date']) . '</span>
                    </div>
                    
                    <div class="excerpt">
                        <strong>Extracto del mensaje:</strong><br>
                        ' . htmlspecialchars($data['post_message']) . '
                    </div>
                    
                    <div style="text-align: center;">
                        <a href="' . $data['post_url'] . '" class="button">Ver Participación</a>
                    </div>
                </div>
                <div class="footer">
                    <p>Este es un correo automático de ' . htmlspecialchars($data['site_name']) . '</p>
                    <p>No responder a este correo</p>
                </div>
            </div>
        </body>
        </html>';
        
        return $html;
    }
    
    /**
     * Generar texto plano del correo
     *
     * @param array $data Datos del correo
     * @return string Texto plano
     */
    private static function get_email_text($data) {
        $text = "NUEVA PARTICIPACIÓN EN FORO\n";
        $text .= "================================\n\n";
        $text .= "Alumno: {$data['student_name']} ({$data['student_username']})\n";
        $text .= "Curso: {$data['course_name']}\n";
        $text .= "Foro: {$data['forum_name']}\n";
        $text .= "Tipo: {$data['participation_type']}\n";
        $text .= "Discusión: {$data['discussion_name']}\n";
        $text .= "Fecha: {$data['post_date']}\n\n";
        $text .= "Extracto del mensaje:\n";
        $text .= "{$data['post_message']}\n\n";
        $text .= "Ver participación: {$data['post_url']}\n\n";
        $text .= "---\n";
        $text .= "Este es un correo automático de {$data['site_name']}\n";
        
        return $text;
    }
}
