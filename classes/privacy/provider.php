<?php
namespace local_largebackup\privacy;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy Subsystem implementation for local_largebackup.
 */
class provider implements \core_privacy\local\metadata\null_provider {
    
    /**
     * Get the language string identifier with the component's language
     * file to explain why this plugin stores no data.
     *
     * @return string
     */
    public static function get_reason() : string {
        return 'privacy:metadata';
    }
}
