<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Operator Dashboard - <?= esc($nama_loket ?? 'Loket') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f4f7f6; }
        .navbar { box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .card { border: none; border-radius: 12px; transition: transform 0.2s; }
        .card:hover { transform: translateY(-5px); }
        .display-4 { font-weight: 700; letter-spacing: -1px; }
        .btn-group-action .btn { border-radius: 8px; margin-bottom: 10px; font-weight: 600; }
        .status-badge { font-size: 0.8rem; padding: 5px 12px; border-radius: 20px; }
        /* Style tambahan untuk tombol logout */
        .btn-logout { 
            border: 1px solid rgba(255,255,255,0.2); 
            color: rgba(255,255,255,0.8);
            transition: all 0.3s;
        }
        .btn-logout:hover { 
            background: #dc3545; 
            color: white; 
            border-color: #dc3545;
        }
    </style>
    
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-5">
    <div class="container">
        <span class="navbar-brand d-flex align-items-center">
            <i class="bi bi-display me-2 text-primary"></i>
            <div>
                <span class="d-block h5 mb-0"><?= esc($nama_loket ?? 'Nama Loket') ?></span>
                <small class="text-secondary" style="font-size: 0.7rem;">ID: <?= esc($kode_loket ?? '-') ?> | KATEGORI: <?= esc($kode_jenis ?? '-') ?></small>
            </div>
        </span>

        <div class="d-flex align-items-center gap-3">
            <span class="badge bg-success status-badge d-none d-md-inline-block">
                <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i> Operator Online
            </span>
            <div class="vr text-secondary d-none d-md-block" style="height: 30px;"></div>
            <a href="<?= site_url('operator/logout') ?>" class="btn btn-sm btn-logout">
               <i class="bi bi-box-arrow-right me-1"></i> Logout
            </a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="row g-4">
        <div class="col-lg-4 col-md-6">
            <div class="card h-100 shadow-sm p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-3 me-3">
                        <i class="bi bi-megaphone-fill text-primary h4 mb-0"></i>
                    </div>
                    <h6 class="text-secondary fw-600 mb-0">Sedang Dipanggil</h6>
                </div>
                <div class="text-center py-4">
                    <h1 id="antrian-sekarang" class="display-4 text-primary mb-0">
                        <?php if (isset($antrianSekarang['nomor'])): ?>
                            <?= $antrianSekarang['kode_jenis'] . '-' . $antrianSekarang['nomor']; ?>
                        <?php else: ?>
                            <span class="text-light-emphasis">---</span>
                        <?php endif; ?>
                    </h1>
                    <p class="text-muted mt-2 small">Nomor Antrian saat ini</p>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="card h-100 shadow-sm p-4 border-start border-success border-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-success bg-opacity-10 p-2 rounded-3 me-3">
                        <i class="bi bi-person-plus-fill text-success h4 mb-0"></i>
                    </div>
                    <h6 class="text-secondary fw-600 mb-0">Antrian Berikutnya</h6>
                </div>
                <div class="text-center py-3">
                    <h1 id="antrian-berikut" class="display-5 text-success mb-4">
                        <?php if (isset($antrianBerikut['nomor'])): ?>
                            <?= $antrianBerikut['kode_jenis'] . '-' . $antrianBerikut['nomor']; ?>
                        <?php else: ?>
                            <span class="text-light-emphasis">---</span>
                        <?php endif; ?>
                    </h1>
                    
                    <div class="btn-group-action">
                        <button id="btnSelanjutnya" class="btn btn-primary w-100 shadow-sm py-2">
                            <i class="bi bi-skip-forward-fill me-2"></i>Panggil Selanjutnya
                        </button>
                        <button id="btnUlang" class="btn btn-outline-warning w-100 py-2">
                            <i class="bi bi-arrow-clockwise me-2"></i>Panggil Ulang
                        </button>
                        <button id="btnSelesai" class="btn btn-outline-danger w-100 py-2">
                            <i class="bi bi-check-circle-fill me-2"></i>Selesaikan
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100 shadow-sm p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-dark bg-opacity-10 p-2 rounded-3 me-3">
                        <i class="bi bi-hdd-network text-dark h4 mb-0"></i>
                    </div>
                    <h6 class="text-secondary fw-600 mb-0">Loket Aktif Lainnya</h6>
                </div>
                <div class="list-group list-group-flush mt-2">
                    <?php if (!empty($loket)): ?>
                        <?php foreach ($loket as $l): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span><i class="bi bi-dot text-success h4 mb-0"></i> <?= esc($l['nama_loket']) ?></span>
                                <span class="badge bg-light text-dark border fw-normal" style="font-size: 0.7rem;"><?= esc($l['kode_loket']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <small class="text-muted italic">Tidak ada loket lain aktif</small>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="mt-auto pt-4 border-top text-center">
                     <button id="btnReset" class="btn btn-link text-decoration-none text-muted p-0" style="font-size: 0.75rem;">
                         <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Data Selesai
                     </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        const kodeJenis = "<?= esc($kode_jenis) ?>";
        const kodeLoket = "<?= esc($kode_loket) ?>";

        function refreshDashboard() {
            window.location.href = "<?= site_url('operator/dashboard') ?>?jenis=" + kodeJenis + "&loket=" + kodeLoket;
        }

        $("#btnSelanjutnya").click(function() {
            $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
            $.post("<?= site_url('api/operator/panggilSelanjutnya') ?>", { kode_jenis: kodeJenis, kode_loket: kodeLoket }, function(res) {
                refreshDashboard();
            }).fail(function(xhr) { 
                alert("Error: " + xhr.responseText); 
                $(this).prop('disabled', false).html('<i class="bi bi-skip-forward-fill me-2"></i>Panggil Selanjutnya'); 
            });
        });

        $("#btnUlang").click(function() {
            const idAntrian = "<?= esc($antrianSekarang['id_antrian'] ?? '') ?>";
            if (!idAntrian) return alert("Tidak ada antrian aktif.");
            $.post("<?= site_url('api/operator/panggilUlang') ?>", { id_antrian: idAntrian, kode_loket: kodeLoket }, function(res) {
                // Notifikasi toast bisa ditambahkan di sini jika perlu
            });
        });

        $("#btnSelesai").click(function() {
            const idAntrian = "<?= esc($antrianSekarang['id_antrian'] ?? '') ?>";
            if (!idAntrian) return alert("Tidak ada antrian aktif.");
            $.post("<?= site_url('api/operator/selesai') ?>", { id_antrian: idAntrian, kode_loket: kodeLoket }, function(res) {
                refreshDashboard();
            });
        });

        $("#btnReset").click(function() {
            if (confirm("⚠️ PERINGATAN: Semua antrian yang sudah selesai akan dikembalikan ke status 'Menunggu'. Lanjutkan?")) {
                $.post("<?= site_url('api/operator/resetAntrian') ?>", function() { refreshDashboard(); });
            }
        });
    });
</script>
</body>
</html>