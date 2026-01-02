<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Loket Operator - Sistem Antrean</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
        }
        .card { 
            border: none; 
            border-radius: 16px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.08) !important;
        }
        .form-label { font-weight: 600; color: #495057; font-size: 0.9rem; }
        .form-select { 
            border-radius: 10px; 
            padding: 0.6rem 1rem; 
            border-color: #dee2e6;
        }
        .form-select:focus { 
            border-color: #0d6efd; 
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1); 
        }
        .btn-primary { 
            border-radius: 10px; 
            padding: 0.7rem; 
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3); }
        .brand-icon {
            width: 60px;
            height: 60px;
            background: rgba(13, 110, 253, 0.1);
            color: #0d6efd;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 1.5rem;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card p-4 p-md-5 mx-auto" style="max-width: 450px;">
        <div class="text-center">
            <div class="brand-icon">
                <i class="bi bi-person-gear"></i>
            </div>
            <h4 class="fw-bold mb-1">Konfigurasi Loket</h4>
            <p class="text-muted small mb-4">Silakan pilih unit kerja Anda hari ini</p>
        </div>

        <form action="<?= site_url('operator/setOperatorSession') ?>" method="post">
            <div class="mb-3">
                <label for="kode_jenis" class="form-label">
                    <i class="bi bi-grid-fill me-1"></i> Jenis Layanan
                </label>
                <select id="kode_jenis" name="kode_jenis" class="form-select" required>
                    <option value="">-- Pilih Jenis --</option>
                    <?php if (!empty($jenis)): ?>
                        <?php foreach ($jenis as $j): ?>
                            <option value="<?= esc($j['kode_jenis']) ?>">
                                <?= esc($j['nama_jenis']) ?> (<?= esc($j['kode_jenis']) ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="">(Data tidak tersedia)</option>
                    <?php endif; ?>
                </select>
            </div>

            <div class="mb-4">
                <label for="kode_loket" class="form-label">
                    <i class="bi bi-door-open-fill me-1"></i> Pilih Loket
                </label>
                <select id="kode_loket" name="kode_loket" class="form-select" required>
                    <option value="">-- Pilih Loket --</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary w-100 d-flex align-items-center justify-content-center">
                <span>Masuk Dashboard</span>
                <i class="bi bi-arrow-right-short ms-2 h5 mb-0"></i>
            </button>
        </form>
        
        <div class="text-center mt-4">
            <a href="<?= site_url('logout') ?>" class="text-decoration-none text-danger small fw-bold">
                <i class="bi bi-box-arrow-left me-1"></i> Keluar Aplikasi
            </a>
        </div>
    </div>
</div>

<script>
document.getElementById('kode_jenis').addEventListener('change', async function() {
    const kodeJenis = this.value;
    const loketSelect = document.getElementById('kode_loket');

    // Reset isi dropdown loket
    loketSelect.innerHTML = '<option value="">Memuat...</option>';

    if (!kodeJenis) {
        loketSelect.innerHTML = '<option value="">-- Pilih Loket --</option>';
        return;
    }

    try {
        const res = await fetch(`<?= site_url('operator/getLoketByJenis/') ?>${kodeJenis}`);
        if (!res.ok) throw new Error('Gagal memuat data loket');
        const data = await res.json();

        loketSelect.innerHTML = '<option value="">-- Pilih Loket --</option>';
        if (Array.isArray(data) && data.length > 0) {
            data.forEach(l => {
                // Menampilkan nama asli loket dari DB agar lebih informatif
                loketSelect.innerHTML += `<option value="${l.kode_loket}">${l.nama_loket}</option>`;
            });
        } else {
            loketSelect.innerHTML = '<option value="">(Tidak ada loket tersedia)</option>';
        }
    } catch (err) {
        loketSelect.innerHTML = '<option value="">Gagal memuat data</option>';
        console.error(err);
    }
});
</script>

</body>
</html>