<?php
$is_edit = isset($asset) && $asset;
$price_decimals = $is_edit && $asset->type === 'reksa_dana' ? 4 : 2;
$market_decimals = $is_edit && $asset->type === 'reksa_dana' ? 4 : 0;
?>
<div class="panel">
    <?php echo form_open($is_edit ? 'aset/edit/' . $asset->id : 'aset/create'); ?>
        <div class="form-grid">
            <div class="field">
                <label>Nama aset</label>
                <input type="text" name="name" value="<?php echo set_value('name', $is_edit ? $asset->name : ''); ?>" required>
            </div>
            <div class="field">
                <label>Tipe aset</label>
                <select class="js-asset-type" name="type" required>
                    <?php foreach ($types as $key => $label): ?>
                        <option value="<?php echo $key; ?>" <?php echo set_select('type', $key, $is_edit && $asset->type === $key); ?>>
                            <?php echo html_escape($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label>Symbol</label>
                <input type="text" name="symbol" value="<?php echo set_value('symbol', $is_edit ? $asset->symbol : ''); ?>">
            </div>
            <div class="field">
                <label>Platform</label>
                <input type="text" name="platform" value="<?php echo set_value('platform', $is_edit ? $asset->platform : ''); ?>" placeholder="Tring, Stockbit, Bibit">
            </div>
            <div class="field">
                <label>Portofolio / Goal</label>
                <input type="text" name="portfolio_name" value="<?php echo set_value('portfolio_name', $is_edit && isset($asset->portfolio_name) ? $asset->portfolio_name : ''); ?>" placeholder="Tabungan Rumah, Dana Darurat">
            </div>
            <div class="field">
                <label>Jumlah saat ini</label>
                <input class="money-input" type="text" inputmode="decimal" name="quantity_current" value="<?php echo set_value('quantity_current', $is_edit ? angka_input($asset->quantity_current, 4) : ''); ?>">
            </div>
            <div class="field">
                <label>Unit</label>
                <select name="unit">
                    <?php foreach ($units as $key => $label): ?>
                        <option value="<?php echo $key; ?>" <?php echo set_select('unit', $key, $is_edit && $asset->unit === $key); ?>>
                            <?php echo html_escape($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label>AVG/modal per unit</label>
                <input class="money-input" type="text" inputmode="decimal" name="avg_price" value="<?php echo set_value('avg_price', $is_edit ? angka_input($asset->avg_price, $price_decimals) : ''); ?>">
            </div>
            <div class="field">
                <label>Harga market sekarang</label>
                <input class="money-input" type="text" inputmode="decimal" name="market_price" value="<?php echo set_value('market_price', $is_edit ? angka_input($asset->market_price, $market_decimals) : ''); ?>">
            </div>
            <div class="field">
                <label>Target beli</label>
                <input class="money-input" type="text" inputmode="decimal" name="target_buy_price" value="<?php echo set_value('target_buy_price', $is_edit ? angka_input($asset->target_buy_price, 0) : ''); ?>" placeholder="Contoh: 24.000">
            </div>
            <div class="field">
                <label>Reminder harga</label>
                <select name="price_alert_enabled">
                    <option value="1" <?php echo set_select('price_alert_enabled', '1', $is_edit && isset($asset->price_alert_enabled) && (int) $asset->price_alert_enabled === 1); ?>>Aktifkan reminder</option>
                    <option value="0" <?php echo set_select('price_alert_enabled', '0', !$is_edit || !isset($asset->price_alert_enabled) || (int) $asset->price_alert_enabled === 0); ?>>Tidak aktif</option>
                </select>
            </div>
            <div class="field">
                <label>Target reminder</label>
                <input class="money-input" type="text" inputmode="decimal" name="price_alert_target" value="<?php echo set_value('price_alert_target', $is_edit && isset($asset->price_alert_target) ? angka_input($asset->price_alert_target, 4) : ''); ?>" placeholder="Contoh: 6.000">
            </div>
            <div class="field">
                <label>Kondisi reminder</label>
                <select name="price_alert_direction">
                    <option value="below" <?php echo set_select('price_alert_direction', 'below', !$is_edit || !isset($asset->price_alert_direction) || $asset->price_alert_direction === 'below'); ?>>Harga <= target (mau beli)</option>
                    <option value="above" <?php echo set_select('price_alert_direction', 'above', $is_edit && isset($asset->price_alert_direction) && $asset->price_alert_direction === 'above'); ?>>Harga >= target (take profit)</option>
                </select>
            </div>
            <div class="field">
                <label>Status reminder</label>
                <?php if ($is_edit && isset($asset->price_alert_enabled) && (int) $asset->price_alert_enabled && !empty($asset->price_alert_triggered_at)): ?>
                    <div class="alert-inline hit">Target sudah kena pada <?php echo html_escape($asset->price_alert_triggered_at); ?></div>
                <?php else: ?>
                    <div class="alert-inline">Isi target supaya dashboard ngasih tanda saat harga market masuk area yang ditunggu.</div>
                <?php endif; ?>
            </div>
            <div class="field">
                <label>Modal tertanam</label>
                <input class="money-input" type="text" inputmode="decimal" name="invested_amount" value="<?php echo set_value('invested_amount', $is_edit ? angka_input($asset->invested_amount, 0) : ''); ?>">
            </div>
            <div class="field">
                <label>Lot size/min unit</label>
                <input type="number" step="1" name="lot_size" value="<?php echo set_value('lot_size', $is_edit ? $asset->lot_size : 1); ?>">
            </div>
            <div class="field">
                <label>Minimum pembelian</label>
                <input class="money-input" type="text" inputmode="decimal" name="min_purchase_amount" value="<?php echo set_value('min_purchase_amount', $is_edit ? angka_input($asset->min_purchase_amount, 0) : 0); ?>">
            </div>
            <div class="field">
                <label>Target alokasi (%)</label>
                <input type="number" step="0.01" name="target_allocation" value="<?php echo set_value('target_allocation', $is_edit ? $asset->target_allocation : 0); ?>">
            </div>
            <div class="field">
                <label>Masukkan ke planner</label>
                <select name="is_planned">
                    <option value="1" <?php echo set_select('is_planned', '1', !$is_edit || (int) $asset->is_planned === 1); ?>>Ya, dipakai rekomendasi</option>
                    <option value="0" <?php echo set_select('is_planned', '0', $is_edit && (int) $asset->is_planned === 0); ?>>Tidak, skip dari planner</option>
                </select>
            </div>
            <div class="field full">
                <label>Catatan</label>
                <textarea name="notes" placeholder="Untuk Saldo RDN, isi nominal saldo di Jumlah saat ini. Unit/harga otomatis dianggap Rupiah."><?php echo set_value('notes', $is_edit ? $asset->notes : ''); ?></textarea>
            </div>
        </div>
        <div class="actions" style="margin-top:18px">
            <button class="btn" type="submit">Simpan</button>
            <a class="btn secondary" href="<?php echo site_url('aset'); ?>">Batal</a>
        </div>
    <?php echo form_close(); ?>
</div>
