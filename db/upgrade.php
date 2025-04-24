<?php
/**
 * Upgrade script for the yconrunner question type.
 *
 * This file is executed during an upgrade to add missing database fields:
 * - questionid
 * - contestid
 * - submissionid
 *
 * @package    qtype
 * @subpackage yconrunner
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_qtype_yconrunner_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2024083463) {
        $table = new xmldb_table('qtype_yconrunner');

        $field = new xmldb_field('questionid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'id');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('contestid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'questionid');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('submissionid', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, '', 'contestid');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2024083463, 'qtype', 'yconrunner');
    }

    return true;
}
