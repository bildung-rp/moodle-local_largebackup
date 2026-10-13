<?php
// Standard strings
$string['pluginname'] = 'Large Backups';
$string['pluginname_desc'] = 'Allows uploading large Moodle backup files (>256MB)';
$string['upload_large_backup'] = 'Upload large course backup';
$string['upload_description'] = 'Here you can upload large Moodle backup files (.mbz) that are larger than 256MB.';
$string['select_mbz_file'] = 'Select MBZ file';
$string['start_upload'] = 'Start upload';
$string['max_file_size_unlimited'] = 'Maximum file size: unlimited (chunked upload)';
$string['upload_info_text'] = 'The upload is done in small chunks, bypassing standard PHP limits.';

$string['largebackup:viewreports'] = 'View backup reports';
$string['largebackup:uploadbackup'] = 'Upload large backups';
$string['largebackup_reports'] = 'Large Backup - Reports';

// Error and status messages
$string['invalid_session'] = 'Invalid session ID';
$string['file_not_found'] = 'The uploaded file was not found';
$string['invalidbackupfile'] = 'The uploaded file is not a valid Moodle backup file (.mbz). Please check if it is a correct Moodle backup file.';
$string['restore_failed'] = 'Restore failed: {$a}';
$string['no_uploaded_files'] = 'No uploaded files found';
$string['permission_denied'] = 'You do not have permission for this action';
$string['toomanyuploads'] = 'You already have too many simultaneous uploads. Please wait until an upload is completed.';
$string['upload_in_progress'] = 'An upload is already in progress. Please wait until it is completed.';
$string['couldnotcreatefile'] = 'Could not create temporary file';
$string['couldnotwritefile'] = 'Could not write to file';
$string['nofiledata'] = 'No file data received';

// Action confirmations
$string['delete_confirm'] = 'Do you really want to delete this file?';
$string['file_deleted'] = 'File successfully deleted';
$string['delete_failed'] = 'File could not be deleted';

// Cleanup task
$string['taskcleanup'] = 'Clean up temporary large backup files';
$string['cleanup_success'] = '{$a} temporary files were deleted';
$string['cleanup_nothing'] = 'No temporary files found to delete';
$string['cleanup_error'] = 'Error deleting temporary files: {$a}';

// Progress display
$string['upload_progress'] = 'Upload progress';
$string['upload_speed'] = 'Speed';
$string['upload_eta'] = 'Remaining time';
$string['upload_complete'] = 'Upload complete!';
$string['preparing_restore'] = 'Preparing restore...';

// Selection page
$string['choose_action'] = 'Choose an action:';
$string['restore_as_new'] = 'Restore as new course';
$string['restore_as_new_desc'] = 'Creates a completely new course from the backup.';
$string['import_into_course'] = 'Import into this course';
$string['import_into_course_desc'] = 'Imports selected content into the current course.';
$string['file_info'] = 'File information';
$string['uploaded_on'] = 'Uploaded on:';
$string['file_size'] = 'File size:';

// Buttons
$string['restore_button'] = 'Restore';
$string['delete_button'] = 'Delete';
$string['cancel_button'] = 'Cancel';

// Uploadlimit
$string['maxfilesize']          = 'Maximum file size (MB)';
$string['maxfilesize_desc']     = 'Maximum file size for upload in megabytes. Default: 512 MB.';
$string['maxfilesize_info']     = 'Maximum file size: {$a} MB';
$string['maxfilesize_exceeded'] = 'The file exceeds the maximum size of {$a} MB.';