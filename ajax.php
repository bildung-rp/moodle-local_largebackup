<?php
require_once(__DIR__ . '/../../config.php');
require_login();

header('Content-Type: application/json');

try {
    $filename  = required_param('filename', PARAM_FILE);
    $offset    = required_param('offset', PARAM_INT);
    $totalSize = required_param('total_size', PARAM_INT);
    $contextid = required_param('contextid', PARAM_INT);
    $sessionid = required_param('sessionid', PARAM_ALPHANUM);

    require_sesskey();

    $context = context::instance_by_id($contextid);
    require_capability('local/largebackup:uploadbackup', $context);

    // Upload-Limit serverseitig prüfen
    $maxfilesizemb = (int) get_config('local_largebackup', 'maxfilesize');
    if (!$maxfilesizemb || $maxfilesizemb <= 0) {
        $maxfilesizemb = 512;
    }
    $maxfilesizebytes = $maxfilesizemb * 1024 * 1024;

    if ($totalSize > $maxfilesizebytes) {
        throw new moodle_exception(
            'error',
            'core',
            '',
            get_string('maxfilesize_exceeded', 'local_largebackup', $maxfilesizemb)
        );
    }

    // Temporäres Verzeichnis
    $tempdir = $CFG->dataroot . '/temp/largebackup';
    if (!file_exists($tempdir)) {
        mkdir($tempdir, 0777, true);
    }

    // Session initialisieren
    if (empty($SESSION->largebackup_files[$sessionid])) {
        $SESSION->largebackup_files[$sessionid] = [
            'userid'     => $USER->id,
            'contextid'  => $contextid,
            'filename'   => $filename,
            'filesize'   => $totalSize,
            'filepath'   => $tempdir . '/' . $sessionid . '.mbz',
            'uploaded'   => 0,
            'chunks'     => [],
            'timecreated' => time()
        ];

        $log = new stdClass();
        $log->userid      = $USER->id;
        $log->contextid   = $contextid;
        $log->filename    = $filename;
        $log->filesize    = $totalSize;
        $log->status      = 'in_progress';
        $log->sessionid   = $sessionid;
        $log->timecreated = time();
        $log->timemodified = time();
        $DB->insert_record('local_largebackup_logs', $log);
    }

    $filepath = $SESSION->largebackup_files[$sessionid]['filepath'];

    if (!isset($_FILES['chunk']) || $_FILES['chunk']['error'] !== UPLOAD_ERR_OK) {
        throw new moodle_exception('nofiledata', 'error');
    }

    $chunkTmpPath = $_FILES['chunk']['tmp_name'];
    $chunkSize    = $_FILES['chunk']['size'];

    if ($chunkSize === 0) {
        throw new moodle_exception('nofiledata', 'error');
    }

    $chunkData = file_get_contents($chunkTmpPath);
    if ($chunkData === false) {
        throw new moodle_exception('nofiledata', 'error');
    }

    $fp = fopen($filepath, 'c+b');
    if (!$fp) {
        throw new moodle_exception('couldnotcreatefile', 'error', '', $filepath);
    }

    fseek($fp, $offset);
    $bytesWritten = fwrite($fp, $chunkData);
    fclose($fp);

    if ($bytesWritten === false || $bytesWritten !== $chunkSize) {
        throw new moodle_exception('couldnotwritefile', 'error', '', $filepath);
    }

    $SESSION->largebackup_files[$sessionid]['chunks'][$offset] = $bytesWritten;
    $SESSION->largebackup_files[$sessionid]['uploaded'] = array_sum(
        $SESSION->largebackup_files[$sessionid]['chunks']
    );

    $DB->execute(
        "UPDATE {local_largebackup_logs} SET timemodified = ? WHERE sessionid = ? AND status = 'in_progress'",
        [time(), $sessionid]
    );

    echo json_encode([
        'success'  => true,
        'sessionid' => $sessionid,
        'offset'   => $offset + $bytesWritten,
        'total'    => $totalSize,
        'uploaded' => $SESSION->largebackup_files[$sessionid]['uploaded']
    ]);

} catch (Exception $e) {
    if (!empty($sessionid)) {
        $DB->execute(
            "UPDATE {local_largebackup_logs} SET status = 'failed', timemodified = ? WHERE sessionid = ? AND status = 'in_progress'",
            [time(), $sessionid]
        );
    }

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
exit;
