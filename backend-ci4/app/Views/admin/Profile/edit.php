<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>

<h2 class="mb-4">Edit Profil Instansi</h2>

<form method="post" action="<?= base_url('admin/profile/update/'.$profile['id']) ?>">
    <?= csrf_field() ?>

    <label>Nama Instansi</label>
    <input class="form-control mb-2" name="nama_instansi" value="<?= esc($profile['nama_instansi']) ?>" required>

    <label>Alamat</label>
    <textarea class="form-control mb-2" name="alamat"><?= esc($profile['alamat']) ?></textarea>

    <label>Telepon</label>
    <input class="form-control mb-2" name="telp" value="<?= esc($profile['telp']) ?>">

    <label>Tema Warna Aplikasi</label><br>
    <input type="color" name="color_palette" value="<?= esc($profile['color_palette']) ?>" class="mb-3">

    <br>
    <button class="btn btn-primary">Update Profile</button>
</form>

<?= $this->endSection() ?>