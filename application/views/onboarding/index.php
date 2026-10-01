<div class="grid two">
    <div class="panel">
        <h3>Upload Screenshot Portfolio</h3>
        <p class="muted">Upload screenshot dari Tring, Stockbit, Bibit, atau aplikasi lain. Hasilnya masuk halaman review dulu supaya bisa dikoreksi sebelum disimpan.</p>

        <?php if (isset($ai_processor)): ?>
            <div class="processor-card">
                <div>
                    <span class="badge <?php echo html_escape($ai_processor['badge_class']); ?>"><?php echo html_escape($ai_processor['title']); ?></span>
                    <strong><?php echo html_escape($ai_processor['provider']); ?></strong>
                    <?php if ($ai_processor['model']): ?>
                        <small>Model: <?php echo html_escape($ai_processor['model']); ?></small>
                    <?php endif; ?>
                </div>
                <p><?php echo html_escape($ai_processor['description']); ?></p>
                <a href="<?php echo site_url('settings/ai'); ?>">Atur AI</a>
            </div>
        <?php endif; ?>

        <?php echo form_open_multipart('onboarding/upload'); ?>
            <div class="form-grid">
                <div class="field">
                    <label>Platform</label>
                    <select name="platform">
                        <option value="">Auto detect</option>
                        <option value="Stockbit">Stockbit</option>
                        <option value="Tring">Tring / Pegadaian</option>
                        <option value="Bibit">Bibit</option>
                        <option value="Bareksa">Bareksa</option>
                    </select>
                </div>
                <div class="field">
                    <label>Screenshot</label>
                    <input type="file" name="screenshot[]" accept="image/*" multiple required>
                    <div class="muted">Bisa pilih lebih dari satu gambar. Semua hasilnya akan digabung di halaman review.</div>
                </div>
            </div>
            <div class="actions" style="margin-top:18px">
                <button class="btn" type="submit">Upload & Review</button>
            </div>
        <?php echo form_close(); ?>
    </div>

    <div class="panel signal-card">
        <h3>Flow MVP</h3>
        <p class="muted">Screenshot tidak langsung masuk portfolio. User tetap pegang kontrol final. Parser awal sudah mendukung tabel portfolio Stockbit seperti contoh screenshot lo.</p>
        <ol class="flow-list">
            <li>Upload screenshot</li>
            <li>Review hasil baca</li>
            <li>Koreksi/tambah aset manual</li>
            <li>Simpan ke portfolio</li>
            <li>Planner kasih rekomendasi yang executable</li>
        </ol>
    </div>
</div>
