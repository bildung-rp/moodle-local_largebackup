<?php
namespace local_largebackup\task;

class cleanup_temp_files extends \core\task\scheduled_task {

    public function get_name() {
        return get_string('taskcleanup', 'local_largebackup');
    }

    public function execute() {
        global $CFG;

        $tempdir = $CFG->dataroot . '/temp/largebackup';
        if (!file_exists($tempdir)) {
            return;
        }

        $files = glob($tempdir . '/*.mbz');
        $deleted = 0;
        $now = time();

        foreach ($files as $file) {
            // Dateien älter als 24 Stunden löschen
            if (filemtime($file) < $now - 86400) {
                if (unlink($file)) {
                    $deleted++;
                }
            }
        }

        if ($deleted > 0) {
            mtrace("Large Backup Cleanup: Gelöschte $deleted temporäre Dateien");
        } else {
            mtrace("Large Backup Cleanup: Keine veralteten Dateien gefunden");
        }
    }
}
