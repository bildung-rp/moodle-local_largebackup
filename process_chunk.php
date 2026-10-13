<?php
// Debug-Modus aktivieren
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once(__DIR__ . '/../../config.php');
require_login();

header('Content-Type: application/json');

// Debug-Log schreiben
$debuglog = $CFG->dataroot . '/temp/largebackup_debug.log';
file_put_contents($debuglog, date('Y-m-d H:i:s') . " - Request received\n", FILE_APPEND);
file_put_contents($debuglog, "POST: " . print_r($_POST, true) . "\n", FILE_APPEND);
file_put_contents($debuglog, "FILES: " . print_r($_FILES, true) . "\n", FILE_APPEND);

try {
    $filename = required_param('filename', PARAM_FILE);
    $offset = required_param('offset', PARAM_INT);
    $totalSize = required_param('total_size', PARAM_INT);
    $contextid = required_param('contextid', PARAM_INT);
    $sessionid = required_param('sessionid', PARAM_ALPHANUM);

    file_put_contents($debuglog, "Parameters OK\n", FILE_APPEND);

    require_sesskey();
    file_put_contents($debuglog, "Sesskey OK\n", FILE_APPEND);

    $context = context::instance_by_id($contextid);
    require_capability('local/largebackup:uploadbackup', $context);
    file_put_contents($debuglog, "Capability OK\n", FILE_APPEND);

    // Temporäres Verzeichnis erstellen
    $tempdir = $CFG->dataroot . '/temp/largebackup';
    if (!file_exists($tempdir)) {
        mkdir($tempdir, 0777, true);
    }

    // Session-Daten initialisieren
    if (empty($SESSION->largebackup_files[$sessionid])) {
        $SESSION->largebackup_files[$sessionid] = [
            'userid' => $USER->id,
            'contextid' => $contextid,
            'filename' => $filename,
            'filesize' => $totalSize,
            'filepath' => $tempdir . '/' . $sessionid . '.mbz',
            'uploaded' => 0,
            'chunks' => [],
            'timecreated' => time()
        ];
        
        file_put_contents($debuglog, "Session initialized\n", FILE_APPEND);
        
        // Log-Eintrag erstellen (Upload gestartet)
        $log = new stdClass();
        $log->userid = $USER->id;
        $log->contextid = $contextid;
        $log->filename = $filename;
        $log->filesize = $totalSize;
        $log->status = 'in_progress';
        $log->sessionid = $sessionid;
        $log->timecreated = time();
        $log->timemodified = time();
        
        $DB->insert_record('local_largebackup_logs', $log);
        file_put_contents($debuglog, "Log created\n", FILE_APPEND);
    }

    $filepath = $SESSION->largebackup_files[$sessionid]['filepath'];

    // Chunk aus $_FILES lesen
    if (!isset($_FILES['chunk']) || $_FILES['chunk']['error'] !== UPLOAD_ERR_OK) {
        file_put_contents($debuglog, "No file data: " . print_r($_FILES, true) . "\n", FILE_APPEND);
        throw new moodle_exception('nofiledata', 'error');
    }

    $chunkTmpPath = $_FILES['chunk']['tmp_name'];
    $chunkSize = $_FILES['chunk']['size'];

    if ($chunkSize === 0) {
        throw new moodle_exception('nofiledata', 'error');
    }

    file_put_contents($debuglog, "Chunk size: $chunkSize bytes\n", FILE_APPEND);

    // Chunk-Daten lesen
    $chunkData = file_get_contents($chunkTmpPath);
    if ($chunkData === false) {
        throw new moodle_exception('nofiledata', 'error');
    }

    // Datei öffnen und an der richtigen Position schreiben
    $fp = fopen($filepath, 'c+b');
    if (!$fp) {
        throw new moodle_exception('couldnotcreatefile', 'error', '', $filepath);
    }

    // An die richtige Position springen
    if (fseek($fp, $offset) !== 0) {
        fclose($fp);
        throw new moodle_exception('error', 'core', '', 'Could not seek to offset ' . $offset);
    }

    // Chunk schreiben
    $bytesWritten = fwrite($fp, $chunkData);
    fclose($fp);

    if ($bytesWritten === false || $bytesWritten !== $chunkSize) {
        throw new moodle_exception('couldnotwritefile', 'error', '', $filepath);
    }

    file_put_contents($debuglog, "Written $bytesWritten bytes at offset $offset\n", FILE_APPEND);

    // Chunk als empfangen markieren
    $SESSION->largebackup_files[$sessionid]['chunks'][$offset] = $bytesWritten;
    $SESSION->largebackup_files[$sessionid]['uploaded'] = array_sum(
        $SESSION->largebackup_files[$sessionid]['chunks']
    );

    // Log aktualisieren
    $DB->execute("UPDATE {local_largebackup_logs} 
                  SET timemodified = ? 
                  WHERE sessionid = ? AND status = 'in_progress'",
                  [time(), $sessionid]);

    // Erfolgreiche Antwort
    $response = [
        'success' => true,
        'sessionid' => $sessionid,
        'offset' => $offset + $bytesWritten,
        'total' => $totalSize,
        'uploaded' => $SESSION->largebackup_files[$sessionid]['uploaded']
    ];
    
    file_put_contents($debuglog, "Success: " . json_encode($response) . "\n", FILE_APPEND);
    echo json_encode($response);

} catch (Exception $e) {
    file_put_contents($debuglog, "ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
    
    // Fehler loggen
    if (!empty($sessionid)) {
        $DB->execute("UPDATE {local_largebackup_logs} 
                      SET status = 'failed', timemodified = ? 
                      WHERE sessionid = ? AND status = 'in_progress'",
                      [time(), $sessionid]);
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
exit;
