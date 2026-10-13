<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

// Admin-Seite initialisieren
admin_externalpage_setup('local_largebackup_report');

require_login();
require_capability('local/largebackup:viewreports', context_system::instance());

$PAGE->set_url(new moodle_url('/local/largebackup/report.php'));
$PAGE->set_title(get_string('largebackup_reports', 'local_largebackup'));
$PAGE->set_heading(get_string('largebackup_reports', 'local_largebackup'));

echo $OUTPUT->header();

// Statistiken abrufen (PostgreSQL-kompatibel)
$stats = [];

// Datenbank-Typ erkennen
$dbtype = $DB->get_dbfamily();

// Uploads der letzten 30 Tage
if ($dbtype === 'postgres') {
    $sql = "SELECT DATE(TO_TIMESTAMP(timemodified)) as day,
            COUNT(*) as count,
            SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as success,
            SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed
            FROM {local_largebackup_logs}
            WHERE timemodified > EXTRACT(EPOCH FROM (NOW() - INTERVAL '30 days'))
            GROUP BY day
            ORDER BY day DESC";
} else {
    $sql = "SELECT DATE(FROM_UNIXTIME(timemodified)) as day,
            COUNT(*) as count,
            SUM(status = 'success') as success,
            SUM(status = 'failed') as failed
            FROM {local_largebackup_logs}
            WHERE timemodified > UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 30 DAY))
            GROUP BY day
            ORDER BY day DESC";
}

try {
    $stats['uploads'] = $DB->get_records_sql($sql);
} catch (Exception $e) {
    $stats['uploads'] = [];
}

// Gesamtstatistiken
if ($dbtype === 'postgres') {
    $sql = "SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as success,
            SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
            AVG(filesize) as avg_size
            FROM {local_largebackup_logs}";
} else {
    $sql = "SELECT 
            COUNT(*) as total,
            SUM(status = 'success') as success,
            SUM(status = 'failed') as failed,
            AVG(filesize) as avg_size
            FROM {local_largebackup_logs}";
}

try {
    $stats['total'] = $DB->get_record_sql($sql);
    if (!$stats['total']) {
        $stats['total'] = (object)[
            'total' => 0,
            'success' => 0,
            'failed' => 0,
            'avg_size' => 0
        ];
    }
} catch (Exception $e) {
    $stats['total'] = (object)[
        'total' => 0,
        'success' => 0,
        'failed' => 0,
        'avg_size' => 0
    ];
}

// Top 10 größte Uploads
$sql = "SELECT id, filename, filesize, timemodified, status
        FROM {local_largebackup_logs}
        ORDER BY filesize DESC";

try {
    $stats['largest'] = $DB->get_records_sql($sql, null, 0, 10);
} catch (Exception $e) {
    $stats['largest'] = [];
}

?>

<div class="largebackup-report">
    <h2>Nutzungsstatistiken - Large Backup Upload</h2>

    <!-- Gesamtstatistiken -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3><?php echo $stats['total']->total; ?></h3>
                    <p class="text-muted">Gesamt-Uploads</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3 class="text-success"><?php echo $stats['total']->success; ?></h3>
                    <p class="text-muted">Erfolgreich</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3 class="text-danger"><?php echo $stats['total']->failed; ?></h3>
                    <p class="text-muted">Fehlgeschlagen</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3><?php echo round($stats['total']->avg_size / (1024 * 1024), 2); ?> MB</h3>
                    <p class="text-muted">Durchschn. Größe</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Uploads der letzten 30 Tage -->
    <h3>Uploads der letzten 30 Tage</h3>
    <?php if (!empty($stats['uploads'])): ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Datum</th>
                    <th>Gesamt</th>
                    <th>Erfolgreich</th>
                    <th>Fehlgeschlagen</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stats['uploads'] as $upload): ?>
                    <tr>
                        <td><?php echo $upload->day; ?></td>
                        <td><?php echo $upload->count; ?></td>
                        <td class="text-success"><?php echo $upload->success; ?></td>
                        <td class="text-danger"><?php echo $upload->failed; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="alert alert-info">Keine Daten verfügbar</p>
    <?php endif; ?>

    <!-- Top 10 größte Uploads -->
    <h3 class="mt-4">Top 10 größte Uploads</h3>
    <?php if (!empty($stats['largest'])): ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Dateiname</th>
                    <th>Größe</th>
                    <th>Datum</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stats['largest'] as $file): ?>
                    <tr>
                        <td><?php echo s($file->filename); ?></td>
                        <td><?php echo round($file->filesize / (1024 * 1024), 2); ?> MB</td>
                        <td><?php echo userdate($file->timemodified); ?></td>
                        <td>
                            <?php if ($file->status === 'success'): ?>
                                <span class="badge badge-success">Erfolgreich</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Fehlgeschlagen</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="alert alert-info">Keine Daten verfügbar</p>
    <?php endif; ?>
</div>

<style>
.largebackup-report .card {
    margin-bottom: 20px;
}
.largebackup-report .card-body h3 {
    margin: 0;
    font-size: 2em;
}
</style>

<?php
echo $OUTPUT->footer();
