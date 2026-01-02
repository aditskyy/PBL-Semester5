<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <style>
        body { background-color: #0099ff; font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .register-card { background: white; padding: 30px; border-radius: 10px; width: 350px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #333; }
        input, select { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .login-link { text-align: center; display: block; margin-top: 15px; color: #007bff; text-decoration: none; }
    </style>
</head>
<body>
    <div class="register-card">
        <h2>Register</h2>
        <form action="<?= base_url('/register/process') ?>" method="post">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <select name="role">
                <option value="operator">Operator</option>
                <option value="admin">Admin</option>
            </select>
            <button type="submit">Daftar</button>
            <a href="<?= base_url('/login') ?>" class="login-link">Sudah punya akun? Login</a>
        </form>
    </div>
</body>
</html>