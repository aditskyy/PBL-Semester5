<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Operator Loket</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>
<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <span class="navbar-brand mb-0 h1">Operator Loket - <?= esc($username ?? 'Guest') ?></span>
    </div>
</nav>

<div class="container text-center">
    <div class="row">
        <!-- Antrian sedang dipanggil -->
        <div class="col-md-4">
            <div class="card p-3 mb-3 shadow">
                <h5 class="text-muted">Antrian Sedang Dipanggil</h5>
                <h1 class="display-4 text-primary">
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
                <h1 class="display-4 text-success">
    <?php if (isset($antrianBerikut['nomor'])): ?>
        <?= $antrianBerikut['kode_jenis'] . '-' . $antrianBerikut['nomor']; ?>
        <br>
        <small class="text-muted">(<?= $antrianBerikut['kode_loket']; ?>)</small>
    <?php else: ?>
        -
    <?php endif; ?>
</h1>
                <div class="mt-3">
                    <a href="<?= site_url('operator/panggilSelanjutnya') ?>" class="btn btn-primary mb-2">Panggil Selanjutnya</a><br>
                    <a href="<?= site_url('operator/panggilUlang') ?>" class="btn btn-warning mb-2">Panggil Ulang</a><br>
                    <a href="<?= site_url('operator/selesai') ?>" class="btn btn-danger">Selesai</a>
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
</body>
</html>
