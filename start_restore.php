<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

$contextid = required_param('contextid', PARAM_INT);
$sessionid = required_param('sessionid', PARAM_ALPHANUM);

require_login();
require_sesskey();

$context = context::instance_by_id($contextid);
require_capability('local/largebackup:uploadbackup', $context);

// DEBUG-Logging
$debuglog = $CFG->dataroot . '/temp/largebackup_debug.log';
file_put_contents($debuglog, date('Y-m-d H:i:s') . " - start_restore.php aufgerufen\n", FILE_APPEND);
file_put_contents($debuglog, "User: {$USER->id} ({$USER->username}), Rolle-Context: {$context->id}\n", FILE_APPEND);
file_put_contents($debuglog, "Sessionid: {$sessionid}\n", FILE_APPEND);
file_put_contents($debuglog, "Session vorhanden: " . (empty($SESSION->largebackup_files[$sessionid]) ? 'NEIN' : 'JA') . "\n", FILE_APPEND);

if (!empty($SESSION->largebackup_files[$sessionid])) {
    $fi = $SESSION->largebackup_files[$sessionid];
    file_put_contents($debuglog, "Erwarteter Pfad: {$fi['filepath']}\n", FILE_APPEND);
    file_put_contents($debuglog, "Datei existiert: " . (file_exists($fi['filepath']) ? 'JA' : 'NEIN') . "\n", FILE_APPEND);
    if (file_exists($fi['filepath'])) {
        file_put_contents($debuglog, "Dateigröße auf Platte: " . filesize($fi['filepath']) . " / erwartet: {$fi['filesize']}\n", FILE_APPEND);
    }
    file_put_contents($debuglog, "Hochgeladen laut Session: {$fi['uploaded']}\n", FILE_APPEND);
}

// Session-Daten prüfen
if (empty($SESSION->largebackup_files[$sessionid])) {
    throw new moodle_exception('invalid_session', 'local_largebackup');	
}

$fileinfo = $SESSION->largebackup_files[$sessionid];

// Sicherheitscheck
if ($fileinfo['contextid'] != $contextid || $fileinfo['userid'] != $USER->id) {
    throw new moodle_exception('permission_denied', 'local_largebackup');
}

// Prüfen ob Datei existiert
if (!file_exists($fileinfo['filepath'])) {
    throw new moodle_exception('file_not_found', 'local_largebackup');
}

// Prüfen ob Datei nicht leer ist
$actualSize = filesize($fileinfo['filepath']);
if ($actualSize === 0) {
    @unlink($fileinfo['filepath']);
    unset($SESSION->largebackup_files[$sessionid]);
    
    // Log als fehlgeschlagen markieren
    $DB->execute("UPDATE {local_largebackup_logs} 
                  SET status = 'failed', timemodified = ? 
                  WHERE sessionid = ?",
                  [time(), $sessionid]);
    
    throw new moodle_exception('emptyfile', 'error');
}

// Nur .mbz Endung prüfen
if (!preg_match('/\.mbz$/i', $fileinfo['filename'])) {
    @unlink($fileinfo['filepath']);
    unset($SESSION->largebackup_files[$sessionid]);
    
    // Log als fehlgeschlagen markieren
    $DB->execute("UPDATE {local_largebackup_logs} 
                  SET status = 'failed', timemodified = ? 
                  WHERE sessionid = ?",
                  [time(), $sessionid]);
    
    throw new moodle_exception('invalidbackupfile', 'local_largebackup');
}

// Datei in Moodle-Filepool kopieren
$usercontext = context_user::instance($USER->id);
$fs = get_file_storage();

try {
    $filerecord = [
        'contextid' => $usercontext->id,
        'component' => 'user',
        'filearea' => 'draft',
        'itemid' => file_get_unused_draft_itemid(),
        'filepath' => '/',
        'filename' => $fileinfo['filename'],
        'userid' => $USER->id,
        'timecreated' => time(),
        'timemodified' => time()
    ];

    // Datei in den Filepool kopieren
    $storedfile = $fs->create_file_from_pathname($filerecord, $fileinfo['filepath']);

    if (!$storedfile) {
        throw new Exception('Fehler beim Erstellen der Datei im Filepool');
    }

    $pathnamehash = $storedfile->get_pathnamehash();
    $contenthash = $storedfile->get_contenthash();

    // Temporäre Datei löschen
    @unlink($fileinfo['filepath']);
    unset($SESSION->largebackup_files[$sessionid]);

    // Log als erfolgreich markieren
    $DB->execute("UPDATE {local_largebackup_logs} 
                  SET status = 'success', timemodified = ? 
                  WHERE sessionid = ?",
                  [time(), $sessionid]);

    // Direkt zur Restore-Seite weiterleiten
    $url = new moodle_url('/backup/restore.php', [
        'contextid' => $context->id,
        'pathnamehash' => $pathnamehash,
        'contenthash' => $contenthash
    ]);
    
    redirect($url);

} catch (Exception $e) {
    // Bei Fehler: Temporäre Datei löschen
    if (file_exists($fileinfo['filepath'])) {
        @unlink($fileinfo['filepath']);
    }
    unset($SESSION->largebackup_files[$sessionid]);
    
    // Log als fehlgeschlagen markieren
    $DB->execute("UPDATE {local_largebackup_logs} 
                  SET status = 'failed', timemodified = ? 
                  WHERE sessionid = ?",
                  [time(), $sessionid]);
    
    debugging('Large Backup Upload Error: ' . $e->getMessage(), DEBUG_DEVELOPER);
    throw new moodle_exception('restore_failed', 'local_largebackup', '', $e->getMessage());
}
