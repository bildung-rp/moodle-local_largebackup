<?php
defined('MOODLE_INTERNAL') || die();

function xmldb_local_largebackup_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2024051806) {
        // Define table local_largebackup_logs to be created.
        $table = new xmldb_table('local_largebackup_logs');

        // Adding fields to table local_largebackup_logs.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('contextid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('filename', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('filesize', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('status', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('sessionid', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_largebackup_logs.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);

        // Adding indexes to table local_largebackup_logs.
        $table->add_index('contextid', XMLDB_INDEX_NOTUNIQUE, ['contextid']);
        $table->add_index('sessionid', XMLDB_INDEX_NOTUNIQUE, ['sessionid']);
        $table->add_index('status', XMLDB_INDEX_NOTUNIQUE, ['status']);
        $table->add_index('timemodified', XMLDB_INDEX_NOTUNIQUE, ['timemodified']);

        // Conditionally launch create table for local_largebackup_logs.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Largebackup savepoint reached.
        upgrade_plugin_savepoint(true, 2024051806, 'local', 'largebackup');
    }
	
	if ($oldversion < 2026092400) {
        // Sicherstellen, dass die Log-Tabelle auch in älteren bzw.
        // unvollständig installierten Instanzen vorhanden ist.
        $table = new xmldb_table('local_largebackup_logs');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null,
            XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null,
            XMLDB_NOTNULL, null, null);
        $table->add_field('contextid', XMLDB_TYPE_INTEGER, '10', null,
            XMLDB_NOTNULL, null, null);
        $table->add_field('filename', XMLDB_TYPE_CHAR, '255', null,
            XMLDB_NOTNULL, null, null);
        $table->add_field('filesize', XMLDB_TYPE_INTEGER, '20', null,
            XMLDB_NOTNULL, null, null);
        $table->add_field('status', XMLDB_TYPE_CHAR, '20', null,
            XMLDB_NOTNULL, null, null);
        $table->add_field('sessionid', XMLDB_TYPE_CHAR, '50', null,
            XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null,
            XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null,
            XMLDB_NOTNULL, null, null);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);

        $table->add_index('contextid', XMLDB_INDEX_NOTUNIQUE, ['contextid']);
        $table->add_index('sessionid', XMLDB_INDEX_NOTUNIQUE, ['sessionid']);
        $table->add_index('status', XMLDB_INDEX_NOTUNIQUE, ['status']);
        $table->add_index('timemodified', XMLDB_INDEX_NOTUNIQUE, ['timemodified']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2026092400, 'local', 'largebackup');
    }

    return true;
}
