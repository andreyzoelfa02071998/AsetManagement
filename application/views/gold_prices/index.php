<?php
$type_labels = $this->Asset_model->types();
$signal = $this->Gold_price_model->signal($latest, $target_price);
$rows = isset($portfolio['rows']) ? $portfolio['rows'] : array();
?>

<section class="grid two">
    <div class="panel signal-card">
        <div class="panel-head">
            <h3>Market Portfolio</h3>
            <span class="badge success"><?php echo count($rows); ?> aset</span>
        </div>
        <div class="metric">
            Nilai market total
            <strong><?php echo rupiah(isset($portfolio['total']) ? $portfolio['total'] : 0); ?></strong>
        </div>
        <div class="mini-breakdown">
            <div class="mini-breakdown-row">
                <span>Modal</span>
                <strong><?php echo rupiah(isset($portfolio['invested']) ? $portfolio['invested'] : 0); ?></strong>
            </div>
            <div class="mini-breakdown-row">
                <span>Gain / Loss</span>
                <?php $gain = isset($portfolio['gain_loss']) ? (float) $portfolio['gain_loss'] : 0; ?>
                <strong class="<?php echo $gain >= 0 ? 'positive' : 'negative'; ?>"><?php echo rupiah($gain); ?></strong>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head">
            <h3>Aksi Market</h3>
            <span class="badge muted">Auto/manual sync</span>
        </div>
        <div class="actions">
            <a class="btn secondary js-market-update" href="<?php echo site_url('market/sync-all'); ?>">Update Semua</a>
            <a class="btn secondary js-market-update" href="<?php echo site_url('market/sync-stocks'); ?>">Update Saham IDX</a>
            <a class="btn secondary js-market-update" href="<?php echo site_url('market/sync-tring'); ?>">Update Tring</a>
            <a class="btn" href="<?php echo site_url('gold_prices/create'); ?>">Input Harga Emas</a>
        </div>
        <p class="muted" style="margin-top:12px">Saham memakai Yahoo Finance IDX, emas memakai Tring/Pegadaian jika tersedia. Reksadana masih mengikuti data import/manual terakhir.</p>
    </div>
</section>

<section class="dashboard-grid">
    <div class="panel dashboard-card">
        <div class="panel-head">
            <h3>Ringkasan Per Jenis Aset</h3>
            <span class="badge muted">Market value</span>
        </div>
        <?php if (empty($market_groups)): ?>
            <div class="empty">Belum ada aset untuk diringkas.</div>
        <?php else: ?>
            <div class="category-list">
                <?php foreach ($market_groups as $group): ?>
                    <?php $group_gain = (float) $group['gain_loss']; ?>
                    <div class="category-row">
                        <div>
                            <strong><?php echo html_escape(isset($type_labels[$group['type']]) ? $type_labels[$group['type']] : $group['type']); ?></strong>
                            <span><?php echo (int) $group['count']; ?> aset · modal <?php echo rupiah($group['invested_amount']); ?></span>
                        </div>
                        <div class="text-right">
                            <strong><?php echo rupiah($group['market_value']); ?></strong>
                            <span class="<?php echo $group_gain >= 0 ? 'positive' : 'negative'; ?>"><?php echo rupiah($group_gain); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="panel dashboard-card">
        <div class="panel-head">
            <h3>Berita Penunjang Investasi</h3>
            <span class="badge muted">Google News</span>
        </div>
        <?php if (empty($news)): ?>
            <div class="empty">Berita belum bisa dimuat. Coba refresh halaman Market nanti.</div>
        <?php else: ?>
            <div class="news-list">
                <?php foreach ($news as $item): ?>
                    <a class="news-item" href="<?php echo html_escape($item['link']); ?>" target="_blank" rel="noopener">
                        <strong><?php echo html_escape($item['title']); ?></strong>
                        <span><?php echo html_escape($item['source']); ?> · <?php echo html_escape($item['published_at']); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="panel" style="margin-top:18px">
    <div class="panel-head">
        <h3>Harga Market Aset yang Dipunya</h3>
        <span class="badge muted">Live + manual</span>
    </div>
    <?php if (empty($rows)): ?>
        <div class="empty">Belum ada aset.</div>
    <?php else: ?>
        <div class="table-wrap market-table-wrap">
            <table class="market-table">
                <thead>
                    <tr>
                        <th>Aset</th>
                        <th>Tipe</th>
                        <th>Platform</th>
                        <th class="text-right">Qty</th>
                        <th class="text-right">Harga Market</th>
                        <th class="text-right">Nilai Market</th>
                        <th class="text-right">Gain / Loss</th>
                        <th>Catatan Market</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $asset): ?>
                        <?php
                        $asset_gain = isset($asset->gain_loss) ? (float) $asset->gain_loss : 0;
                        $note = 'Manual/import terakhir';
                        if ($asset->type === 'saham') {
                            $note = 'Update IDX/Yahoo Finance';
                        } elseif ($asset->type === 'emas') {
                            $note = 'Valuasi pakai buyback Tring/Pegadaian';
                        } elseif ($asset->type === 'rdn' || $asset->type === 'kas') {
                            $note = 'Saldo tetap, tanpa gain/loss market';
                        }
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo html_escape($asset->name); ?></strong>
                                <?php if (!empty($asset->symbol)): ?>
                                    <div class="muted"><?php echo html_escape($asset->symbol); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($asset->portfolio_name)): ?>
                                    <div class="muted"><?php echo html_escape($asset->portfolio_name); ?></div>
                                <?php endif; ?>
                            </td>
                            <td><span class="type-pill"><?php echo html_escape(isset($type_labels[$asset->type]) ? $type_labels[$asset->type] : $asset->type); ?></span></td>
                            <td><?php echo html_escape($asset->platform); ?></td>
                            <td class="text-right">
                                <?php if ($asset->type === 'saham' && $asset->unit === 'share'): ?>
                                    <?php echo number_format((float) $asset->quantity_current / 100, 0, ',', '.'); ?> lot
                                    <span class="muted">(<?php echo number_format((float) $asset->quantity_current, 0, ',', '.'); ?> lbr)</span>
                                <?php elseif ($asset->type === 'saham' && $asset->unit === 'lot'): ?>
                                    <?php echo number_format((float) $asset->quantity_current, 0, ',', '.'); ?> lot
                                    <span class="muted">(<?php echo number_format((float) $asset->quantity_current * 100, 0, ',', '.'); ?> lbr)</span>
                                <?php else: ?>
                                    <?php echo number_format((float) $asset->quantity_current, 4, ',', '.'); ?> <?php echo html_escape($asset->unit); ?>
                                <?php endif; ?>
                            </td>
                            <td class="text-right"><?php echo $asset->market_price ? rupiah($asset->market_price) : '-'; ?></td>
                            <td class="text-right"><?php echo rupiah($asset->market_value); ?></td>
                            <td class="text-right"><span class="<?php echo $asset_gain >= 0 ? 'positive' : 'negative'; ?>"><?php echo rupiah($asset_gain); ?></span></td>
                            <td><?php echo html_escape($note); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<section class="grid two" style="margin-top:18px">
    <div class="panel">
        <div class="panel-head">
            <h3>Status Emas</h3>
            <span class="badge <?php echo $signal['class']; ?>"><?php echo html_escape($signal['label']); ?></span>
        </div>
        <p class="muted">Target beli: <?php echo rupiah($target_price); ?> / 0,01g</p>
        <p>Harga user beli <strong><?php echo $latest ? rupiah($latest->price) : '-'; ?></strong> / 0,01g</p>
        <p>Harga user jual <strong><?php echo $latest && isset($latest->buy_price) ? rupiah($latest->buy_price) : '-'; ?></strong> / 0,01g</p>
    </div>

    <div class="panel">
        <h3>Riwayat Harga Emas</h3>
        <?php if (empty($prices)): ?>
            <div class="empty">Belum ada data harga emas.</div>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th class="text-right">Harga user beli</th>
                            <th class="text-right">Harga user jual</th>
                            <th>Sumber</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($prices, 0, 8) as $price): ?>
                            <tr>
                                <td><?php echo html_escape($price->price_date); ?></td>
                                <td class="text-right"><?php echo rupiah($price->price); ?></td>
                                <td class="text-right"><?php echo isset($price->buy_price) && $price->buy_price ? rupiah($price->buy_price) : '-'; ?></td>
                                <td><?php echo html_escape($price->source); ?></td>
                                <td><a class="btn danger" href="<?php echo site_url('gold_prices/delete/' . $price->id); ?>" onclick="return confirm('Hapus harga ini?')">Hapus</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>
