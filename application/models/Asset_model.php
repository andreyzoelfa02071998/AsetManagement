<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Asset_model extends CI_Model
{
    private function current_user_id()
    {
        return (int) $this->session->userdata('user_id');
    }

    private function owned()
    {
        return $this->db->where('user_id', $this->current_user_id());
    }

    public function all()
    {
        return $this->owned()->order_by('type')->order_by('name')->get('assets')->result();
    }

    public function portfolio()
    {
        return $this->db
            ->where('user_id', $this->current_user_id())
            ->order_by('type')
            ->order_by('name')
            ->get('assets')
            ->result();
    }

    public function planning_assets()
    {
        return $this->db
            ->where('user_id', $this->current_user_id())
            ->where('is_planned', 1)
            ->order_by('type')
            ->order_by('name')
            ->get('assets')
            ->result();
    }

    public function portfolio_summary()
    {
        $rows = $this->portfolio();
        $total_market = 0;
        $total_invested = 0;

        foreach ($rows as $row) {
            $market_value = $this->market_value($row);
            $row->market_value = $market_value;
            $this->db
                ->where('id', $row->id)
                ->where('user_id', $this->current_user_id())
                ->update('assets', array('market_value' => $market_value));
            $row->gain_loss = $market_value - (float) $row->invested_amount;
            $row->gain_loss_percent = (float) $row->invested_amount > 0
                ? ($row->gain_loss / (float) $row->invested_amount) * 100
                : 0;
            $total_market += $market_value;
            $total_invested += (float) $row->invested_amount;
        }

        return array(
            'rows' => $rows,
            'total' => $total_market,
            'invested' => $total_invested,
            'gain_loss' => $total_market - $total_invested,
            'asset_count' => count($rows)
        );
    }

    public function calculate_market_value($payload)
    {
        $asset = (object) $payload;
        return $this->market_value($asset);
    }

    private function market_value($asset)
    {
        if ((float) $asset->market_price <= 0) {
            return (float) $asset->invested_amount;
        }

        if ($asset->type === 'emas' && $asset->unit === 'gram') {
            return ((float) $asset->quantity_current * 100) * (float) $asset->market_price;
        }

        if ($asset->type === 'saham' && $asset->unit === 'lot') {
            return ((float) $asset->quantity_current * 100) * (float) $asset->market_price;
        }

        if ($asset->unit === 'idr') {
            return (float) $asset->quantity_current;
        }

        return (float) $asset->quantity_current * (float) $asset->market_price;
    }

    public function find($id)
    {
        return $this->owned()->where('id', $id)->get('assets')->row();
    }

    public function insert($payload)
    {
        if (!isset($payload['user_id'])) {
            $payload['user_id'] = $this->current_user_id();
        }
        $payload['market_value'] = $this->calculate_market_value($payload);
        $inserted = $this->db->insert('assets', $payload);

        if ($inserted) {
            $this->evaluate_price_alert($this->db->insert_id());
        }

        return $inserted;
    }

    public function update($id, $payload)
    {
        $existing = $this->find($id);
        $merged = $existing ? array_merge((array) $existing, $payload) : $payload;
        $payload['market_value'] = $this->calculate_market_value($merged);
        $updated = $this->owned()->where('id', $id)->update('assets', $payload);

        if ($updated) {
            $this->evaluate_price_alert($id);
        }

        return $updated;
    }

    public function delete($id)
    {
        return $this->owned()->where('id', $id)->delete('assets');
    }

    public function set_planned($id, $is_planned)
    {
        return $this->owned()
            ->where('id', $id)
            ->update('assets', array('is_planned' => $is_planned ? 1 : 0));
    }

    public function active_price_alerts()
    {
        return $this->owned()
            ->where('price_alert_enabled', 1)
            ->where('price_alert_target IS NOT NULL', null, false)
            ->order_by('price_alert_triggered_at IS NULL', 'ASC', false)
            ->order_by('price_alert_triggered_at', 'DESC')
            ->order_by('name')
            ->get('assets')
            ->result();
    }

    public function triggered_price_alerts()
    {
        return $this->owned()
            ->where('price_alert_enabled', 1)
            ->where('price_alert_triggered_at IS NOT NULL', null, false)
            ->order_by('price_alert_triggered_at', 'DESC')
            ->get('assets')
            ->result();
    }

    public function evaluate_price_alert($id)
    {
        $asset = $this->find($id);
        if (!$asset || !(int) $asset->price_alert_enabled || (float) $asset->price_alert_target <= 0 || (float) $asset->market_price <= 0) {
            return false;
        }

        $is_hit = $this->is_price_alert_hit($asset);
        $payload = array(
            'price_alert_last_price' => (float) $asset->market_price
        );

        if ($is_hit && empty($asset->price_alert_triggered_at)) {
            $payload['price_alert_triggered_at'] = date('Y-m-d H:i:s');
        } elseif (!$is_hit && !empty($asset->price_alert_triggered_at)) {
            $payload['price_alert_triggered_at'] = null;
        }

        return $this->db
            ->where('user_id', $this->current_user_id())
            ->where('id', $id)
            ->update('assets', $payload);
    }

    private function is_price_alert_hit($asset)
    {
        if ($asset->price_alert_direction === 'above') {
            return (float) $asset->market_price >= (float) $asset->price_alert_target;
        }

        return (float) $asset->market_price <= (float) $asset->price_alert_target;
    }

    public function types()
    {
        return array(
            'emas' => 'Emas',
            'saham' => 'Saham',
            'reksa_dana' => 'Reksa Dana',
            'crypto' => 'Crypto',
            'properti' => 'Properti',
            'kas' => 'Kas',
            'rdn' => 'Saldo RDN',
            'lainnya' => 'Lainnya'
        );
    }

    public function units()
    {
        return array(
            'idr' => 'Rupiah',
            'gram' => 'Gram',
            '0.01g' => '0,01 gram',
            'share' => 'Lembar saham',
            'lot' => 'Lot saham',
            'unit' => 'Unit',
            'property' => 'Properti'
        );
    }

    public function sync_stock_prices()
    {
        $stocks = $this->db
            ->where('user_id', $this->current_user_id())
            ->where('type', 'saham')
            ->where('symbol IS NOT NULL', null, false)
            ->get('assets')
            ->result();

        $updated = 0;
        $failed = array();

        foreach ($stocks as $stock) {
            $price = $this->fetch_yahoo_price($stock->symbol);
            if ($price <= 0) {
                $failed[] = $stock->symbol;
                continue;
            }

            $this->update($stock->id, array('market_price' => $price));
            $updated++;
        }

        return array('updated' => $updated, 'failed' => $failed);
    }

    private function fetch_yahoo_price($symbol)
    {
        $symbol = strtoupper(trim($symbol));
        if ($symbol === '') {
            return 0;
        }

        if (substr($symbol, -3) !== '.JK') {
            $symbol .= '.JK';
        }

        $url = 'https://query1.finance.yahoo.com/v8/finance/chart/' . rawurlencode($symbol) . '?range=1d&interval=1d';
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => array('Accept: application/json', 'User-Agent: AnalisaAset/1.0')
        ));
        $body = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!$body || $status >= 400) {
            return 0;
        }

        $json = json_decode($body, true);
        if (isset($json['chart']['result'][0]['meta']['regularMarketPrice'])) {
            return (float) $json['chart']['result'][0]['meta']['regularMarketPrice'];
        }

        $quote = isset($json['chart']['result'][0]['indicators']['quote'][0]['close'])
            ? $json['chart']['result'][0]['indicators']['quote'][0]['close']
            : array();

        if (!empty($quote)) {
            $last = end($quote);
            return (float) $last;
        }

        return 0;
    }
}
