<div class="panel">
    <div class="topbar">
        <h3>Daftar Transaksi</h3>
        <a class="btn" href="<?php echo site_url('transactions/create'); ?>">Tambah Transaksi</a>
    </div>
    <?php if (empty($transactions)): ?>
        <div class="empty">Belum ada transaksi.</div>
    <?php else: ?>
        <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Aset</th>
                    <th>Tipe</th>
                    <th>Qty</th>
                    <th class="text-right">Harga</th>
                    <th class="text-right">Fee</th>
                    <th class="text-right">Nilai</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $transaction): ?>
                    <?php
                        $multiplier = (($transaction->asset_type === 'saham' && $transaction->asset_unit === 'lot') || ($transaction->asset_type === 'emas' && $transaction->asset_unit === 'gram')) ? 100 : 1;
                        $gross_value = (float) $transaction->quantity * $multiplier * (float) $transaction->price;
                        $net_value = $transaction->transaction_type === 'sell'
                            ? $gross_value - (float) $transaction->fee
                            : $gross_value + (float) $transaction->fee;
                    ?>
                    <tr>
                        <td><?php echo html_escape($transaction->transaction_date); ?></td>
                        <td><?php echo html_escape($transaction->asset_name); ?></td>
                        <td><?php echo strtoupper(html_escape($transaction->transaction_type)); ?></td>
                        <td>
                            <?php if ($transaction->asset_type === 'saham' && $transaction->asset_unit === 'share'): ?>
                                <?php echo number_format((float) $transaction->quantity / 100, 0, ',', '.'); ?> lot
                                <span class="muted">(<?php echo number_format((float) $transaction->quantity, 0, ',', '.'); ?> lbr)</span>
                            <?php elseif ($transaction->asset_type === 'saham' && $transaction->asset_unit === 'lot'): ?>
                                <?php echo number_format((float) $transaction->quantity, 0, ',', '.'); ?> lot
                                <span class="muted">(<?php echo number_format((float) $transaction->quantity * 100, 0, ',', '.'); ?> lbr)</span>
                            <?php else: ?>
                                <?php echo number_format((float) $transaction->quantity, 4, ',', '.'); ?>
                            <?php endif; ?>
                        </td>
                        <td class="text-right"><?php echo rupiah($transaction->price); ?></td>
                        <td class="text-right"><?php echo rupiah($transaction->fee); ?></td>
                        <td class="text-right"><?php echo rupiah($net_value); ?></td>
                        <td><a class="btn danger" href="<?php echo site_url('transactions/delete/' . $transaction->id); ?>" onclick="return confirm('Hapus transaksi ini?')">Hapus</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</div>
