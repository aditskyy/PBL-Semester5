<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>

<h2 class="mb-4">Buat Profil Instansi</h2>

<form method="post" enctype="multipart/form-data" action="<?= base_url('admin/profile/store') ?>">
    <?= csrf_field() ?>

    <input class="form-control mb-2" name="nama_instansi" placeholder="Nama Instansi" required>
    <textarea class="form-control mb-2" name="alamat" placeholder="Alamat"></textarea>
    <input class="form-control mb-2" name="telp" placeholder="Telepon">
    <input type="color" name="color_palette" class="mb-2">
    <input type="file" name="gambar_logo" class="form-control mb-3">

    <button class="btn btn-primary">Simpan</button>
</form>

<?= $this->endSection() ?>
