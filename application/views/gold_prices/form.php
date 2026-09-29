<div class="panel">
    <?php echo form_open('gold_prices/create'); ?>
        <div class="form-grid">
            <div class="field">
                <label>Tanggal</label>
                <input type="date" name="price_date" value="<?php echo set_value('price_date', date('Y-m-d')); ?>" required>
            </div>
            <div class="field">
                <label>Harga user beli / hargaJual Tring per 0,01g</label>
                <input class="money-input" type="text" inputmode="decimal" name="price" value="<?php echo set_value('price', angka_input(24570, 0)); ?>" required>
            </div>
            <div class="field">
                <label>Harga user jual / buyback per 0,01g</label>
                <input class="money-input" type="text" inputmode="decimal" name="buy_price" value="<?php echo set_value('buy_price'); ?>" placeholder="Contoh: 23.380">
            </div>
            <div class="field">
                <label>Sumber</label>
                <input type="text" name="source" value="<?php echo set_value('source', 'manual'); ?>">
            </div>
            <div class="field full">
                <label>Catatan</label>
                <textarea name="notes"><?php echo set_value('notes'); ?></textarea>
            </div>
        </div>
        <div class="actions" style="margin-top:18px">
            <button class="btn" type="submit">Simpan</button>
            <a class="btn secondary" href="<?php echo site_url('gold_prices'); ?>">Batal</a>
        </div>
    <?php echo form_close(); ?>
</div>
