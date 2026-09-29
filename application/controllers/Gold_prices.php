<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Gold_prices extends MY_Controller
{
    public function index()
    {
        $portfolio = $this->Asset_model->portfolio_summary();
        $data = array(
            'title' => 'Market Aset',
            'prices' => $this->Gold_price_model->all(),
            'latest' => $this->Gold_price_model->latest(),
            'target_price' => 24000,
            'portfolio' => $portfolio,
            'market_groups' => $this->market_groups($portfolio['rows']),
            'news' => $this->market_news($portfolio['rows']),
            'content' => 'gold_prices/index'
        );

        $this->load->view('layouts/main', $data);
    }

    private function market_groups($assets)
    {
        $groups = array();
        foreach ($assets as $asset) {
            $type = $asset->type;
            if (!isset($groups[$type])) {
                $groups[$type] = array(
                    'type' => $type,
                    'count' => 0,
                    'market_value' => 0,
                    'invested_amount' => 0,
                    'gain_loss' => 0
                );
            }

            $groups[$type]['count']++;
            $groups[$type]['market_value'] += (float) $asset->market_value;
            $groups[$type]['invested_amount'] += (float) $asset->invested_amount;
            $groups[$type]['gain_loss'] += (float) $asset->gain_loss;
        }

        uasort($groups, function ($a, $b) {
            return $b['market_value'] <=> $a['market_value'];
        });

        return $groups;
    }

    private function market_news($assets)
    {
        $topics = array('IHSG', 'emas', 'reksadana', 'suku bunga Bank Indonesia');
        foreach ($assets as $asset) {
            if ($asset->type === 'saham' && trim((string) $asset->symbol) !== '') {
                $topics[] = strtoupper(str_replace('.JK', '', $asset->symbol));
            }
        }

        $topics = array_slice(array_values(array_unique($topics)), 0, 8);
        $query = implode(' OR ', $topics);
        $url = 'https://news.google.com/rss/search?q=' . rawurlencode($query . ' investasi Indonesia') . '&hl=id&gl=ID&ceid=ID:id';
        $body = $this->fetch_url($url, 8);
        if (!$body) {
            return array();
        }

        $xml = @simplexml_load_string($body);
        if (!$xml || empty($xml->channel->item)) {
            return array();
        }

        $items = array();
        foreach ($xml->channel->item as $item) {
            $items[] = array(
                'title' => (string) $item->title,
                'link' => (string) $item->link,
                'source' => isset($item->source) ? (string) $item->source : 'Google News',
                'published_at' => date('d M Y H:i', strtotime((string) $item->pubDate))
            );

            if (count($items) >= 8) {
                break;
            }
        }

        return $items;
    }

    private function fetch_url($url, $timeout = 10)
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => 'AnalisaAset/1.0'
        ));
        $body = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $body && $status < 400 ? $body : '';
    }

    public function create()
    {
        if ($this->input->post('price') !== null) {
            $_POST['price'] = $this->clean_number($this->input->post('price'));
        }
        if ($this->input->post('buy_price') !== null) {
            $_POST['buy_price'] = $this->clean_number($this->input->post('buy_price'));
        }

        $this->form_validation->set_rules('price_date', 'Tanggal', 'required');
        $this->form_validation->set_rules('price', 'Harga', 'required|numeric');

        if ($this->form_validation->run()) {
            $harga_jual = $this->input->post('price');
            $harga_beli = $this->input->post('buy_price') ?: null;

            $this->Gold_price_model->upsert(array(
                'price_date' => $this->input->post('price_date', TRUE),
                'price' => $harga_jual,
                'buy_price' => $harga_beli,
                'unit' => '0.01',
                'source' => $this->input->post('source', TRUE),
                'notes' => $this->input->post('notes', TRUE)
            ));
            $this->apply_gold_price_to_assets($harga_jual, $harga_beli);

            $this->session->set_flashdata('success', 'Harga emas berhasil disimpan dan aset emas diperbarui.');
            redirect('gold_prices');
        }

        $data = array(
            'title' => 'Input Harga Emas',
            'content' => 'gold_prices/form'
        );

        $this->load->view('layouts/main', $data);
    }

    public function sync_tring()
    {
        $result = $this->Gold_price_model->fetch_tring();
        if (!$result['ok']) {
            if ($this->input->is_ajax_request()) {
                return $this->json_response(array('ok' => false, 'message' => $result['message']));
            }
            $this->session->set_flashdata('error', $result['message']);
            redirect('market');
        }

        $this->Gold_price_model->upsert_today_tring(
            $result['harga_jual'],
            $result['harga_beli'],
            $result['unit']
        );

        $this->apply_gold_price_to_assets($result['harga_jual'], $result['harga_beli']);

        if ($this->input->is_ajax_request()) {
            return $this->json_response(array('ok' => true, 'message' => 'Harga Tring berhasil diupdate.'));
        }

        $this->session->set_flashdata('success', 'Harga Tring berhasil diupdate. Patokan beli user memakai hargaJual.');
        redirect($this->input->get('back') ?: 'market');
    }

    public function sync_all()
    {
        $stock_result = $this->Asset_model->sync_stock_prices();
        $gold_result = $this->sync_tring_assets();

        $messages = array();
        $messages[] = 'Saham update: ' . $stock_result['updated'] . ' aset.';
        if (!empty($stock_result['failed'])) {
            $messages[] = 'Saham gagal: ' . implode(', ', $stock_result['failed']) . '.';
        }
        $messages[] = $gold_result['ok']
            ? 'Emas Tring update.'
            : 'Emas Tring gagal: ' . $gold_result['message'];

        $payload = array(
            'ok' => $stock_result['updated'] > 0 || $gold_result['ok'],
            'message' => implode(' ', $messages),
            'stocks' => $stock_result,
            'gold' => $gold_result,
            'synced_at' => date('H:i:s')
        );

        if ($this->input->is_ajax_request()) {
            return $this->json_response($payload);
        }

        $this->session->set_flashdata($payload['ok'] ? 'success' : 'error', $payload['message']);
        redirect($this->input->get('back') ?: 'dashboard');
    }

    public function sync_stocks()
    {
        $result = $this->Asset_model->sync_stock_prices();
        $message = 'Harga saham berhasil diupdate: ' . $result['updated'] . ' aset.';
        if (!empty($result['failed'])) {
            $message .= ' Gagal: ' . implode(', ', $result['failed']) . '.';
        }

        if ($this->input->is_ajax_request()) {
            return $this->json_response(array(
                'ok' => $result['updated'] > 0,
                'message' => $message,
                'updated' => $result['updated'],
                'failed' => $result['failed']
            ));
        }

        $this->session->set_flashdata($result['updated'] > 0 ? 'success' : 'error', $message);
        redirect($this->input->get('back') ?: 'aset');
    }

    private function json_response($payload)
    {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($payload));
    }

    private function sync_tring_assets()
    {
        $result = $this->Gold_price_model->fetch_tring();
        if (!$result['ok']) {
            return $result;
        }

        $this->Gold_price_model->upsert_today_tring(
            $result['harga_jual'],
            $result['harga_beli'],
            $result['unit']
        );

        $this->apply_gold_price_to_assets($result['harga_jual'], $result['harga_beli']);

        return array(
            'ok' => true,
            'message' => 'Harga Tring berhasil diupdate.',
            'harga_jual' => $result['harga_jual'],
            'harga_beli' => $result['harga_beli'],
            'unit' => $result['unit']
        );
    }

    private function apply_gold_price_to_assets($harga_jual, $harga_beli = null)
    {
        $valuation_price = (float) ($harga_beli ?: $harga_jual);
        $buy_price = (float) $harga_jual;

        $emas_assets = $this->db
            ->where('type', 'emas')
            ->where('user_id', (int) $this->session->userdata('user_id'))
            ->get('assets')
            ->result();

        foreach ($emas_assets as $asset) {
            $this->Asset_model->update($asset->id, array(
                'market_price' => $valuation_price,
                'target_buy_price' => 24000,
                'min_purchase_amount' => $buy_price
            ));
        }
    }

    public function delete($id)
    {
        $this->Gold_price_model->delete($id);
        $this->session->set_flashdata('success', 'Harga emas berhasil dihapus.');
        redirect('gold_prices');
    }
}
