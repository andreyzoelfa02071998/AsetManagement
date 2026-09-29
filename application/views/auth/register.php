<div class="auth-card">
    <h2>Buat Akun</h2>
    <p class="muted">Setelah register, lo bisa langsung onboarding portfolio dari screenshot atau manual.</p>
    <?php echo form_open('register'); ?>
        <div class="form-grid">
            <div class="field">
                <label>Nama</label>
                <input type="text" name="name" required>
            </div>
            <div class="field">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="field">
                <label>Password</label>
                <input type="password" name="password" minlength="6" required>
            </div>
        </div>
        <div class="actions" style="margin-top:18px">
            <button class="btn" type="submit">Register</button>
            <a class="btn secondary" href="<?php echo site_url('login'); ?>">Sudah punya akun</a>
        </div>
    <?php echo form_close(); ?>
</div>
