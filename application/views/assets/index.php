<div class="panel">
    <div class="topbar">
        <h3>Daftar Aset</h3>
        <div class="actions">
            <a class="btn secondary js-market-update" href="<?php echo site_url('market/sync-stocks'); ?>">Update Saham IDX</a>
            <a class="btn" href="<?php echo site_url('aset/create'); ?>">Tambah Aset</a>
        </div>
    </div>
    <?php if (empty($assets)): ?>
        <div class="empty">Belum ada aset.</div>
    <?php else: ?>
        <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Tipe</th>
                    <th>Symbol</th>
                    <th>Platform</th>
                    <th>Portofolio</th>
                    <th>Plan</th>
                    <th>Qty</th>
                    <th class="text-right">Harga Market</th>
                    <th class="text-right">Nilai Market</th>
                    <th class="text-right">Target Beli</th>
                    <th>Reminder</th>
                    <th>Catatan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($assets as $asset): ?>
                    <tr>
                        <td><?php echo html_escape($asset->name); ?></td>
                        <td><?php echo html_escape(isset($types[$asset->type]) ? $types[$asset->type] : $asset->type); ?></td>
                        <td><?php echo html_escape($asset->symbol); ?></td>
                        <td><?php echo html_escape($asset->platform); ?></td>
                        <td><?php echo !empty($asset->portfolio_name) ? html_escape($asset->portfolio_name) : '-'; ?></td>
                        <td>
                            <a class="badge <?php echo (int) $asset->is_planned ? 'success' : 'muted'; ?>" href="<?php echo site_url('aset/toggle-plan/' . $asset->id); ?>">
                                <?php echo (int) $asset->is_planned ? 'Dipakai' : 'Skip'; ?>
                            </a>
                        </td>
                        <td><?php echo number_format((float) $asset->quantity_current, 4, ',', '.'); ?> <?php echo html_escape($asset->unit); ?></td>
                        <td class="text-right"><?php echo $asset->market_price ? rupiah($asset->market_price) : '-'; ?></td>
                        <td class="text-right"><?php echo isset($asset->market_value) && $asset->market_value ? rupiah($asset->market_value) : rupiah($this->Asset_model->calculate_market_value((array) $asset)); ?></td>
                        <td class="text-right"><?php echo $asset->target_buy_price ? rupiah($asset->target_buy_price) : '-'; ?></td>
                        <td>
                            <?php if (isset($asset->price_alert_enabled) && (int) $asset->price_alert_enabled && (float) $asset->price_alert_target > 0): ?>
                                <span class="badge <?php echo !empty($asset->price_alert_triggered_at) ? 'warning' : 'muted'; ?>">
                                    <?php echo !empty($asset->price_alert_triggered_at) ? 'Kena target' : 'Aktif'; ?>
                                </span>
                                <div class="muted">
                                    Harga <?php echo $asset->price_alert_direction === 'above' ? '>=' : '<='; ?>
                                    <?php echo rupiah($asset->price_alert_target); ?>
                                </div>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td><?php echo html_escape($asset->notes); ?></td>
                        <td>
                            <div class="actions">
                                <a class="btn secondary" href="<?php echo site_url('aset/edit/' . $asset->id); ?>">Edit</a>
                                <a class="btn danger" href="<?php echo site_url('aset/delete/' . $asset->id); ?>" onclick="return confirm('Hapus aset ini?')">Hapus</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</div>
