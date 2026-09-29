<div class="panel">
    <?php $screenshot_exists = $batch->file_path && is_file(FCPATH . $batch->file_path); ?>
    <div class="panel-head">
        <h3>Review Hasil Import</h3>
        <?php if ($screenshot_exists): ?>
            <a class="btn secondary" href="<?php echo base_url($batch->file_path); ?>" target="_blank">Lihat Screenshot</a>
            <a class="btn secondary" href="<?php echo site_url('onboarding/reprocess/' . $batch->id); ?>">Proses ulang gambar</a>
        <?php endif; ?>
    </div>

    <?php if ($batch->file_path && !$screenshot_exists): ?>
        <div class="alert error">
            File screenshot batch ini tidak ditemukan di storage aplikasi. Upload ulang gambar supaya bisa dianalisa dari file asli.
        </div>
    <?php elseif (!$batch->file_path && $batch->source_type === 'screenshot'): ?>
        <div class="alert success">
            Screenshot sudah diproses dan file gambar otomatis dihapus dari storage aplikasi.
        </div>
    <?php endif; ?>

    <div class="processor-card">
        <div>
            <?php $processor_type = isset($batch->processor_type) ? $batch->processor_type : 'ocr'; ?>
            <span class="badge <?php echo $processor_type === 'ai' ? 'success' : 'muted'; ?>">
                <?php echo $processor_type === 'ai' ? 'Diproses pakai AI' : 'Diproses pakai OCR lokal'; ?>
            </span>
            <strong><?php echo html_escape(isset($batch->processor_name) && $batch->processor_name ? $batch->processor_name : 'OCR lokal'); ?></strong>
            <?php if (isset($batch->processor_model) && $batch->processor_model): ?>
                <small>Model: <?php echo html_escape($batch->processor_model); ?></small>
            <?php endif; ?>
        </div>
        <?php if (isset($batch->processor_notes) && $batch->processor_notes): ?>
            <p><?php echo html_escape($batch->processor_notes); ?></p>
        <?php endif; ?>
    </div>

    <p class="muted">Koreksi data yang salah, hapus isi nama untuk mengabaikan baris, atau pakai baris kosong terakhir untuk tambah aset manual.</p>

    <?php echo form_open('onboarding/save/' . $batch->id); ?>
        <div class="table-wrap import-review-wrap">
            <table class="import-review-table">
                <thead>
                    <tr>
                        <th>Tipe</th>
                        <th>Platform</th>
                        <th>Portofolio</th>
                        <th>Nama</th>
                        <th>Symbol</th>
                        <th>Qty</th>
                        <th>Unit</th>
                        <th>AVG</th>
                        <th>NAB / Market</th>
                        <th>Nilai Sekarang</th>
                        <th>Modal</th>
                        <th>Confidence</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $rows = $items;
                    $rows[] = (object) array(
                        'asset_type' => 'saham',
                        'platform' => '',
                        'portfolio_name' => '',
                        'name' => '',
                        'symbol' => '',
                        'quantity' => '',
                        'unit' => 'share',
                        'avg_price' => '',
                        'market_price' => '',
                        'invested_amount' => '',
                        'confidence' => 0,
                        'notes' => ''
                    );
                    ?>
                    <?php foreach ($rows as $item): ?>
                        <?php $confidence = isset($item->confidence) ? (int) $item->confidence : 0; ?>
                        <?php
                        $quantity = isset($item->quantity) ? (float) $item->quantity : 0;
                        $market_price = isset($item->market_price) ? (float) $item->market_price : 0;
                        $current_value = $quantity * $market_price;
                        if (isset($item->asset_type) && $item->asset_type === 'emas' && isset($item->unit) && $item->unit === 'gram') {
                            $current_value = $quantity * 100 * $market_price;
                        }
                        if (isset($item->unit) && $item->unit === 'idr') {
                            $current_value = $quantity;
                        }
                        ?>
                        <tr>
                            <td>
                                <select name="asset_type[]">
                                    <?php foreach ($types as $key => $label): ?>
                                        <option value="<?php echo $key; ?>" <?php echo $item->asset_type === $key ? 'selected' : ''; ?>><?php echo html_escape($label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><input class="input-md" type="text" name="platform[]" value="<?php echo html_escape($item->platform); ?>"></td>
                            <td><input class="input-md" type="text" name="portfolio_name[]" value="<?php echo html_escape(isset($item->portfolio_name) ? $item->portfolio_name : ''); ?>" placeholder="Tabungan Rumah"></td>
                            <td><input class="input-lg" type="text" name="name[]" value="<?php echo html_escape($item->name); ?>"></td>
                            <td><input class="input-sm" type="text" name="symbol[]" value="<?php echo html_escape($item->symbol); ?>"></td>
                            <td><input class="input-md money-input" type="text" inputmode="decimal" name="quantity[]" value="<?php echo html_escape(angka_input($item->quantity, 4)); ?>"></td>
                            <td>
                                <select name="unit[]">
                                    <?php foreach ($units as $key => $label): ?>
                                        <option value="<?php echo $key; ?>" <?php echo $item->unit === $key ? 'selected' : ''; ?>><?php echo html_escape($label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><input class="input-md money-input" type="text" inputmode="decimal" name="avg_price[]" value="<?php echo html_escape(angka_input($item->avg_price, $item->asset_type === 'reksa_dana' ? 4 : 2)); ?>"></td>
                            <td><input class="input-md money-input" type="text" inputmode="decimal" name="market_price[]" value="<?php echo html_escape(angka_input($item->market_price, $item->asset_type === 'reksa_dana' ? 4 : 0)); ?>"></td>
                            <td><input class="input-md" type="text" value="<?php echo html_escape(rupiah($current_value)); ?>" readonly></td>
                            <td><input class="input-md money-input" type="text" inputmode="decimal" name="invested_amount[]" value="<?php echo html_escape(angka_input($item->invested_amount, 0)); ?>"></td>
                            <td><span class="badge <?php echo $confidence >= 80 ? 'success' : 'warning'; ?>"><?php echo $confidence; ?>%</span></td>
                            <td><textarea class="input-notes" name="notes[]"><?php echo html_escape($item->notes); ?></textarea></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="actions" style="margin-top:18px">
            <button class="btn" type="submit">Simpan ke Portfolio</button>
            <a class="btn secondary" href="<?php echo site_url('onboarding'); ?>">Batal</a>
        </div>
    <?php echo form_close(); ?>
</div>
