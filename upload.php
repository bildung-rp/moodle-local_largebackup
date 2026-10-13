<?php
require_once(__DIR__ . '/../../config.php');

$contextid = optional_param('contextid', 0, PARAM_INT);

require_login();

if ($contextid) {
    try {
        $context = context::instance_by_id($contextid);
    } catch (Exception $e) {
        print_error('invalidcontext');
    }
} else {
    $context = context_system::instance();
}

require_capability('local/largebackup:uploadbackup', $context);

// Upload-Limit aus Einstellungen lesen (in MB)
$maxfilesizemb = (int) get_config('local_largebackup', 'maxfilesize');
if (!$maxfilesizemb || $maxfilesizemb <= 0) {
    $maxfilesizemb = 512; // Fallback auf 512 MB
}
$maxfilesizebytes = $maxfilesizemb * 1024 * 1024;

$PAGE->set_url(new moodle_url('/local/largebackup/upload.php', ['contextid' => $context->id]));
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('upload_large_backup', 'local_largebackup'));
$PAGE->set_heading(get_string('upload_large_backup', 'local_largebackup'));

print_r($PAGE->requires->js_call_amd);

$ret = $PAGE->requires->js_call_amd('local_largebackup/uploader', 'init', [
        $context->id,
        $maxfilesizebytes  // Limit an JavaScript übergeben
]);
print_r($ret);

echo $OUTPUT->header();
?>

    <div class="largebackup-upload-container">

        <div class="card">
            <div class="card-body">
                <h3><?php echo get_string('select_mbz_file', 'local_largebackup'); ?></h3>

                <p class="text-muted">
                    <?php echo get_string('maxfilesize_info', 'local_largebackup', $maxfilesizemb); ?>
                </p>

                <div class="form-group">
                    <input type="file" id="largebackup-file" accept=".mbz" class="form-control-file">
                </div>

                <div id="largebackup-file-info" style="display: none;" class="alert alert-secondary mt-3">
                    <div id="largebackup-file-details"></div>
                </div>

                <button id="largebackup-start-upload" class="btn btn-primary btn-lg mt-3" disabled>
                    <?php echo get_string('start_upload', 'local_largebackup'); ?>
                </button>
            </div>
        </div>

        <div id="largebackup-progress" class="mt-4" style="display: none;">
            <h4><?php echo get_string('upload_progress', 'local_largebackup'); ?></h4>
            <div class="progress" style="height: 30px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated"
                     role="progressbar"
                     aria-valuenow="0"
                     aria-valuemin="0"
                     aria-valuemax="100"
                     style="width: 0%">
                    0%
                </div>
            </div>
            <div class="mt-3">
                <div id="largebackup-status"></div>
                <div id="largebackup-speed" class="text-muted"></div>
                <div id="largebackup-eta" class="text-muted"></div>
            </div>
        </div>
    </div>

    <style>
        .largebackup-upload-container {
            max-width: 800px;
            margin: 0 auto;
        }
    </style>

<?php
echo $OUTPUT->footer();
