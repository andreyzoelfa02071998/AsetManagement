<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Recommendation_model extends CI_Model
{
    public function calculate($budget, $selected_asset_ids = null)
    {
        $portfolio = $this->Asset_model->portfolio();
        $assets = $this->investable_assets($this->Asset_model->planning_assets(), $selected_asset_ids);
        $rdn_balance = $this->rdn_balance($portfolio);
        $context = $this->build_context($assets, $budget, $rdn_balance);
        $candidates = array();

        foreach ($assets as $asset) {
            $candidates[] = $this->candidate($asset, $budget, $context);
        }

        usort($candidates, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return array(
            'summary' => $context,
            'plan' => $this->allocate_budget($candidates, $budget, $rdn_balance),
            'candidates' => $candidates,
            'ai' => $this->ai_analysis($portfolio, $budget, $rdn_balance, $selected_asset_ids)
        );
    }

    private function rdn_balance($assets)
    {
        $total = 0;
        foreach ($assets as $asset) {
            if ($asset->type === 'rdn') {
                $total += (float) $asset->market_value > 0 ? (float) $asset->market_value : (float) $asset->quantity_current;
            }
        }
        return $total;
    }

    private function investable_assets($assets, $selected_asset_ids = null)
    {
        $investable = array();
        $selected = array();
        $has_selection_filter = is_array($selected_asset_ids);
        if ($has_selection_filter) {
            foreach ($selected_asset_ids as $id) {
                $selected[(int) $id] = true;
            }
        }

        foreach ($assets as $asset) {
            if ($asset->type === 'kas' || $asset->type === 'rdn') {
                continue;
            }
            if ($has_selection_filter && !isset($selected[(int) $asset->id])) {
                continue;
            }
            $investable[] = $asset;
        }
        return $investable;
    }

    private function build_context($assets, $budget, $rdn_balance)
    {
        $total_market = 0;
        $type_counts = array();
        $selected_asset_ids = array();

        foreach ($assets as $asset) {
            $total_market += (float) $asset->market_value;
            $selected_asset_ids[] = (int) $asset->id;
            if (!isset($type_counts[$asset->type])) {
                $type_counts[$asset->type] = 0;
            }
            $type_counts[$asset->type]++;
        }

        return array(
            'total_market' => $total_market,
            'future_total' => $total_market + $budget + $rdn_balance,
            'budget_plan' => $budget,
            'rdn_balance' => $rdn_balance,
            'stock_fund_buying_power' => $budget + $rdn_balance,
            'gold_buying_power' => $budget,
            'selected_asset_ids' => $selected_asset_ids,
            'targets' => $this->target_weights($assets, $type_counts),
            'type_counts' => $type_counts
        );
    }

    private function target_weights($assets, $type_counts)
    {
        $default_type_target = array(
            'reksa_dana' => 35,
            'saham' => 45,
            'emas' => 20,
            'crypto' => 0,
            'properti' => 0,
            'lainnya' => 0
        );

        $weights = array();
        $total = 0;

        foreach ($assets as $asset) {
            if ((float) $asset->target_allocation > 0) {
                $weight = (float) $asset->target_allocation;
            } else {
                $type_target = isset($default_type_target[$asset->type]) ? $default_type_target[$asset->type] : 0;
                $count = isset($type_counts[$asset->type]) && $type_counts[$asset->type] > 0 ? $type_counts[$asset->type] : 1;
                $weight = $type_target / $count;
            }

            $weights[$asset->id] = $weight;
            $total += $weight;
        }

        if ($total <= 0 && count($assets) > 0) {
            $equal = 100 / count($assets);
            foreach ($assets as $asset) {
                $weights[$asset->id] = $equal;
            }
            return $weights;
        }

        foreach ($weights as $id => $weight) {
            $weights[$id] = ($weight / $total) * 100;
        }

        return $weights;
    }

    private function candidate($asset, $budget, $context)
    {
        $target_weight = isset($context['targets'][$asset->id]) ? $context['targets'][$asset->id] : 0;
        $target_value = $context['future_total'] * ($target_weight / 100);
        $gap = $target_value - (float) $asset->market_value;
        $required = $this->minimum_required($asset);
        $available = in_array($asset->type, array('saham', 'reksa_dana'), true)
            ? (float) $context['stock_fund_buying_power']
            : (float) $context['gold_buying_power'];
        $executable = $available >= $required && $required > 0;
        $planning_price = $this->planning_price($asset);
        $below_avg = $planning_price > 0 && (float) $asset->avg_price > 0 && $planning_price < (float) $asset->avg_price;
        $suggested_buy_price = $this->suggested_buy_price($asset);
        $suggested_required = $this->minimum_required($asset, $suggested_buy_price);

        return array(
            'asset' => $asset,
            'required' => $required,
            'suggested_required' => $suggested_required,
            'executable' => $executable,
            'target_weight' => $target_weight,
            'target_value' => $target_value,
            'allocation_gap' => $gap,
            'below_avg' => $below_avg,
            'planning_price' => $planning_price,
            'suggested_buy_price' => $suggested_buy_price,
            'score' => $this->score_asset($asset, $available, $executable, $gap, $below_avg),
            'reason' => $this->reason($asset, $gap, $target_weight, $below_avg, $required, $executable),
            'max_amount' => $this->max_amount($asset, $available)
        );
    }

    private function minimum_required($asset, $price_override = null)
    {
        $price = $price_override !== null ? (float) $price_override : $this->planning_price($asset);
        if ($asset->type === 'saham') {
            return $price * max(1, (int) $asset->lot_size);
        }
        if ((float) $asset->min_purchase_amount > 0) {
            return (float) $asset->min_purchase_amount;
        }
        if ($asset->type === 'reksa_dana') {
            return 10000;
        }
        return $price > 0 ? $price : 10000;
    }

    private function max_amount($asset, $budget)
    {
        if ($asset->type === 'saham') {
            $required = $this->minimum_required($asset);
            $lots = $required > 0 ? floor($budget / $required) : 0;
            return $lots * $required;
        }

        return $budget;
    }

    private function allocate_budget($candidates, $budget, $rdn_balance)
    {
        $budget_remaining = $budget;
        $rdn_remaining = $rdn_balance;
        $items = array();

        foreach ($candidates as $candidate) {
            $asset = $candidate['asset'];
            $available = in_array($asset->type, array('saham', 'reksa_dana'), true)
                ? $budget_remaining + $rdn_remaining
                : $budget_remaining;

            if ($available <= 0 || !$candidate['executable']) {
                continue;
            }

            $desired = $candidate['allocation_gap'] > 0 ? min($available, $candidate['allocation_gap']) : 0;
            if ($desired <= 0 && empty($items)) {
                $desired = min($available, $candidate['max_amount']);
            }

            $allocation = $this->allocation_for_asset($asset, $available, $desired);
            if ($allocation['amount'] <= 0) {
                continue;
            }

            $funding = $this->funding_split($asset, $allocation['amount'], $budget_remaining, $rdn_remaining);
            if ($funding['amount'] <= 0) {
                continue;
            }

            $allocation['amount'] = $funding['amount'];
            $allocation['from_budget'] = $funding['from_budget'];
            $allocation['from_rdn'] = $funding['from_rdn'];
            $allocation['asset'] = $asset;
            $allocation['score'] = $candidate['score'];
            $allocation['reason'] = $candidate['reason'];
            $allocation['avg_after'] = $this->avg_after($asset, $allocation['amount'], $allocation['quantity']);
            $allocation['market_price'] = $candidate['planning_price'];
            $allocation['suggested_buy_price'] = $candidate['suggested_buy_price'];
            $allocation['suggested_amount'] = $this->amount_at_price($asset, $allocation['quantity'], $candidate['suggested_buy_price']);
            $allocation['avg_after_suggested'] = $this->avg_after($asset, $allocation['suggested_amount'], $allocation['quantity']);
            $allocation['allocation_gap'] = $candidate['allocation_gap'];
            $allocation['target_weight'] = $candidate['target_weight'];
            $items[] = $allocation;

            $budget_remaining -= $allocation['from_budget'];
            $rdn_remaining -= $allocation['from_rdn'];
        }

        return array(
            'items' => $items,
            'total_allocated' => ($budget - $budget_remaining) + ($rdn_balance - $rdn_remaining),
            'budget_used' => $budget - $budget_remaining,
            'rdn_used' => $rdn_balance - $rdn_remaining,
            'remaining' => $budget_remaining,
            'rdn_remaining' => $rdn_remaining
        );
    }

    private function funding_split($asset, $amount, $budget_remaining, $rdn_remaining)
    {
        if (in_array($asset->type, array('saham', 'reksa_dana'), true)) {
            $from_rdn = min($rdn_remaining, $amount);
            $from_budget = min($budget_remaining, $amount - $from_rdn);
            return array(
                'amount' => $from_rdn + $from_budget,
                'from_budget' => $from_budget,
                'from_rdn' => $from_rdn
            );
        }

        $from_budget = min($budget_remaining, $amount);
        return array('amount' => $from_budget, 'from_budget' => $from_budget, 'from_rdn' => 0);
    }

    private function allocation_for_asset($asset, $remaining, $desired)
    {
        $price = $this->planning_price($asset);
        $required = $this->minimum_required($asset);

        if ($asset->type === 'saham') {
            $lot_size = max(1, (int) $asset->lot_size);
            $lot_cost = $price * $lot_size;
            $lots = $lot_cost > 0 ? floor(min($remaining, max($desired, $lot_cost)) / $lot_cost) : 0;
            if ($lots < 1) {
                return array('amount' => 0, 'quantity' => 0, 'unit_label' => 'lot');
            }
            return array('amount' => $lots * $lot_cost, 'quantity' => $lots * $lot_size, 'unit_label' => $lots . ' lot');
        }

        if ($remaining < $required) {
            return array('amount' => 0, 'quantity' => 0, 'unit_label' => $asset->unit);
        }

        $amount = min($remaining, max($required, $desired));
        if ($asset->type === 'emas' && $asset->unit === 'gram') {
            $quantity = $price > 0 ? $amount / ($price * 100) : 0;
            return array('amount' => $amount, 'quantity' => $quantity, 'unit_label' => 'gram');
        }
        if ($asset->unit === 'idr') {
            return array('amount' => $amount, 'quantity' => $amount, 'unit_label' => 'rupiah');
        }

        $quantity = $price > 0 ? $amount / $price : $amount;
        return array('amount' => $amount, 'quantity' => $quantity, 'unit_label' => $asset->unit);
    }

    private function avg_after($asset, $amount, $quantity_added)
    {
        if ($quantity_added <= 0) {
            return (float) $asset->avg_price;
        }
        $total_quantity = (float) $asset->quantity_current + $quantity_added;
        if ($asset->type === 'emas' && $asset->unit === 'gram') {
            return ((float) $asset->invested_amount + $amount) / ($total_quantity * 100);
        }
        return ((float) $asset->invested_amount + $amount) / $total_quantity;
    }

    private function suggested_buy_price($asset)
    {
        $market = $this->planning_price($asset);
        $avg = (float) $asset->avg_price;
        $target = (float) $asset->target_buy_price;

        if ($target > 0) {
            return $this->round_entry_price($asset, $target);
        }

        if ($asset->type === 'saham') {
            $base = $market > 0 ? $market * 0.98 : $avg * 0.98;
            if ($avg > 0 && $market > $avg) {
                $base = min($base, $avg);
            }
            return $this->round_entry_price($asset, max(1, $base));
        }

        if ($asset->type === 'reksa_dana') {
            $base = $market > 0 ? $market * 0.995 : $avg;
            if ($avg > 0 && $market > $avg) {
                $base = min($base, $avg);
            }
            return max(0, round($base, 4));
        }

        if ($asset->type === 'emas') {
            $base = $market > 0 ? $market * 0.99 : $avg * 0.99;
            return $this->round_entry_price($asset, max(0, $base));
        }

        return $market;
    }

    private function planning_price($asset)
    {
        if ($asset->type === 'emas' && (float) $asset->min_purchase_amount > 0) {
            return (float) $asset->min_purchase_amount;
        }

        return (float) $asset->market_price;
    }

    private function amount_at_price($asset, $quantity, $price)
    {
        if ($quantity <= 0 || $price <= 0) {
            return 0;
        }

        if (($asset->type === 'emas' && $asset->unit === 'gram') || ($asset->type === 'saham' && $asset->unit === 'lot')) {
            return $quantity * 100 * $price;
        }

        return $quantity * $price;
    }

    private function round_entry_price($asset, $price)
    {
        if ($asset->type !== 'saham') {
            return round($price, 0);
        }

        if ($price < 200) {
            $tick = 1;
        } elseif ($price < 500) {
            $tick = 2;
        } elseif ($price < 2000) {
            $tick = 5;
        } elseif ($price < 5000) {
            $tick = 10;
        } else {
            $tick = 25;
        }

        return max($tick, floor($price / $tick) * $tick);
    }

    private function score_asset($asset, $budget, $executable, $gap, $below_avg)
    {
        if (!$executable) {
            return 0;
        }

        $score = 45;
        if ($gap > 0) {
            $score += min(30, ($gap / max(1, $budget)) * 30);
        } else {
            $score -= 12;
        }
        if ($below_avg) {
            $score += $asset->type === 'saham' ? 16 : 8;
        }
        if ($asset->type === 'reksa_dana') {
            $score += 12;
        }
        if ($asset->type === 'emas') {
            $score += ($this->planning_price($asset) <= (float) $asset->target_buy_price) ? 18 : 2;
        }
        if ($budget < 500000 && $asset->type === 'saham') {
            $score -= 10;
        }
        return max(0, round($score, 2));
    }

    private function reason($asset, $gap, $target_weight, $below_avg, $required, $executable)
    {
        if (!$executable) {
            return 'Belum feasible. Minimal butuh Rp ' . number_format($required, 0, ',', '.') . '.';
        }

        $parts = array();
        $parts[] = $gap > 0
            ? 'alokasi masih kurang dari target ' . number_format($target_weight, 1, ',', '.') . '%'
            : 'alokasi sudah cukup/di atas target';

        if ($below_avg) {
            $parts[] = 'harga sekarang di bawah AVG, averaging lebih efektif';
        } elseif ((float) $asset->avg_price > 0 && $this->planning_price($asset) > 0) {
            $parts[] = 'harga sekarang belum di bawah AVG';
        }
        if ($asset->type === 'saham') {
            $parts[] = 'pembelian dibulatkan ke lot';
        }
        if (in_array($asset->type, array('saham', 'reksa_dana'), true)) {
            $parts[] = 'saldo RDN ikut dihitung sebagai buying power';
        }
        $parts[] = 'harga masuk ideal dipakai sebagai area tunggu, bukan kepastian harga akan tercapai';
        if ($asset->type === 'emas') {
            $parts[] = 'emas hanya memakai budget input, bukan RDN';
            $parts[] = 'spread Tring tetap perlu diperhatikan';
        }
        if ($asset->type === 'reksa_dana') {
            $parts[] = 'fleksibel untuk sisa dana kecil';
        }

        return implode('; ', $parts) . '.';
    }

    private function ai_analysis($portfolio, $budget, $rdn_balance, $selected_asset_ids = array())
    {
        $ci =& get_instance();
        if (!isset($ci->ai_screenshot_parser)) {
            $ci->load->library('Ai_screenshot_parser');
        }

        $assets = array();
        $selected = array();
        foreach ((array) $selected_asset_ids as $id) {
            $selected[(int) $id] = true;
        }

        foreach ($portfolio as $asset) {
            $is_selected_for_plan = isset($selected[(int) $asset->id]);
            $assets[] = array(
                'asset_id' => (int) $asset->id,
                'name' => $asset->name,
                'type' => $asset->type,
                'symbol' => $asset->symbol,
                'platform' => $asset->platform,
                'portfolio_name' => $asset->portfolio_name,
                'quantity' => (float) $asset->quantity_current,
                'unit' => $asset->unit,
                'avg_price' => (float) $asset->avg_price,
                'market_price' => (float) $asset->market_price,
                'market_value' => (float) $asset->market_value,
                'invested_amount' => (float) $asset->invested_amount,
                'is_planned' => (int) $asset->is_planned,
                'selected_for_this_plan' => $is_selected_for_plan ? 1 : 0
            );
        }

        $payload = array(
            'budget_plan' => $budget,
            'rdn_balance' => $rdn_balance,
            'selected_asset_ids' => array_values(array_map('intval', (array) $selected_asset_ids)),
            'rule' => 'RDN hanya untuk saham/reksadana. Emas hanya memakai budget plan baru. Analisa eksekusi hanya untuk aset selected_for_this_plan=1.',
            'assets' => $assets
        );

        $analysis = $ci->ai_screenshot_parser->analyze_planner($payload);
        if (!empty($analysis)) {
            $analysis['enabled'] = true;
            $analysis['error'] = '';
            return $analysis;
        }

        return array(
            'enabled' => false,
            'risk_level' => 'rule-based',
            'headline' => 'Planner memakai rule-based karena AI belum aktif atau gagal.',
            'strategy' => 'Alokasi dihitung dari target komposisi, gap alokasi, harga market, minimum pembelian, saldo RDN, dan simulasi AVG.',
            'market_view' => '',
            'cash_policy' => 'Saldo RDN dipakai sebagai buying power untuk saham/reksadana. Budget plan tetap dipakai untuk semua aset, tapi emas tidak memakai RDN.',
            'warnings' => array($ci->ai_screenshot_parser->last_error()),
            'item_notes' => array(),
            'error' => $ci->ai_screenshot_parser->last_error()
        );
    }
}
