<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>

<h2 class="mb-4">Edit Profil Instansi</h2>

<form method="post" enctype="multipart/form-data"
      action="<?= base_url('admin/profile/update/'.$profile['id']) ?>">
    <?= csrf_field() ?>

    <input class="form-control mb-2" name="nama_instansi"
           value="<?= esc($profile['nama_instansi']) ?>" required>

    <textarea class="form-control mb-2" name="alamat"><?= esc($profile['alamat']) ?></textarea>

    <input class="form-control mb-2" name="telp"
           value="<?= esc($profile['telp']) ?>">

    <input type="color" name="color_palette"
           value="<?= esc($profile['color_palette']) ?>" class="mb-2">

    <img src="<?= base_url('uploads/logo/'.$profile['gambar_logo']) ?>" height="80"><br><br>

    <input type="file" name="gambar_logo" class="form-control mb-3">

    <button class="btn btn-primary">Update</button>
</form>

<?= $this->endSection() ?>
