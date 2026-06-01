<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Tidak perlu include koneksi atau class di sini lagi
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Game List (AJAX)</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        #live-log {
            background-color: #212529;
            color: #f8f9fa;
            border: 1px solid #495057;
            padding: 15px;
            height: 400px;
            overflow-y: auto;
            font-family: 'Courier New', Courier, monospace;
            font-size: 0.9em;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
    </style>
</head>
<body>
<div class="container-xxl flex-grow-1 container-p-y mt-4">
    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Menu Utama /</span> Update Game List (PGSOFT - AJAX)
    </h4>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                 <div class="card-header"><h5 class="card-title">Update Game Otomatis via AJAX</h5></div>
                <div class="card-body">
                    <p>Tombol ini akan memulai proses sinkronisasi di latar belakang tanpa me-reload halaman. Anda dapat memantau progresnya secara langsung di bawah.</p>
                    
                    <button id="startButton" class="btn btn-primary">
                        Mulai Update Game PGSOFT
                    </button>
                    
                    <div id="progress-container" class="mt-4" style="display: none;">
                        <div class="progress" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="height: 25px">
                            <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%">0%</div>
                        </div>
                        <pre id="live-log" class="mt-2"></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const startButton = document.getElementById('startButton');
    const progressContainer = document.getElementById('progress-container');
    const progressBar = document.getElementById('progress-bar');
    const liveLog = document.getElementById('live-log');

    let totalGames = 0;

    function addLog(message, type = 'info') {
        const colors = { info: '#f8f9fa', success: '#28a745', error: '#dc3545', warn: '#ffc107' };
        const logEntry = document.createElement('div');
        logEntry.innerHTML = `[${new Date().toLocaleTimeString()}] ${message}`;
        logEntry.style.color = colors[type] || colors.info;
        liveLog.appendChild(logEntry);
        liveLog.scrollTop = liveLog.scrollHeight;
    }

    function updateProgress(processedCount) {
        if (totalGames === 0) return;
        const percentage = Math.round((processedCount / totalGames) * 100);
        progressBar.style.width = `${percentage}%`;
        progressBar.innerText = `${percentage}%`;
        progressBar.setAttribute('aria-valuenow', percentage);
    }

    async function processBatch() {
        try {
            const response = await fetch('ajax_handler.php?action=process_batch');
            const data = await response.json();

            if (data.logs && Array.isArray(data.logs)) {
                data.logs.forEach(log => addLog(log));
            }
            
            updateProgress(data.processed_count);

            if (data.status === 'processing') {
                // Lanjut ke batch berikutnya
                requestAnimationFrame(processBatch); 
            } else if (data.status === 'complete') {
                addLog('<strong>PROSES SELESAI</strong>', 'success');
                finalizeProcess();
            } else {
                throw new Error(data.message || 'Terjadi kesalahan saat memproses batch.');
            }
        } catch (error) {
            addLog(`Error: ${error.message}`, 'error');
            startButton.disabled = false;
        }
    }

    async function finalizeProcess() {
        await fetch('ajax_handler.php?action=finalize');
        addLog('File sementara telah dibersihkan.', 'info');
        startButton.disabled = false;
        progressBar.classList.remove('progress-bar-animated');
    }

    startButton.addEventListener('click', async () => {
        // Reset UI
        startButton.disabled = true;
        progressContainer.style.display = 'block';
        liveLog.innerHTML = '';
        progressBar.style.width = '0%';
        progressBar.innerText = '0%';
        progressBar.classList.add('progress-bar-animated');

        addLog('Memulai proses...', 'info');

        try {
            const response = await fetch('ajax_handler.php?action=start');
            const data = await response.json();

            if (data.status === 'started') {
                totalGames = data.total_games;
                addLog(data.message, 'success');
                if (totalGames > 0) {
                    // Mulai proses batch pertama
                    processBatch();
                } else {
                    addLog('Tidak ada game untuk diproses.', 'warn');
                    finalizeProcess();
                }
            } else {
                throw new Error(data.message || 'Gagal memulai proses.');
            }
        } catch (error) {
            addLog(`Error Kritis: ${error.message}`, 'error');
            startButton.disabled = false;
        }
    });

</script>
</body>
</html>