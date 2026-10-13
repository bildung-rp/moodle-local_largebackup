<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
#
    // Hauptkategorie für das Plugin
    $ADMIN->add('localplugins', new admin_category('local_largebackup', 
        get_string('pluginname', 'local_largebackup')));

    // Einstellungsseite (falls Sie später Einstellungen hinzufügen möchten)
    $settings = new admin_settingpage('local_largebackup_settings',
        get_string('settings', 'core'));

    if ($ADMIN->fulltree) {
        // Hier können später Einstellungen hinzugefügt werden
        $settings->add(new admin_setting_heading('local_largebackup_heading',
            get_string('pluginname', 'local_largebackup'),
            get_string('pluginname_desc', 'local_largebackup')));
    }

    // Upload-Limit Einstellung (in MB)
    $settings->add(new admin_setting_configtext(
        'local_largebackup/maxfilesize',
        get_string('maxfilesize', 'local_largebackup'),
        get_string('maxfilesize_desc', 'local_largebackup'),
        512,  // Default: 512 MB
        PARAM_INT
    ));

    $ADMIN->add('local_largebackup', $settings);
}
if (has_capability('local/largebackup:uploadbackup', context_system::instance())) {
    // Link zum Upload unter "Website-Administration → Kurse" hinzufügen
    $ADMIN->add('courses', new admin_externalpage(
        'local_largebackup_upload',
        get_string('upload_large_backup', 'local_largebackup'),
        new moodle_url('/local/largebackup/upload.php'),
        'local/largebackup:uploadbackup'
    ));
	
    // Report-Link unter "Website-Administration → Berichte"
    $ADMIN->add('reports', new admin_externalpage(
        'local_largebackup_report',
        get_string('largebackup_reports', 'local_largebackup'),
        new moodle_url('/local/largebackup/report.php'),
        'local/largebackup:viewreports'
    ));	
}
