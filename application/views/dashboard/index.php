<?php
    $asset_type_labels = array(
        'emas' => 'Emas',
        'saham' => 'Saham',
        'reksa_dana' => 'Reksa Dana',
        'crypto' => 'Crypto',
        'properti' => 'Properti',
        'kas' => 'Kas',
        'rdn' => 'Saldo RDN',
        'lainnya' => 'Lainnya'
    );
    $asset_type_summary = array();
    $portfolio_group_summary = array();
    foreach ($summary['rows'] as $row) {
        $type = $row->type;
        if (!isset($asset_type_summary[$type])) {
            $asset_type_summary[$type] = array(
                'type' => $type,
                'label' => isset($asset_type_labels[$type]) ? $asset_type_labels[$type] : ucwords(str_replace('_', ' ', $type)),
                'market_value' => 0,
                'invested' => 0,
                'gain_loss' => 0,
                'count' => 0
            );
        }

        $asset_type_summary[$type]['market_value'] += (float) $row->market_value;
        $asset_type_summary[$type]['invested'] += (float) $row->invested_amount;
        $asset_type_summary[$type]['gain_loss'] += (float) $row->gain_loss;
        $asset_type_summary[$type]['count']++;

        if (!empty($row->portfolio_name)) {
            $group_key = ($row->platform ?: '-') . '|' . $row->portfolio_name;
            if (!isset($portfolio_group_summary[$group_key])) {
                $portfolio_group_summary[$group_key] = array(
                    'platform' => $row->platform,
                    'portfolio_name' => $row->portfolio_name,
                    'market_value' => 0,
                    'invested' => 0,
                    'gain_loss' => 0,
                    'count' => 0
                );
            }
            $portfolio_group_summary[$group_key]['market_value'] += (float) $row->market_value;
            $portfolio_group_summary[$group_key]['invested'] += (float) $row->invested_amount;
            $portfolio_group_summary[$group_key]['gain_loss'] += (float) $row->gain_loss;
            $portfolio_group_summary[$group_key]['count']++;
        }
    }

    $visible_type_summary = array_filter($asset_type_summary, function ($item) {
        return abs($item['market_value']) > 0 || abs($item['invested']) > 0 || abs($item['gain_loss']) > 0;
    });
    $display_type_summary = !empty($visible_type_summary) ? $visible_type_summary : $asset_type_summary;
    $gain_loss_type_summary = array_filter($display_type_summary, function ($item) {
        return !in_array($item['type'], array('kas', 'rdn'), true) && abs($item['gain_loss']) > 0;
    });
    $total_return_percent = $summary['invested'] > 0 ? ($summary['gain_loss'] / $summary['invested']) * 100 : 0;
    $asset_category_count = count($display_type_summary);
    $portfolio_goal_count = count($portfolio_group_summary);
    $hit_price_alerts = array();
    $watching_price_alerts = array();
    foreach (!empty($price_alerts) ? $price_alerts : array() as $alert_asset) {
        if (!empty($alert_asset->price_alert_triggered_at)) {
            $hit_price_alerts[] = $alert_asset;
        } else {
            $watching_price_alerts[] = $alert_asset;
        }
    }
?>

<span class="js-auto-market-sync"
      data-sync-url="<?php echo site_url('market/sync-all'); ?>"
      data-interval="60000"
      hidden></span>

<section class="dashboard-hero">
    <div class="dashboard-hero-main">
        <div class="eyebrow">Portfolio Command Center</div>
        <strong><?php echo rupiah($summary['total']); ?></strong>
        <span><?php echo number_format((int) $summary['asset_count'], 0, ',', '.'); ?> aset tercatat · <?php echo number_format($asset_category_count, 0, ',', '.'); ?> kategori</span>
    </div>
    <div class="dashboard-hero-stats">
        <div>
            <span>Modal</span>
            <strong><?php echo rupiah($summary['invested']); ?></strong>
        </div>
        <div>
            <span>Gain / loss</span>
            <strong class="<?php echo (float) $summary['gain_loss'] >= 0 ? 'positive' : 'negative'; ?>">
                <?php echo rupiah($summary['gain_loss']); ?>
            </strong>
        </div>
        <div>
            <span>Return</span>
            <strong class="<?php echo (float) $summary['gain_loss'] >= 0 ? 'positive' : 'negative'; ?>">
                <?php echo number_format($total_return_percent, 2, ',', '.'); ?>%
            </strong>
        </div>
    </div>
</section>

<section class="dashboard-kpi-grid">
    <div class="dashboard-kpi">
        <span>Aset Aktif</span>
        <strong><?php echo number_format((int) $summary['asset_count'], 0, ',', '.'); ?></strong>
    </div>
    <div class="dashboard-kpi">
        <span>Kategori</span>
        <strong><?php echo number_format($asset_category_count, 0, ',', '.'); ?></strong>
    </div>
    <div class="dashboard-kpi">
        <span>Goal / Portofolio</span>
        <strong><?php echo number_format($portfolio_goal_count, 0, ',', '.'); ?></strong>
    </div>
    <div class="dashboard-kpi">
        <span>Auto Market</span>
        <strong>1 menit</strong>
    </div>
</section>

<?php if (!empty($price_alerts)): ?>
    <section class="panel dashboard-card price-alert-panel">
        <div class="panel-head">
            <h3>Reminder Harga</h3>
            <span class="badge <?php echo !empty($hit_price_alerts) ? 'warning' : 'muted'; ?>">
                <?php echo !empty($hit_price_alerts) ? count($hit_price_alerts) . ' kena target' : count($watching_price_alerts) . ' dipantau'; ?>
            </span>
        </div>
        <div class="price-alert-grid">
            <?php foreach ($hit_price_alerts as $alert): ?>
                <div class="price-alert-card hit">
                    <div>
                        <span class="badge warning">Kena target</span>
                        <strong><?php echo html_escape($alert->symbol ?: $alert->name); ?></strong>
                        <small><?php echo html_escape($alert->name); ?> · <?php echo html_escape($asset_type_labels[$alert->type] ?? $alert->type); ?></small>
                    </div>
                    <div class="price-alert-values">
                        <span>Market <?php echo rupiah($alert->market_price); ?></span>
                        <strong>Target <?php echo $alert->price_alert_direction === 'above' ? '>=' : '<='; ?> <?php echo rupiah($alert->price_alert_target); ?></strong>
                    </div>
                    <a class="btn secondary" href="<?php echo site_url('aset/edit/' . $alert->id); ?>">Atur ulang</a>
                </div>
            <?php endforeach; ?>

            <?php foreach (array_slice($watching_price_alerts, 0, 4) as $alert): ?>
                <div class="price-alert-card">
                    <div>
                        <span class="badge muted">Dipantau</span>
                        <strong><?php echo html_escape($alert->symbol ?: $alert->name); ?></strong>
                        <small><?php echo html_escape($alert->name); ?> · <?php echo html_escape($asset_type_labels[$alert->type] ?? $alert->type); ?></small>
                    </div>
                    <div class="price-alert-values">
                        <span>Market <?php echo rupiah($alert->market_price); ?></span>
                        <strong>Target <?php echo $alert->price_alert_direction === 'above' ? '>=' : '<='; ?> <?php echo rupiah($alert->price_alert_target); ?></strong>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<section class="dashboard-grid">
    <div class="panel dashboard-card">
        <div class="panel-head">
            <h3>Total Modal Aset</h3>
            <span class="badge muted">Cost</span>
        </div>
        <?php if (empty($display_type_summary)): ?>
            <div class="empty">Belum ada modal aset yang tercatat.</div>
        <?php else: ?>
            <div class="category-list">
                <?php foreach ($display_type_summary as $item): ?>
                    <div class="category-row">
                        <div>
                            <strong><?php echo html_escape($item['label']); ?></strong>
                            <span><?php echo number_format((int) $item['count'], 0, ',', '.'); ?> aset</span>
                        </div>
                        <strong><?php echo rupiah($item['invested']); ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="panel dashboard-card">
        <div class="panel-head">
            <h3>Gain / Loss Per Kategori</h3>
            <span class="badge muted">Live</span>
        </div>
        <?php if (empty($gain_loss_type_summary)): ?>
            <div class="empty">Belum ada kategori aset yang punya gain/loss.</div>
        <?php else: ?>
            <div class="category-list">
                <?php foreach ($gain_loss_type_summary as $item): ?>
                    <?php
                        $percent = $item['invested'] > 0 ? ($item['gain_loss'] / $item['invested']) * 100 : 0;
                    ?>
                    <div class="category-row">
                        <div>
                            <strong><?php echo html_escape($item['label']); ?></strong>
                            <span><?php echo number_format((int) $item['count'], 0, ',', '.'); ?> aset · <?php echo number_format($percent, 2, ',', '.'); ?>%</span>
                        </div>
                        <strong class="<?php echo (float) $item['gain_loss'] >= 0 ? 'positive' : 'negative'; ?>">
                            <?php echo rupiah($item['gain_loss']); ?>
                        </strong>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="panel dashboard-card">
        <div class="panel-head">
            <h3>Komposisi Aset</h3>
            <span class="badge muted">Market</span>
        </div>
        <?php if (empty($display_type_summary)): ?>
            <div class="empty">Belum ada komposisi aset.</div>
        <?php else: ?>
            <div class="allocation-list">
                <?php foreach ($display_type_summary as $item): ?>
                    <?php $weight = $summary['total'] > 0 ? ($item['market_value'] / $summary['total']) * 100 : 0; ?>
                    <div class="allocation-row">
                        <div class="allocation-meta">
                            <span><?php echo html_escape($item['label']); ?></span>
                            <strong><?php echo rupiah($item['market_value']); ?></strong>
                        </div>
                        <div class="allocation-track">
                            <span style="width: <?php echo max(0, min(100, $weight)); ?>%"></span>
                        </div>
                        <div class="allocation-percent"><?php echo number_format($weight, 1, ',', '.'); ?>%</div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php if (!empty($portfolio_group_summary)): ?>
    <section class="panel dashboard-card goal-panel">
        <div class="panel-head">
            <h3>Portofolio / Goal</h3>
            <span class="badge muted">Grouping</span>
        </div>
        <div class="goal-grid">
            <?php foreach ($portfolio_group_summary as $goal): ?>
                <?php $percent = $goal['invested'] > 0 ? ($goal['gain_loss'] / $goal['invested']) * 100 : 0; ?>
                <div class="goal-card">
                    <div>
                        <strong><?php echo html_escape($goal['portfolio_name']); ?></strong>
                        <span><?php echo html_escape($goal['platform'] ?: 'Tanpa platform'); ?> · <?php echo number_format((int) $goal['count'], 0, ',', '.'); ?> aset</span>
                    </div>
                    <div class="goal-card-values">
                        <div>
                            <span>Total aset</span>
                            <strong><?php echo rupiah($goal['market_value']); ?></strong>
                        </div>
                        <div>
                            <span>Modal / AVG</span>
                            <strong><?php echo rupiah($goal['invested']); ?></strong>
                        </div>
                        <div>
                            <span>P/L</span>
                            <strong class="<?php echo (float) $goal['gain_loss'] >= 0 ? 'positive' : 'negative'; ?>">
                                <?php echo rupiah($goal['gain_loss']); ?>
                            </strong>
                            <span class="<?php echo (float) $goal['gain_loss'] >= 0 ? 'positive' : 'negative'; ?>">
                                <?php echo number_format($percent, 2, ',', '.'); ?>%
                            </span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<section class="dashboard-table-grid portfolio-full-grid">
    <div class="panel dashboard-card portfolio-panel">
        <div class="panel-head">
            <h3>Ringkasan Portofolio</h3>
            <span class="badge muted"><?php echo number_format((int) $summary['asset_count'], 0, ',', '.'); ?> aset</span>
        </div>
        <?php if (empty($summary['rows'])): ?>
            <div class="empty">Belum ada transaksi. Tambahkan transaksi pertama untuk mulai menghitung portofolio.</div>
        <?php else: ?>
            <div class="table-wrap market-table-wrap">
                <table class="market-table">
                    <thead>
                        <tr>
                            <th>Aset</th>
                            <th>Tipe</th>
                            <th>Qty</th>
                            <th class="text-right">AVG Kamu</th>
                            <th class="text-right">AVG Market</th>
                            <th class="text-right">Modal</th>
                            <th class="text-right">Nilai Market</th>
                            <th class="text-right">P&L</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($summary['rows'] as $row): ?>
                            <tr>
                                <td>
                                    <strong><?php echo html_escape($row->name); ?></strong>
                                    <?php if (!empty($row->portfolio_name)): ?>
                                        <div class="muted"><?php echo html_escape($row->platform ? $row->platform . ' · ' . $row->portfolio_name : $row->portfolio_name); ?></div>
                                    <?php elseif (!empty($row->platform)): ?>
                                        <div class="muted"><?php echo html_escape($row->platform); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><span class="type-pill"><?php echo html_escape(isset($asset_type_labels[$row->type]) ? $asset_type_labels[$row->type] : str_replace('_', ' ', $row->type)); ?></span></td>
                                <td>
                                    <?php if ($row->type === 'saham' && $row->unit === 'share'): ?>
                                        <?php echo number_format((float) $row->quantity_current / 100, 0, ',', '.'); ?> lot
                                        <span class="muted">(<?php echo number_format((float) $row->quantity_current, 0, ',', '.'); ?> lbr)</span>
                                    <?php elseif ($row->type === 'saham' && $row->unit === 'lot'): ?>
                                        <?php echo number_format((float) $row->quantity_current, 0, ',', '.'); ?> lot
                                        <span class="muted">(<?php echo number_format((float) $row->quantity_current * 100, 0, ',', '.'); ?> lbr)</span>
                                    <?php else: ?>
                                        <?php echo number_format((float) $row->quantity_current, 4, ',', '.'); ?> <?php echo html_escape($row->unit); ?>
                                    <?php endif; ?>
                                </td>
                                <td class="text-right">
                                    <?php echo $row->avg_price ? rupiah($row->avg_price) : '-'; ?>
                                    <?php if ($row->type === 'emas' && $row->unit === 'gram'): ?>
                                        <div class="muted">per 0,01g</div>
                                    <?php elseif ($row->type === 'reksa_dana'): ?>
                                        <div class="muted">harga beli/unit</div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-right">
                                    <?php echo $row->market_price ? rupiah($row->market_price) : '-'; ?>
                                    <?php if ($row->type === 'emas' && $row->unit === 'gram'): ?>
                                        <div class="muted">buyback / 0,01g</div>
                                    <?php elseif ($row->type === 'reksa_dana'): ?>
                                        <div class="muted">NAB/unit</div>
                                    <?php elseif ($row->type === 'saham'): ?>
                                        <div class="muted">last price</div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-right"><?php echo rupiah($row->invested_amount); ?></td>
                                <td class="text-right"><?php echo rupiah($row->market_value); ?></td>
                                <td class="text-right">
                                    <strong class="<?php echo (float) $row->gain_loss >= 0 ? 'positive' : 'negative'; ?>">
                                        <?php echo rupiah($row->gain_loss); ?>
                                    </strong>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
</section>

<section class="dashboard-bottom-grid">
    <div class="panel dashboard-card">
        <div class="panel-head">
            <h3>Transaksi Terakhir</h3>
            <span class="badge muted">Riwayat</span>
        </div>
        <?php if (empty($recent_transactions)): ?>
            <div class="empty">Belum ada transaksi tercatat.</div>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Aset</th>
                            <th>Tipe</th>
                            <th class="text-right">Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_transactions as $transaction): ?>
                            <tr>
                                <td><?php echo html_escape($transaction->transaction_date); ?></td>
                                <td><?php echo html_escape($transaction->asset_name); ?></td>
                                <td><?php echo strtoupper(html_escape($transaction->transaction_type)); ?></td>
                                <td class="text-right"><?php echo rupiah(($transaction->quantity * $transaction->price) + $transaction->fee); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>
