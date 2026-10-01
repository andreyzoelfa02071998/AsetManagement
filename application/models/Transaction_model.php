<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Transaction_model extends CI_Model
{
    private $last_error = '';

    private function current_user_id()
    {
        return (int) $this->session->userdata('user_id');
    }

    public function error()
    {
        return $this->last_error;
    }

    public function all()
    {
        return $this->db
            ->select('transactions.*, assets.name AS asset_name, assets.type AS asset_type, assets.unit AS asset_unit')
            ->from('transactions')
            ->join('assets', 'assets.id = transactions.asset_id')
            ->where('assets.user_id', $this->current_user_id())
            ->order_by('transaction_date', 'DESC')
            ->order_by('transactions.id', 'DESC')
            ->get()
            ->result();
    }

    public function recent($limit = 5)
    {
        return $this->db
            ->select('transactions.*, assets.name AS asset_name, assets.type AS asset_type, assets.unit AS asset_unit')
            ->from('transactions')
            ->join('assets', 'assets.id = transactions.asset_id')
            ->where('assets.user_id', $this->current_user_id())
            ->order_by('transaction_date', 'DESC')
            ->limit($limit)
            ->get()
            ->result();
    }

    public function insert($payload)
    {
        $asset = $this->db
            ->where('id', $payload['asset_id'])
            ->where('user_id', $this->current_user_id())
            ->get('assets')
            ->row();
        if (!$asset) {
            $this->last_error = 'Aset tidak ditemukan.';
            return false;
        }

        $quantity = (float) $payload['quantity'];
        $price = (float) $payload['price'];
        $fee = (float) $payload['fee'];
        $type = $payload['transaction_type'];

        if ($quantity <= 0 || $price <= 0 || !in_array($type, array('buy', 'sell'), true)) {
            $this->last_error = 'Data transaksi tidak valid.';
            return false;
        }

        if ($type === 'sell' && $quantity > ((float) $asset->quantity_current + 0.0001)) {
            $this->last_error = 'Qty jual lebih besar dari qty aset yang dimiliki.';
            return false;
        }

        $this->db->trans_start();
        $inserted = $this->db->insert('transactions', $payload);
        if ($inserted) {
            $this->apply_portfolio_effect($asset, $type, $quantity, $price, $fee);
        }
        $this->db->trans_complete();

        if (!$this->db->trans_status()) {
            $this->last_error = 'Transaksi gagal disimpan.';
            return false;
        }

        return true;
    }

    public function delete($id)
    {
        $transaction = $this->db
            ->select('transactions.id')
            ->from('transactions')
            ->join('assets', 'assets.id = transactions.asset_id')
            ->where('transactions.id', $id)
            ->where('assets.user_id', $this->current_user_id())
            ->get()
            ->row();

        if (!$transaction) {
            return false;
        }

        $transaction = $this->db
            ->select('transactions.*, assets.type AS asset_type')
            ->from('transactions')
            ->join('assets', 'assets.id = transactions.asset_id')
            ->where('transactions.id', $id)
            ->where('assets.user_id', $this->current_user_id())
            ->get()
            ->row();

        $asset = $this->db
            ->where('id', $transaction->asset_id)
            ->where('user_id', $this->current_user_id())
            ->get('assets')
            ->row();

        $this->db->trans_start();
        if ($asset) {
            $reverse_type = $transaction->transaction_type === 'buy' ? 'sell' : 'buy';
            $this->apply_portfolio_effect($asset, $reverse_type, (float) $transaction->quantity, (float) $transaction->price, (float) $transaction->fee, true);
        }
        $this->db->where('id', $id)->delete('transactions');
        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    private function apply_portfolio_effect($asset, $type, $quantity, $price, $fee, $is_reversal = false)
    {
        $amount = $this->transaction_amount($asset, $quantity, $price);
        $gross = $amount + $fee;
        $net = max(0, $amount - $fee);

        if ($type === 'buy') {
            $new_quantity = (float) $asset->quantity_current + $quantity;
            $new_invested = (float) $asset->invested_amount + $gross;
            $avg_price = $this->average_price($asset, $new_quantity, $new_invested);
            $this->update_asset_position($asset, $new_quantity, $new_invested, $avg_price, $price);
            $this->adjust_rdn($is_reversal ? -$net : -$gross, $is_reversal ? 'Pembatalan jual' : 'Pembelian aset');
            return;
        }

        $current_quantity = (float) $asset->quantity_current;
        $new_quantity = max(0, $current_quantity - $quantity);
        $cost_removed = $current_quantity > 0
            ? min(1, $quantity / $current_quantity) * (float) $asset->invested_amount
            : 0;
        $new_invested = max(0, (float) $asset->invested_amount - $cost_removed);
        $avg_price = $new_quantity > 0 ? $this->average_price($asset, $new_quantity, $new_invested) : 0;
        $this->update_asset_position($asset, $new_quantity, $new_invested, $avg_price, $price);
        $this->adjust_rdn($is_reversal ? $gross : $net, $is_reversal ? 'Pembatalan beli' : 'Hasil jual aset');
    }

    private function transaction_amount($asset, $quantity, $price)
    {
        if ($asset->type === 'saham' && $asset->unit === 'lot') {
            return $quantity * 100 * $price;
        }

        if ($asset->type === 'emas' && $asset->unit === 'gram') {
            return $quantity * 100 * $price;
        }

        return $quantity * $price;
    }

    private function average_price($asset, $quantity, $invested)
    {
        if ($quantity <= 0) {
            return 0;
        }

        if (($asset->type === 'saham' && $asset->unit === 'lot') || ($asset->type === 'emas' && $asset->unit === 'gram')) {
            return $invested / ($quantity * 100);
        }

        return $invested / $quantity;
    }

    private function update_asset_position($asset, $quantity, $invested, $avg_price, $transaction_price)
    {
        $market_price = $asset->type === 'saham' || $asset->type === 'reksa_dana'
            ? $transaction_price
            : (float) $asset->market_price;

        $this->Asset_model->update($asset->id, array(
            'quantity_current' => $quantity,
            'invested_amount' => $invested,
            'avg_price' => $avg_price,
            'market_price' => $market_price
        ));
    }

    private function adjust_rdn($delta, $reason)
    {
        if (abs($delta) < 0.0001) {
            return true;
        }

        $rdn = $this->db
            ->where('user_id', $this->current_user_id())
            ->where('type', 'rdn')
            ->order_by('id', 'ASC')
            ->get('assets')
            ->row();

        if (!$rdn) {
            $balance = max(0, $delta);
            return $this->db->insert('assets', array(
                'user_id' => $this->current_user_id(),
                'name' => 'Saldo RDN',
                'type' => 'rdn',
                'symbol' => 'IDR',
                'platform' => 'RDN',
                'quantity_current' => $balance,
                'unit' => 'idr',
                'avg_price' => 1,
                'market_price' => 1,
                'market_value' => $balance,
                'invested_amount' => $balance,
                'lot_size' => 1,
                'min_purchase_amount' => 0,
                'target_allocation' => 0,
                'is_planned' => 0,
                'notes' => $reason
            ));
        }

        $balance = max(0, (float) $rdn->quantity_current + $delta);
        return $this->Asset_model->update($rdn->id, array(
            'quantity_current' => $balance,
            'invested_amount' => $balance,
            'market_price' => 1,
            'avg_price' => 1,
            'is_planned' => 0
        ));
    }

    public function portfolio_summary()
    {
        $rows = $this->db
            ->select("assets.type, assets.name, SUM(CASE WHEN transactions.transaction_type = 'buy' THEN transactions.quantity ELSE -transactions.quantity END) AS quantity", FALSE)
            ->select("SUM(CASE WHEN transactions.transaction_type = 'buy' THEN (transactions.quantity * transactions.price) + transactions.fee ELSE -((transactions.quantity * transactions.price) - transactions.fee) END) AS net_value", FALSE)
            ->from('transactions')
            ->join('assets', 'assets.id = transactions.asset_id')
            ->where('assets.user_id', $this->current_user_id())
            ->group_by(array('assets.id', 'assets.type', 'assets.name'))
            ->order_by('assets.type')
            ->get()
            ->result();

        $total = 0;
        foreach ($rows as $row) {
            $total += (float) $row->net_value;
        }

        return array(
            'rows' => $rows,
            'total' => $total,
            'asset_count' => count($rows)
        );
    }
}
