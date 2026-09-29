<div class="auth-card">
    <h2>Masuk</h2>
    <p class="muted">Login dulu supaya portfolio dan rekomendasi lo kebaca sesuai akun.</p>
    <?php echo form_open('login'); ?>
        <div class="form-grid">
            <div class="field">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="field">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
        </div>
        <div class="actions" style="margin-top:18px">
            <button class="btn" type="submit">Login</button>
            <a class="btn secondary" href="<?php echo site_url('register'); ?>">Register</a>
        </div>
    <?php echo form_close(); ?>
</div>
