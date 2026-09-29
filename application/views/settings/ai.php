<div class="panel">
    <?php
    $model_groups = array(
        'chatgpt' => array(
            '__info_chatgpt_paid' => 'Tidak ada model ChatGPT API yang Free [Info]',
            'gpt-4o-mini' => 'GPT-4o Mini - cepat & hemat [Berbayar]',
            'gpt-4o' => 'GPT-4o - akurasi tinggi [Berbayar]',
            'gpt-4.1-mini' => 'GPT-4.1 Mini - alternatif [Berbayar]'
        ),
        'gemini' => array(
            'gemini-3.8-flash' => 'Gemini 3.8 Flash - terbaru [Berbayar]',
            'gemini-flash-latest' => 'Gemini Flash Latest - direkomendasikan [Free tier / Berbayar]',
            'gemini-3.7-flash' => 'Gemini 3.7 Flash - terbaru [Berbayar]',
            'gemini-3.6-flash' => 'Gemini 3.6 Flash - stabil [Free tier / Berbayar]',
            'gemini-3.5-flash' => 'Gemini 3.5 Flash - kompatibel [Free tier / Berbayar]',
            'gemini-3.1-flash-lite' => 'Gemini 3.1 Flash Lite - ringan [Free tier / Berbayar]',
            'gemini-2.5-pro' => 'Gemini 2.5 Pro - akurasi tinggi [Berbayar]'
        ),
        'claude' => array(
            'claude-sonnet-4-5' => 'Claude Sonnet 4.5 - teliti [Berbayar]',
            'claude-haiku-4-5' => 'Claude Haiku 4.5 - lebih hemat [Berbayar]',
            'claude-opus-4-1' => 'Claude Opus 4.1 - premium [Berbayar]'
        ),
        'qwen' => array(
            'qwen/qwen3.8-27b:free' => 'Qwen3.8 27B - vision [Free]',
            'qwen/qwen2.5-vl-7b-instruct:free' => 'Qwen2.5 VL 7B - vision ringan [Free]',
            'qwen/qwen3-vl-235b-a22b-instruct' => 'Qwen3 VL 235B - kuat [Berbayar]'
        ),
        'llama' => array(
            'meta-llama/llama-3.2-90b-vision-instruct:free' => 'Llama 3.2 90B Vision [Free]',
            'meta-llama/llama-3.2-11b-vision-instruct:free' => 'Llama 3.2 11B Vision [Free]'
        ),
        'custom' => array(
            'gpt-4o-mini' => 'OpenAI-compatible - Mini vision [Cek provider]',
            'gpt-4o' => 'OpenAI-compatible - Akurasi tinggi [Cek provider]',
            'google/gemini-2.5-flash' => 'Gemini-compatible [Cek provider]',
            'anthropic/claude-sonnet-4' => 'Claude-compatible [Cek provider]'
        )
    );
    ?>
    <?php echo form_open('settings/ai/save'); ?>
        <div class="form-grid ai-settings-form">
            <div class="field full">
                <label>
                    <input type="checkbox" name="enabled" value="1" <?php echo (int) $settings->enabled === 1 ? 'checked' : ''; ?> style="width:auto;min-height:auto;margin-right:8px">
                    Aktifkan AI untuk analisa gambar
                </label>
                <p class="muted">Kalau AI gagal atau belum aktif, import tetap pakai OCR/parser lokal sebagai cadangan.</p>
            </div>

            <div class="field">
                <label>AI yang dipakai</label>
                <select class="js-ai-provider" name="provider">
                    <option value="chatgpt" <?php echo in_array($settings->provider, array('chatgpt', 'openai'), true) ? 'selected' : ''; ?>>ChatGPT</option>
                    <option value="gemini" <?php echo in_array($settings->provider, array('gemini', 'openrouter'), true) ? 'selected' : ''; ?>>Gemini</option>
                    <option value="claude" <?php echo $settings->provider === 'claude' ? 'selected' : ''; ?>>Claude</option>
                    <option value="qwen" <?php echo $settings->provider === 'qwen' ? 'selected' : ''; ?>>Qwen</option>
                    <option value="llama" <?php echo $settings->provider === 'llama' ? 'selected' : ''; ?>>Llama</option>
                    <option value="custom" <?php echo $settings->provider === 'custom' ? 'selected' : ''; ?>>Custom OpenAI-compatible</option>
                </select>
            </div>

            <div class="field">
                <label>
                    Model
                    <span class="help-tip" tabindex="0" data-tooltip="Pilih model yang support vision/gambar. Label Free/Berbayar mengikuti info provider dan bisa berubah.">?</span>
                </label>
                <select class="js-ai-model" name="model" data-current="<?php echo html_escape($settings->model); ?>">
                    <?php foreach ($model_groups as $provider => $models): ?>
                        <?php foreach ($models as $value => $label): ?>
                            <option value="<?php echo html_escape($value); ?>" data-provider="<?php echo html_escape($provider); ?>" <?php echo strpos($value, '__info_') === 0 ? 'disabled' : ''; ?> <?php echo $settings->model === $value ? 'selected' : ''; ?>>
                                <?php echo html_escape($label); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </select>
                <p class="muted js-ai-model-note">Label Free/Berbayar mengikuti layanan AI. Tetap cek limit dan billing di halaman token sebelum dipakai.</p>
            </div>

            <div class="field full">
                <label>
                    API Key
                    <span class="help-tip js-ai-token-help" tabindex="0" data-tooltip="Pilih AI dulu, lalu buka link token di bawah. Buat key baru, salin tokennya, lalu tempel di field ini.">?</span>
                </label>
                <input type="password" name="api_key" value="<?php echo html_escape($settings->api_key); ?>" autocomplete="off" placeholder="Masukkan API key provider">
                <p class="muted ai-token-links">
                    <a class="js-ai-token-link" href="https://platform.openai.com/api-keys" target="_blank" rel="noopener">Ambil token provider</a>
                </p>
            </div>

            <div class="field full">
                <label>
                    Base URL Custom
                    <span class="help-tip" tabindex="0" data-tooltip="Isi hanya kalau pilihan AI Custom. Contoh: https://api.openai.com/v1 atau endpoint kompatibel lain yang punya /chat/completions.">?</span>
                </label>
                <input type="text" name="base_url" value="<?php echo html_escape($settings->base_url); ?>" placeholder="https://provider.com/v1">
                <p class="muted">Dipakai hanya untuk Custom OpenAI-compatible. ChatGPT, Gemini, dan Claude memakai endpoint resmi masing-masing.</p>
            </div>
        </div>

        <div class="actions" style="margin-top:18px">
            <button class="btn" type="submit">Simpan AI Settings</button>
        </div>
    <?php echo form_close(); ?>
</div>
