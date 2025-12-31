<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Admin' ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body { background: #f5f6fa; }

        /* Sidebar */
        .sidebar {
            width: 250px;
            height: 100vh;
            background: #1d2939;
            color: #fff;
            padding: 25px;
            position: fixed;
        }

        .sidebar h2 {
            font-size: 30px;
            font-weight: bold;
        }

        .sidebar a {
            color: #fff;
            text-decoration: none;
            display: block;
            padding: 12px 0;
            font-size: 16px;
        }

        .sidebar a:hover {
            color: #a5c1ff;
        }

        /* Topbar */
        .topbar {
            margin-left: 240px;
            height: 65px;
            background: white;
            display: flex;
            align-items: center;
            padding: 0 25px;
            border-bottom: 1px solid #ddd;
        }

        /* Content */
        .content {
            margin-left: 240px;
            padding: 25px;
        }
    </style>
</head>

<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <h2>ANTRIAN</h2>
        <hr style="background:white">

        <p class="mt-3 mb-1 text-secondary">Menu Utama</p>

        <a href="/admin/dashboard">🏠 Beranda</a>

        <p class="mt-4 mb-1 text-secondary">Utility</p>

        <a href="<?= base_url('admin/users') ?>">Manajemen User</a>
        <a href="<?= base_url('admin/jenisLoket') ?>">Manajemen Jenis Loket</a>
        <a href="<?= base_url('admin/loket') ?>">Manajemen Loket</a>
        <a href="<?= base_url('admin/antrian') ?>">Manajemen Antrian</a>
        <a href="<?= base_url('admin/profile') ?>">Manajemen Profile</a>
        <a href="<?= base_url('admin/log-antrian') ?>">Log Antrian</a>
        


        <a href="/logout">🚪 Logout</a>
    </div>

    <!-- Top bar -->
    <div class="topbar">
        <div class="ms-auto">
            <b><?= session()->get('username') ?></b> (Admin)
        </div>
    </div>

    <!-- Content -->
    <div class="content">
        <?= $this->renderSection('content') ?>
    </div>

</body>
</html>
