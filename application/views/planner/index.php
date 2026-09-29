<section class="grid two">
    <div class="panel">
        <div class="panel-head">
            <h3>Plan Suggest</h3>
            <span class="badge success">AI assisted</span>
        </div>
        <form method="post" action="<?php echo site_url('planner/calculate'); ?>">
            <div class="form-grid">
                <div class="field full">
                    <label>Budget yang mau diplan</label>
                    <input class="money-input" type="text" name="budget" value="<?php echo $budget !== null ? angka_input($budget) : ''; ?>" placeholder="Contoh: 1.000.000" required>
                </div>
            </div>
            <div class="actions" style="margin-top:16px">
                <button class="btn" type="submit">Hitung Plan</button>
                <a class="btn secondary js-market-update" href="<?php echo site_url('market/sync-stocks'); ?>">Update Saham IDX</a>
                <a class="btn secondary js-market-update" href="<?php echo site_url('market/sync-tring'); ?>">Update Tring</a>
            </div>
        </form>
    </div>

    <div class="panel">
        <h3>Cara Planner Membaca Aset</h3>
        <p class="muted">
            Planner memakai aset investasi yang flag Plan-nya aktif: saham, emas, reksadana, atau tipe investasi lain yang lo punya.
            Rekomendasi dihitung dari target komposisi, nilai market saat ini, harga terbaru, minimum pembelian, saldo RDN, dan efek AVG setelah beli.
        </p>
        <p class="muted">
            Saldo RDN dipakai sebagai modal untuk saham/reksadana. Emas hanya memakai budget plan baru.
        </p>
    </div>
</section>

<?php if ($recommendation): ?>
    <?php
        $summary = $recommendation['summary'];
        $plan = $recommendation['plan'];
        $items = $plan['items'];
        $candidates = $recommendation['candidates'];
        $ai = isset($recommendation['ai']) ? $recommendation['ai'] : array();
    ?>

    <section class="grid three" style="margin-top:18px">
        <div class="panel signal-card">
            <div class="metric">
                Dana diplan
                <strong><?php echo rupiah($budget); ?></strong>
            </div>
        </div>
        <div class="panel">
            <div class="metric">
                Dipakai dari budget
                <strong><?php echo rupiah(isset($plan['budget_used']) ? $plan['budget_used'] : $plan['total_allocated']); ?></strong>
            </div>
        </div>
        <div class="panel">
            <div class="metric">
                Dipakai dari RDN
                <strong><?php echo rupiah(isset($plan['rdn_used']) ? $plan['rdn_used'] : 0); ?></strong>
            </div>
        </div>
    </section>

    <section class="grid three" style="margin-top:18px">
        <div class="panel">
            <div class="metric">
                Buying power saham/RD
                <strong><?php echo rupiah($summary['stock_fund_buying_power']); ?></strong>
            </div>
        </div>
        <div class="panel">
            <div class="metric">
                Dialokasikan total
                <strong><?php echo rupiah($plan['total_allocated']); ?></strong>
            </div>
        </div>
        <div class="panel">
            <div class="metric">
                Sisa dana
                <strong><?php echo rupiah($plan['remaining']); ?></strong>
                <span class="muted">RDN sisa <?php echo rupiah(isset($plan['rdn_remaining']) ? $plan['rdn_remaining'] : 0); ?></span>
            </div>
        </div>
    </section>

    <section class="panel" style="margin-top:18px">
        <div class="panel-head">
            <h3>Analisa AI Planner</h3>
            <span class="badge <?php echo !empty($ai['enabled']) ? 'success' : 'muted'; ?>">
                <?php echo !empty($ai['enabled']) ? 'AI aktif' : 'Rule fallback'; ?>
            </span>
        </div>
        <div class="planner-ai-grid">
            <div>
                <div class="metric">Risk Level <strong><?php echo html_escape(isset($ai['risk_level']) ? $ai['risk_level'] : '-'); ?></strong></div>
            </div>
            <div>
                <strong><?php echo html_escape(isset($ai['headline']) ? $ai['headline'] : ''); ?></strong>
                <p class="muted"><?php echo html_escape(isset($ai['strategy']) ? $ai['strategy'] : ''); ?></p>
                <?php if (!empty($ai['market_view'])): ?>
                    <p class="muted"><?php echo html_escape($ai['market_view']); ?></p>
                <?php endif; ?>
                <?php if (!empty($ai['cash_policy'])): ?>
                    <p class="muted"><?php echo html_escape($ai['cash_policy']); ?></p>
                <?php endif; ?>
                <?php if (!empty($ai['warnings'])): ?>
                    <ul class="flow-list">
                        <?php foreach ($ai['warnings'] as $warning): ?>
                            <?php if (trim((string) $warning) !== ''): ?>
                                <li><?php echo html_escape($warning); ?></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="panel" style="margin-top:18px">
        <div class="panel-head">
            <h3>Komposisi Ideal Budget Ini</h3>
            <span class="badge success"><?php echo count($items); ?> aset dipilih</span>
        </div>

        <?php if (empty($items)): ?>
            <div class="empty">Belum ada aset yang feasible untuk budget ini. Coba naikkan budget atau cek minimum pembelian aset yang dipakai plan.</div>
        <?php else: ?>
            <div class="table-wrap import-review-wrap">
                <table class="import-review-table">
                    <thead>
                        <tr>
                            <th>Aset</th>
                            <th>Tipe</th>
                            <th class="text-right">Qty Beli</th>
                            <th class="text-right">Nominal</th>
                            <th>Sumber Dana</th>
                            <th class="text-right">AVG Sekarang</th>
                            <th class="text-right">AVG Setelah Beli</th>
                            <th>Alasan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <?php $asset = $item['asset']; ?>
                            <tr>
                                <td>
                                    <strong><?php echo html_escape($asset->name); ?></strong>
                                    <?php if (!empty($asset->portfolio_name)): ?>
                                        <div class="muted"><?php echo html_escape($asset->platform ? $asset->platform . ' · ' . $asset->portfolio_name : $asset->portfolio_name); ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($asset->symbol)): ?>
                                        <div class="muted"><?php echo html_escape($asset->symbol); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo html_escape(str_replace('_', ' ', $asset->type)); ?></td>
                                <td class="text-right">
                                    <?php if ($asset->type === 'saham'): ?>
                                        <?php echo html_escape($item['unit_label']); ?>
                                        <div class="muted"><?php echo number_format((float) $item['quantity'], 0, ',', '.'); ?> lbr</div>
                                    <?php elseif ($asset->unit === 'idr'): ?>
                                        <?php echo rupiah($item['quantity']); ?>
                                    <?php else: ?>
                                        <?php echo number_format((float) $item['quantity'], 4, ',', '.'); ?> <?php echo html_escape($item['unit_label']); ?>
                                    <?php endif; ?>
                                </td>
                                <td class="text-right"><?php echo rupiah($item['amount']); ?></td>
                                <td>
                                    <?php if (!empty($item['from_rdn'])): ?>
                                        <div>RDN <?php echo rupiah($item['from_rdn']); ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($item['from_budget'])): ?>
                                        <div>Budget <?php echo rupiah($item['from_budget']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-right"><?php echo rupiah($asset->avg_price); ?></td>
                                <td class="text-right"><?php echo rupiah($item['avg_after']); ?></td>
                                <td>
                                    <?php echo html_escape($item['reason']); ?>
                                    <?php if (!empty($ai['item_notes'][(string) $asset->id])): ?>
                                        <div class="muted"><?php echo html_escape($ai['item_notes'][(string) $asset->id]); ?></div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <section class="panel" style="margin-top:18px">
        <div class="panel-head">
            <h3>Detail Kandidat</h3>
            <span class="badge muted">Total portfolio nanti <?php echo rupiah($summary['future_total']); ?></span>
        </div>
        <?php if (empty($candidates)): ?>
            <div class="empty">Belum ada aset yang flag Plan-nya aktif.</div>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Aset</th>
                            <th>Tipe</th>
                            <th class="text-right">Target</th>
                            <th class="text-right">Gap Alokasi</th>
                            <th class="text-right">Min Beli</th>
                            <th>Status</th>
                            <th class="text-right">Score</th>
                            <th>Alasan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($candidates as $candidate): ?>
                            <?php $asset = $candidate['asset']; ?>
                            <tr>
                                <td>
                                    <strong><?php echo html_escape($asset->name); ?></strong>
                                    <?php if (!empty($asset->portfolio_name)): ?>
                                        <div class="muted"><?php echo html_escape($asset->platform ? $asset->platform . ' · ' . $asset->portfolio_name : $asset->portfolio_name); ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($asset->symbol)): ?>
                                        <div class="muted"><?php echo html_escape($asset->symbol); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo html_escape(str_replace('_', ' ', $asset->type)); ?></td>
                                <td class="text-right"><?php echo number_format((float) $candidate['target_weight'], 1, ',', '.'); ?>%</td>
                                <td class="text-right"><?php echo rupiah($candidate['allocation_gap']); ?></td>
                                <td class="text-right"><?php echo rupiah($candidate['required']); ?></td>
                                <td>
                                    <span class="badge <?php echo $candidate['executable'] ? 'success' : 'warning'; ?>">
                                        <?php echo $candidate['executable'] ? 'Bisa dibeli' : 'Budget kurang'; ?>
                                    </span>
                                </td>
                                <td class="text-right"><?php echo number_format((float) $candidate['score'], 1, ',', '.'); ?></td>
                                <td><?php echo html_escape($candidate['reason']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
<?php endif; ?>
