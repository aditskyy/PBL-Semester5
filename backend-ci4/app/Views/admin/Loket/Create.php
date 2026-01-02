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

    <div class="mb-3">
    <label>Warna Loket (Muncul di TV/Display)</label>
    <input type="color" name="warna" class="form-control form-control-color" 
           value="<?= isset($loket) ? $loket['warna'] : '#1E88E5' ?>" title="Pilih warna loket">
    <small class="text-muted">Warna ini akan menjadi background nomor antrean di layar</small>
</div>

<div class="mb-3">
    <label>Icon (Bootstrap Icon Name)</label>
    <select name="icon" class="form-control">
        <option value="account_balance" <?= (isset($loket) && $loket['icon'] == 'account_balance') ? 'selected' : '' ?>>Bank (account_balance)</option>
        <option value="people" <?= (isset($loket) && $loket['icon'] == 'people') ? 'selected' : '' ?>>People (people)</option>
        <option value="credit_card" <?= (isset($loket) && $loket['icon'] == 'credit_card') ? 'selected' : '' ?>>Credit Card (credit_card)</option>
        <option value="person" <?= (isset($loket) && $loket['icon'] == 'person') ? 'selected' : '' ?>>Person (person)</option>
    </select>
    <small class="text-muted">Pilih icon yang sesuai dengan fungsi loket</small>
</div>

    <button class="btn btn-primary">Simpan</button>
</form>

<?= $this->endSection() ?>
