<?= $this->extend('admin/Layout') ?>
<?= $this->section('content') ?>

<div class="container-fluid px-4">
    <h4 class="mt-4 mb-4">Tambah Jenis Loket</h4>

    <div class="card shadow-sm border-0">
        <div class="card-body">
           <form method="post" action="<?= base_url('admin/jenisLoket/store') ?>">

                <div class="mb-3">
                    <label class="form-label">Kode Jenis</label>
                    <input type="text" name="kode_jenis" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nama Jenis</label>
                    <input type="text" name="nama_jenis" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Keterangan</label>
                    <textarea name="keterangan" class="form-control"></textarea>
                </div>

                <button class="btn btn-primary">Simpan</button>
                <a href="<?= base_url('admin/jenisLoket') ?>" class="btn btn-secondary">Kembali</a>

            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
