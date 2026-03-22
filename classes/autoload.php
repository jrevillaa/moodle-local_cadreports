<?php
/**
 * Autoloader para local_cadreports
 * Plugin local_cadreports para Moodle 4.4
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Autoloader personalizado para el plugin
 */
class local_cadreports_autoloader {

    /**
     * Registrar el autoloader
     */
    public static function register() {
        spl_autoload_register([self::class, 'load']);
    }

    /**
     * Cargar clase automáticamente
     * @param string $classname
     */
    public static function load($classname) {
        global $CFG;

        // Solo cargar clases de nuestro namespace
        if (strpos($classname, 'local_cadreports\\') !== 0) {
            return;
        }

        // Convertir namespace a ruta
        $path = str_replace('local_cadreports\\', '', $classname);
        $path = str_replace('\\', '/', $path);
        $filepath = $CFG->dirroot . '/local/cadreports/classes/' . $path . '.php';

        if (file_exists($filepath)) {
            require_once($filepath);
        }
    }
}

// Registrar autoloader
local_cadreports_autoloader::register();
