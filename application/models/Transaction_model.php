<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Transaction_model extends CI_Model
{
    private function current_user_id()
    {
        return (int) $this->session->userdata('user_id');
    }

    public function all()
    {
        return $this->db
            ->select('transactions.*, assets.name AS asset_name, assets.type AS asset_type')
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
            ->select('transactions.*, assets.name AS asset_name')
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
            return false;
        }
        return $this->db->insert('transactions', $payload);
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

        return $this->db->where('id', $id)->delete('transactions');
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
