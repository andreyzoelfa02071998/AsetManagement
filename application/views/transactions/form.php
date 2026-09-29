<div class="panel">
    <?php echo form_open('transactions/create'); ?>
        <div class="form-grid">
            <div class="field">
                <label>Aset</label>
                <select name="asset_id" required>
                    <option value="">Pilih aset</option>
                    <?php foreach ($assets as $asset): ?>
                        <option value="<?php echo $asset->id; ?>" <?php echo set_select('asset_id', $asset->id); ?>>
                            <?php echo html_escape($asset->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label>Tanggal</label>
                <input type="date" name="transaction_date" value="<?php echo set_value('transaction_date', date('Y-m-d')); ?>" required>
            </div>
            <div class="field">
                <label>Tipe</label>
                <select name="transaction_type" required>
                    <option value="buy" <?php echo set_select('transaction_type', 'buy', TRUE); ?>>Beli</option>
                    <option value="sell" <?php echo set_select('transaction_type', 'sell'); ?>>Jual</option>
                </select>
            </div>
            <div class="field">
                <label>Qty</label>
                <input class="money-input" type="text" inputmode="decimal" name="quantity" value="<?php echo set_value('quantity'); ?>" required>
            </div>
            <div class="field">
                <label>Harga</label>
                <input class="money-input" type="text" inputmode="decimal" name="price" value="<?php echo set_value('price'); ?>" required>
            </div>
            <div class="field">
                <label>Fee</label>
                <input class="money-input" type="text" inputmode="decimal" name="fee" value="<?php echo set_value('fee', 0); ?>">
            </div>
            <div class="field full">
                <label>Catatan</label>
                <textarea name="notes"><?php echo set_value('notes'); ?></textarea>
            </div>
        </div>
        <div class="actions" style="margin-top:18px">
            <button class="btn" type="submit">Simpan</button>
            <a class="btn secondary" href="<?php echo site_url('transactions'); ?>">Batal</a>
        </div>
    <?php echo form_close(); ?>
</div>
