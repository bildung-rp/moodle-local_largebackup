<?php
// Standard-Strings
$string['pluginname'] = 'Große Sicherungen';
$string['pluginname_desc'] = 'Ermöglicht das Hochladen großer Moodle-Sicherungsdateien (>256MB)';
$string['upload_large_backup'] = 'Große Kurs-Sicherung hochladen';
$string['upload_description'] = 'Hier können Sie große Moodle-Sicherungsdateien (.mbz) hochladen, die größer als 256MB sind.';
$string['select_mbz_file'] = 'MBZ-Datei auswählen';
$string['start_upload'] = 'Upload starten';
$string['max_file_size_unlimited'] = 'Maximale Dateigröße: unbegrenzt (Chunked Upload)';
$string['upload_info_text'] = 'Der Upload erfolgt in kleinen Teilen (Chunks) und umgeht so die Standard-PHP-Limits.';

$string['largebackup:viewreports'] = 'Backup-Reports anzeigen';
$string['largebackup:uploadbackup'] = 'Große Backups hochladen';
$string['largebackup_reports'] = 'Large Backup - Berichte';

// Fehler- und Statusmeldungen
$string['invalid_session'] = 'Ungültige Session-ID';
$string['file_not_found'] = 'Die hochgeladene Datei wurde nicht gefunden';
$string['invalidbackupfile'] = 'Die hochgeladene Datei ist keine gültige Moodle-Sicherungsdatei (.mbz). Bitte prüfen Sie, ob es sich um eine korrekte Moodle-Sicherungsdatei handelt.';
$string['restore_failed'] = 'Wiederherstellung fehlgeschlagen: {$a}';
$string['no_uploaded_files'] = 'Keine hochgeladenen Dateien gefunden';
$string['permission_denied'] = 'Sie haben keine Berechtigung für diese Aktion';
$string['toomanyuploads'] = 'Sie haben bereits zu viele gleichzeitige Uploads. Bitte warten Sie, bis ein Upload abgeschlossen ist.';
$string['upload_in_progress'] = 'Ein Upload ist bereits in Bearbeitung. Bitte warten Sie bis dieser abgeschlossen ist.';
$string['couldnotcreatefile'] = 'Konnte die temporäre Datei nicht erstellen';
$string['couldnotwritefile'] = 'Konnte nicht in die Datei schreiben';
$string['nofiledata'] = 'Keine Dateidaten empfangen';

// Aktionsbestätigungen
$string['delete_confirm'] = 'Möchten Sie die Datei wirklich löschen?';
$string['file_deleted'] = 'Datei erfolgreich gelöscht';
$string['delete_failed'] = 'Datei konnte nicht gelöscht werden';

// Cleanup-Task
$string['taskcleanup'] = 'Temporäre Large-Backup-Dateien bereinigen';
$string['cleanup_success'] = 'Es wurden {$a} temporäre Dateien gelöscht';
$string['cleanup_nothing'] = 'Keine temporären Dateien zum Löschen gefunden';
$string['cleanup_error'] = 'Fehler beim Löschen temporärer Dateien: {$a}';

// Fortschrittsanzeige
$string['upload_progress'] = 'Upload-Fortschritt';
$string['upload_speed'] = 'Geschwindigkeit';
$string['upload_eta'] = 'Verbleibende Zeit';
$string['upload_complete'] = 'Upload abgeschlossen!';
$string['preparing_restore'] = 'Bereite Wiederherstellung vor...';

// Auswahlseite
$string['choose_action'] = 'Wählen Sie eine Aktion:';
$string['restore_as_new'] = 'Als neuen Kurs wiederherstellen';
$string['restore_as_new_desc'] = 'Erstellt einen komplett neuen Kurs aus der Sicherung.';
$string['import_into_course'] = 'In diesen Kurs importieren';
$string['import_into_course_desc'] = 'Importiert ausgewählte Inhalte in den aktuellen Kurs.';
$string['file_info'] = 'Datei-Informationen';
$string['uploaded_on'] = 'Hochgeladen am:';
$string['file_size'] = 'Dateigröße:';

// Buttons
$string['restore_button'] = 'Wiederherstellen';
$string['delete_button'] = 'Löschen';
$string['cancel_button'] = 'Abbrechen';

// Uploadlimit
$string['maxfilesize']          = 'Maximale Dateigröße (MB)';
$string['maxfilesize_desc']     = 'Maximale Dateigröße für den Upload in Megabyte. Standard: 512 MB.';
$string['maxfilesize_info']     = 'Maximale Dateigröße: {$a} MB';
$string['maxfilesize_exceeded'] = 'Die Datei überschreitet die maximale Größe von {$a} MB.';
