<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>

<h3>Tambah Loket</h3>

<?php if(session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger">
        <?php foreach(session()->getFlashdata('errors') as $err): ?>
            <div><?= esc($err) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form action="/admin/loket/store" method="post">
    <div class="mb-3">
        <label>Kode Loket</label>
        <input type="text" name="kode_loket" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Nama Loket</label>
        <input type="text" name="nama_loket" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Jenis Layanan</label>
        <select name="kode_jenis" class="form-control" required>
            <?php foreach ($jenis as $j) : ?>
                <option value="<?= $j['kode_jenis'] ?>"><?= $j['nama_jenis'] ?></option>
            <?php endforeach ?>
        </select>
    </div>

    <button class="btn btn-primary">Simpan</button>
</form>

<?= $this->endSection() ?>
