<?= $this->extend('admin/Layout'); ?>
<?= $this->section('content'); ?>

<div class="container-fluid">

    <h2 class="mb-4">Log Aktivitas Antrian</h2>

    <?php if (session()->getFlashdata('success')) : ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= session()->getFlashdata('success'); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>


    <!-- FILTER FORM -->
    <form action="<?= base_url('admin/log-antrian/filter'); ?>" method="post" class="row g-2 mb-3">

        <div class="col-md-3">
            <label>Tanggal</label>
            <input type="date" name="tanggal" value="<?= $tanggal ?? '' ?>" class="form-control">
        </div>

        <div class="col-md-3">
            <label>Aksi</label>
            <select name="aksi" class="form-control">
    <option value="">-- Semua --</option>
    <option value="AMBIL" <?= ($aksi ?? '') == 'AMBIL' ? 'selected' : '' ?>>AMBIL</option>
    <option value="PANGGIL" <?= ($aksi ?? '') == 'PANGGIL' ? 'selected' : '' ?>>PANGGIL</option>
    <option value="RECALL" <?= ($aksi ?? '') == 'RECALL' ? 'selected' : '' ?>>RECALL</option>
    <option value="SELESAI" <?= ($aksi ?? '') == 'SELESAI' ? 'selected' : '' ?>>SELESAI</option>
    <option value="LOGIN" <?= ($aksi ?? '') == 'LOGIN' ? 'selected' : '' ?>>LOGIN</option>
    <option value="LOGOUT" <?= ($aksi ?? '') == 'LOGOUT' ? 'selected' : '' ?>>LOGOUT</option>
             </select>
        </div>

        <div class="col-md-3">
            <label>Operator / User</label>
            <select name="user_id" class="form-control">
                <option value="">-- Semua --</option>
                <?php foreach ($users as $u) : ?>
                    <option value="<?= $u['id']; ?>" <?= ($user_id ?? '') == $u['id'] ? 'selected' : '' ?>>
                        <?= $u['username']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-3 d-grid">
            <label>&nbsp;</label>
            <button type="submit" class="btn btn-primary">Filter</button>
        </div>

    </form>

    <div class="mb-3">
        <a href="<?= base_url('admin/log-antrian'); ?>" class="btn btn-secondary">Reset Filter</a>
        <a href="<?= base_url('admin/log-antrian/reset'); ?>" class="btn btn-danger"
            onclick="return confirm('Yakin ingin menghapus semua log?')">
            Bersihkan Semua Log
        </a>
    </div>

    <!-- TABLE -->
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>No</th>
                <th>Waktu</th>
                <th>Antrian</th>
                <th>Aksi</th>
                <th>Operator</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($logs)) : ?>
                <?php $no = 1; foreach ($logs as $row) : ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><?= date('d-m-Y H:i:s', strtotime($row['waktu'])); ?></td>
                        <td><?= $row['id_antrian']; ?></td>

                        <!-- BADGE Aksi -->
                        <td>
    <?php 
        $a = strtoupper($row['aksi']); // Pastikan kapital
        
        // Menentukan kelas CSS berdasarkan aksi
        if ($a == 'PANGGIL') {
            $bg = 'bg-primary'; // Biru terang
        } elseif ($a == 'SELESAI') {
            $bg = 'bg-success'; // Hijau sukses
        } elseif ($a == 'AMBIL') {
            $bg = 'bg-info text-dark'; // Biru muda/cyan (untuk nasabah)
        } elseif ($a == 'RECALL') {
            $bg = 'bg-warning text-dark'; // Kuning (perlu perhatian)
        } elseif ($a == 'LOGIN') {
            $bg = 'bg-dark'; // Hitam (aktivitas sistem)
        } elseif ($a == 'LOGOUT') {
            $bg = 'bg-danger'; // Merah (aktivitas keluar)
        } else {
            $bg = 'bg-secondary'; // Abu-abu untuk aksi lainnya
        }
    ?>
    <span class="badge <?= $bg ?> shadow-sm" style="min-width: 80px; font-weight: 600;">
        <?= $a; ?>
    </span>
</td>

                        <td><?= $row['username'] ?? 'System'; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="5" class="text-center">Belum ada log</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if ($pager->getPageCount() > 1) : ?>
    <div class="mt-3">
        <?= $pager->links(); ?>
    </div>
<?php endif; ?>
</div>

<!-- AUTO REFRESH (Jika Mau Aktifkan) -->

<script>
    setTimeout(() => {
        location.reload();
    }, 5000); // refresh tiap 5 detik

    setTimeout(() => {
        let alert = document.querySelector('.alert');
        if (alert) {
            alert.style.transition = "0.5s";
            alert.style.opacity = "0";
            setTimeout(() => alert.remove(), 500);
        }
    }, 3000);
</script>

<style>
.badge {
    padding: 7px 12px;
    font-size: 14px;
}
</style>

<?= $this->endSection(); ?>
