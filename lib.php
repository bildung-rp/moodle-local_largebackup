<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Fügt den Link zum Large Backup Upload in die Navigation ein
 *
 * @param navigation_node $navigation
 * @param context|null $context
 */
function local_largebackup_add_to_navigation($navigation, $context = null) {
    global $PAGE;

    if ($context === null) {
        $context = $PAGE->context;
    }

    // Capability-Check
    if (!has_capability('local/largebackup:uploadbackup', $context)) {
        return;
    }

    // Nur in Kurs-Kontexten anzeigen (optional - entfernen für globale Verfügbarkeit)
    if ($context->contextlevel != CONTEXT_COURSE) {
        return;
    }

    // Link zur Navigation hinzufügen
    $navigation->add(
        get_string('upload_large_backup', 'local_largebackup'),
        new moodle_url('/local/largebackup/upload.php', ['contextid' => $context->id]),
        navigation_node::TYPE_SETTING,
        null,
        'largebackup',
        new pix_icon('i/backup', '', 'core')
    );
}

/**
 * Add a link to the library in the global navigation.
 *
 * @param global_navigation $navigation
 */
function local_largebackup_extend_navigation(global_navigation $navigation) {
    local_largebackup_add_to_navigation($navigation);
}

/**
 * Add a link to the library in the course category navigation.
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param context $context The context of the course category
 */
function local_largebackup_extend_navigation_category_settings($navigation, $context) {
    local_largebackup_add_to_navigation($navigation, $context);
}

/**
 * Add a link to the library in the course navigation.
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $course The course to object for the tool
 * @param context $context The context of the course
 */
function local_largebackup_extend_navigation_course($navigation, $course, $context) {
    local_largebackup_add_to_navigation($navigation, $context);
}

/**
 * Add a link to the library in the frontpage navigation.
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $course The course to object for the tool
 * @param context $context The context of the course
 */
function local_largebackup_extend_navigation_frontpage($navigation, $course, $context) {
    local_largebackup_add_to_navigation($navigation, $context);
}
