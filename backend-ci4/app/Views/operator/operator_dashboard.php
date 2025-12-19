<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Operator Loket</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
</head>
<body>
<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <!-- 🟢 Tambahan baru di sini -->
        <span class="navbar-brand mb-0 h1">
            Loket <?= esc($kode_loket ?? '-') ?> - Jenis: <?= esc($kode_jenis ?? '-') ?>
        </span>
    </div>
</nav>

<div class="container text-center">
    <div class="row">
        <!-- Antrian sedang dipanggil -->
        <div class="col-md-4">
            <div class="card p-3 mb-3 shadow">
                <h5 class="text-muted">Antrian Sedang Dipanggil</h5>
                <h1 id="antrian-sekarang" class="display-4 text-primary">
                    <?php if (isset($antrianSekarang['nomor'])): ?>
                        <?= $antrianSekarang['kode_jenis'] . '-' . $antrianSekarang['nomor']; ?>
                        <br>
                        <small class="text-muted">(<?= $antrianSekarang['kode_loket']; ?>)</small>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </h1>
            </div>
        </div>

        <!-- Antrian berikutnya -->
        <div class="col-md-4">
            <div class="card p-3 mb-3 shadow">
                <h5 class="text-muted">Antrian Berikutnya</h5>
                <h1 id="antrian-berikut" class="display-4 text-success">
                    <?php if (isset($antrianBerikut['nomor'])): ?>
                        <?= $antrianBerikut['kode_jenis'] . '-' . $antrianBerikut['nomor']; ?>
                        <br>
                        <small class="text-muted">(<?= $antrianBerikut['kode_loket']; ?>)</small>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </h1>
                <div class="mt-3">
                    <button id="btnSelanjutnya" class="btn btn-primary mb-2 w-100">Panggil Selanjutnya</button>
                    <button id="btnUlang" class="btn btn-warning mb-2 w-100">Panggil Ulang</button>
                    <button id="btnSelesai" class="btn btn-danger mb-2 w-100">Selesai</button>
                    <button id="btnReset" class="btn btn-success">Reset Antrian</button>
                </div>
            </div>
        </div>

        <!-- Loket aktif -->
        <div class="col-md-4">
            <div class="card p-3 mb-3 shadow">
                <h5 class="text-muted">Loket Aktif</h5>
                <ul class="list-group">
                    <?php if (!empty($loket)): ?>
                        <?php foreach ($loket as $l): ?>
                            <li class="list-group-item"><?= esc($l['nama_loket']) ?></li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="list-group-item text-muted">Tidak ada loket aktif</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- JS tetap sama seperti sebelumnya -->
<script>
$(document).ready(function() {
    const kodeJenis = "<?= esc($kode_jenis) ?>";
    const kodeLoket = "<?= esc($kode_loket ?? ($loket[0]['kode_loket'] ?? 'A-01')) ?>";

    function refreshDashboard() {
        location.reload();
    }

    $("#btnSelanjutnya").click(function() {
        $.post("<?= site_url('api/operator/panggilSelanjutnya') ?>", { kode_jenis: kodeJenis, kode_loket: kodeLoket }, function(res) {
            console.log(res);
            refreshDashboard();
        }).fail(function(xhr) {
            alert("Gagal panggil selanjutnya: " + xhr.responseText);
        });
    });

    $("#btnUlang").click(function() {
        const idAntrian = "<?= esc($antrianSekarang['id_antrian'] ?? '') ?>";
        if (!idAntrian) {
            alert("Tidak ada antrian sedang dipanggil.");
            return;
        }

        $.post("<?= site_url('api/operator/panggilUlang') ?>", { id_antrian: idAntrian }, function(res) {
            alert(res.message);
        }).fail(function(xhr) {
            alert("Gagal panggil ulang: " + xhr.responseText);
        });
    });

    $("#btnSelesai").click(function() {
        const idAntrian = "<?= esc($antrianSekarang['id_antrian'] ?? '') ?>";
        if (!idAntrian) {
            alert("Tidak ada antrian sedang dipanggil.");
            return;
        }

        $.post("<?= site_url('api/operator/selesai') ?>", { id_antrian: idAntrian }, function(res) {
            alert(res.message);
            refreshDashboard();
        }).fail(function(xhr) {
            alert("Gagal menyelesaikan antrian: " + xhr.responseText);
        });
    });
 
  $("#btnReset").click(function() {
    if (!confirm("⚠️ Yakin ingin mereset semua antrian selesai menjadi MENUNGGU lagi?")) {
        return;
    }

    $.ajax({
        url: "<?= site_url('api/operator/resetAntrian') ?>",
        type: "POST",
        dataType: "json",
        success: function(res) {
            if (res.status === "success") {
                alert(res.message);
                location.reload();
            } else {
                alert("Gagal: " + res.message);
            }
        },
        error: function(xhr) {
            console.error(xhr.responseText);
            alert("Terjadi kesalahan saat mereset antrian!");
        }
    });
});

});
</script>
</body>
</html>
