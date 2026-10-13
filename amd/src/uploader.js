define(['jquery'], function($) {
    return {
        /**
         * Initialisiert den Large Backup Uploader
         * @param {number} contextid Die Moodle Context-ID
         * @param {number} maxfilesizebytes Maximale Dateigröße in Bytes
         */
        init: function(contextid, maxfilesizebytes) {

            const fileInput         = $('#largebackup-file');
            const uploadButton      = $('#largebackup-start-upload');
            const fileInfo          = $('#largebackup-file-info');
            const fileDetails       = $('#largebackup-file-details');
            const progressContainer = $('#largebackup-progress');
            const progressBar       = $('.progress-bar');
            const statusText        = $('#largebackup-status');
            const speedText         = $('#largebackup-speed');
            const etaText           = $('#largebackup-eta');

            let file, chunkSize = 5 * 1024 * 1024;
            let uploadedBytes = 0;
            let uploadSpeeds  = [];
            let sessionId     = null;

            fileInput.on('change', function(e) {
                file = e.target.files[0];
                resetUploadUI();

                if (!file) {
                    return;
                }

                if (!file.name.endsWith('.mbz')) {
                    fileDetails.html('<span class="text-danger">Bitte wählen Sie eine .mbz Datei aus.</span>');
                    fileInfo.show();
                    uploadButton.prop('disabled', true);
                    return;
                }

                if (file.size === 0) {
                    fileDetails.html('<span class="text-danger">Die Datei ist leer.</span>');
                    fileInfo.show();
                    uploadButton.prop('disabled', true);
                    return;
                }

                if (file.size > maxfilesizebytes) {
                    const maxMB  = Math.round(maxfilesizebytes / (1024 * 1024));
                    const fileMB = (file.size / (1024 * 1024)).toFixed(2);
                    fileDetails.html(
                        '<span class="text-danger">' +
                        'Die Datei ist zu groß: <strong>' + fileMB + ' MB</strong>. ' +
                        'Maximale Größe: <strong>' + maxMB + ' MB</strong>.' +
                        '</span>'
                    );
                    fileInfo.show();
                    uploadButton.prop('disabled', true);
                    return;
                }

                uploadButton.prop('disabled', false);
                const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
                fileDetails.html(
                    '<strong>' + escapeHtml(file.name) + '</strong><br>Größe: ' + sizeMB + ' MB'
                );
                fileInfo.show();
            });

            uploadButton.on('click', function() {
                uploadButton.prop('disabled', true);
                fileInput.prop('disabled', true);
                progressContainer.show();
                uploadedBytes = 0;
                uploadSpeeds  = [];
                sessionId     = generateSessionId();
                uploadNextChunk(0);
            });

            /**
             * Generiert eine eindeutige Session-ID
             * @returns {string} Die generierte Session-ID
             */
            function generateSessionId() {
                return Math.random().toString(36).substring(2) + Date.now();
            }

            /**
             * Escaped HTML-Sonderzeichen
             * @param {string} unsafe Der unsichere String
             * @returns {string} Der escaped String
             */
            function escapeHtml(unsafe) {
                return unsafe
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;");
            }

            /**
             * Setzt die Upload-UI zurück
             */
            function resetUploadUI() {
                progressBar.css('width', '0%')
                    .attr('aria-valuenow', 0)
                    .removeClass('bg-success bg-danger')
                    .addClass('progress-bar-striped progress-bar-animated');
                statusText.html('');
                speedText.text('');
                etaText.text('');
                progressContainer.hide();
            }

            /**
             * Lädt den nächsten Chunk hoch
             * @param {number} offset Der Byte-Offset im File
             */
            function uploadNextChunk(offset) {
                if (offset >= file.size) {
                    completeUpload();
                    return;
                }

                const chunkStartTime = Date.now();
                const chunk = file.slice(offset, offset + chunkSize);

                const formData = new FormData();
                formData.append('chunk', chunk);
                formData.append('sessionid', sessionId);
                formData.append('filename', file.name);
                formData.append('offset', offset);
                formData.append('total_size', file.size);
                formData.append('contextid', contextid);
                formData.append('sesskey', M.cfg.sesskey);

                const uploadUrl = M.cfg.wwwroot + '/local/largebackup/ajax.php';

                $.ajax({
                    url: uploadUrl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    cache: false,
                    dataType: 'json'
                })
                    .done(function(result) {
                        if (!result || !result.success) {
                            handleUploadError(result ? result.message : 'Upload fehlgeschlagen');
                            return;
                        }

                        uploadedBytes += chunk.size;

                        const currentTime   = Date.now();
                        const chunkDuration = (currentTime - chunkStartTime) / 1000;
                        const chunkSpeed    = chunk.size / chunkDuration;
                        uploadSpeeds.push(chunkSpeed);
                        if (uploadSpeeds.length > 5) {
                            uploadSpeeds.shift();
                        }

                        const avgSpeed   = uploadSpeeds.reduce((a, b) => a + b, 0) / uploadSpeeds.length;
                        const totalMB    = (file.size / (1024 * 1024)).toFixed(2);
                        const uploadedMB = (uploadedBytes / (1024 * 1024)).toFixed(2);
                        const progress   = Math.min(100, Math.round((uploadedBytes / file.size) * 100));

                        progressBar.css('width', progress + '%')
                            .attr('aria-valuenow', progress)
                            .text(progress + '%');

                        statusText.html(
                            'Upload-Fortschritt: <strong>' + uploadedMB + ' MB</strong> / ' +
                            '<strong>' + totalMB + ' MB</strong>'
                        );
                        speedText.text('Geschwindigkeit: ' + (avgSpeed / (1024 * 1024)).toFixed(2) + ' MB/s');

                        const remainingBytes = file.size - uploadedBytes;
                        const eta = remainingBytes / avgSpeed;
                        etaText.text('Verbleibende Zeit: ~' + Math.ceil(eta / 60) + ' Minuten');

                        uploadNextChunk(offset + chunk.size);
                    })
                    .fail(function(jqXHR) {
                        let errorMsg = 'Upload-Fehler';
                        if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                            errorMsg = jqXHR.responseJSON.message;
                        } else if (jqXHR.statusText) {
                            errorMsg = jqXHR.statusText;
                        }
                        handleUploadError(errorMsg);
                    });
            }

            /**
             * Behandelt Upload-Fehler
             * @param {string} errorMessage Die Fehlermeldung
             */
            function handleUploadError(errorMessage) {
                statusText.html(
                    '<span class="text-danger"><strong>Fehler:</strong> ' +
                    escapeHtml(errorMessage) + '</span>'
                );
                progressBar.removeClass('progress-bar-animated').addClass('bg-danger');
                uploadButton.prop('disabled', false);
                fileInput.prop('disabled', false);
            }

            /**
             * Beendet den Upload und leitet zur Wiederherstellung weiter
             */
            function completeUpload() {
                progressBar.removeClass('progress-bar-animated')
                    .addClass('bg-success')
                    .css('width', '100%')
                    .text('100%');

                statusText.html('<strong>Upload abgeschlossen!</strong> Bereite Wiederherstellung vor...');

                setTimeout(function() {
                    window.location.href = M.cfg.wwwroot + '/local/largebackup/start_restore.php?' +
                        'contextid=' + contextid +
                        '&sessionid=' + encodeURIComponent(sessionId) +
                        '&sesskey=' + M.cfg.sesskey;
                }, 1000);
            }
        }
    };
});
