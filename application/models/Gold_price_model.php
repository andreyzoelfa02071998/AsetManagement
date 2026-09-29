<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Gold_price_model extends CI_Model
{
    public function all()
    {
        return $this->db->order_by('price_date', 'DESC')->get('gold_prices')->result();
    }

    public function latest()
    {
        return $this->db->order_by('price_date', 'DESC')->limit(1)->get('gold_prices')->row();
    }

    public function upsert($payload)
    {
        $existing = $this->db->where('price_date', $payload['price_date'])->get('gold_prices')->row();
        if ($existing) {
            return $this->db->where('id', $existing->id)->update('gold_prices', $payload);
        }

        return $this->db->insert('gold_prices', $payload);
    }

    public function upsert_today_tring($harga_jual, $harga_beli = null, $unit = '0.01', $source = 'Pegadaian/Tring')
    {
        return $this->upsert(array(
            'price_date' => date('Y-m-d'),
            'price' => $harga_jual,
            'buy_price' => $harga_beli,
            'unit' => $unit,
            'source' => $source,
            'notes' => 'hargaJual = harga user beli; hargaBeli = harga user jual'
        ));
    }

    public function fetch_tring()
    {
        $url = 'https://pegadaian.co.id/gold/prices';
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => array('Accept: application/json')
        ));
        $body = curl_exec($ch);
        $error = curl_error($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($body === false || $status >= 400) {
            return array('ok' => false, 'message' => $error ?: 'Endpoint harga belum bisa dibaca.');
        }

        $json = json_decode($body, true);
        if (!isset($json['data']['hargaJual'])) {
            return array('ok' => false, 'message' => 'Format response Pegadaian tidak sesuai.');
        }

        return array(
            'ok' => true,
            'harga_jual' => (float) $json['data']['hargaJual'],
            'harga_beli' => isset($json['data']['hargaBeli']) ? (float) $json['data']['hargaBeli'] : null,
            'unit' => isset($json['data']['unit']) ? $json['data']['unit'] : '0.01',
            'raw' => $json
        );
    }

    public function delete($id)
    {
        return $this->db->where('id', $id)->delete('gold_prices');
    }

    public function signal($latest_gold, $target_price = 24000)
    {
        if (!$latest_gold) {
            return array('status' => 'empty', 'label' => 'Belum ada data harga', 'class' => 'muted');
        }

        if ((float) $latest_gold->price <= (float) $target_price) {
            return array('status' => 'buy', 'label' => 'BUY - harga Tring <= Rp ' . number_format($target_price, 0, ',', '.') . ' / 0,01g', 'class' => 'success');
        }

        return array('status' => 'wait', 'label' => 'WAIT - tunggu harga <= Rp ' . number_format($target_price, 0, ',', '.') . ' / 0,01g', 'class' => 'warning');
    }
}
