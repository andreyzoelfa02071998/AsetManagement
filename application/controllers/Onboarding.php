<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Onboarding extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('Screenshot_parser');
        $this->load->library('Ai_screenshot_parser');
    }

    public function index()
    {
        $data = array(
            'title' => 'Onboarding Portfolio',
            'ai_processor' => $this->ai_processor_info(),
            'content' => 'onboarding/index'
        );
        $this->load->view('layouts/main', $data);
    }

    private function ai_processor_info()
    {
        $settings = $this->Ai_settings_model->current();
        $provider = (string) $settings->provider;
        if ($provider === 'openai') {
            $provider = 'chatgpt';
        } elseif ($provider === 'openrouter') {
            $provider = 'gemini';
        }

        $provider_labels = array(
            'chatgpt' => 'ChatGPT / OpenAI',
            'gemini' => 'Gemini / Google AI Studio',
            'claude' => 'Claude / Anthropic',
            'qwen' => 'Qwen via OpenRouter',
            'llama' => 'Llama via OpenRouter',
            'custom' => 'Custom OpenAI-compatible'
        );

        $default_models = array(
            'chatgpt' => 'gpt-4o-mini',
            'gemini' => 'gemini-flash-latest',
            'claude' => 'claude-sonnet-4-5',
            'qwen' => 'qwen/qwen3.8-27b:free',
            'llama' => 'meta-llama/llama-3.2-90b-vision-instruct:free',
            'custom' => ''
        );

        $has_key = trim((string) $settings->api_key) !== '';
        $enabled = (int) $settings->enabled === 1 && $has_key;
        $model = trim((string) $settings->model);
        if ($model === '' && isset($default_models[$provider])) {
            $model = $default_models[$provider];
        }

        return array(
            'enabled' => $enabled,
            'title' => $enabled ? 'AI Vision aktif' : 'OCR lokal aktif',
            'provider' => isset($provider_labels[$provider]) ? $provider_labels[$provider] : 'AI Custom',
            'model' => $model,
            'description' => $enabled
                ? 'Screenshot akan dianalisa pakai AI lebih dulu. Kalau gagal, sistem fallback ke OCR/parser lokal.'
                : 'Screenshot akan dibaca pakai OCR/parser lokal. Aktifkan AI Settings dan isi API key kalau mau pakai AI vision.',
            'badge_class' => $enabled ? 'success' : 'muted'
        );
    }

    public function upload()
    {
        $config = array(
            'upload_path' => FCPATH . 'uploads/imports/',
            'allowed_types' => 'jpg|jpeg|png|webp',
            'max_size' => 4096,
            'encrypt_name' => TRUE
        );

        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0777, TRUE);
        }

        $platform = $this->input->post('platform', TRUE);
        $processor = $this->ai_processor_info();
        $files = $this->normalize_upload_files('screenshot');

        if (empty($files)) {
            $this->session->set_flashdata('error', 'Pilih minimal satu gambar portfolio.');
            redirect('onboarding');
        }

        $all_items = array();
        $failed = array();
        $used_ai_count = 0;
        $used_ocr_count = 0;
        $notes = array();

        foreach ($files as $index => $file_input) {
            $_FILES['screenshot_item'] = $file_input;
            $this->upload->initialize($config);

            if (!$this->upload->do_upload('screenshot_item')) {
                $failed[] = $file_input['name'] . ': ' . $this->upload->display_errors('', '');
                continue;
            }

            $file = $this->upload->data();
            $result = $this->parse_uploaded_image($file, $platform, $processor, $index + 1);
            $all_items = array_merge($all_items, $result['items']);
            $used_ai_count += $result['used_ai'] ? 1 : 0;
            $used_ocr_count += $result['used_ai'] ? 0 : 1;
            if ($result['note'] !== '') {
                $notes[] = $result['note'];
            }

            if (is_file($file['full_path'])) {
                @unlink($file['full_path']);
            }
        }

        unset($_FILES['screenshot_item']);

        if (empty($all_items) && !empty($failed)) {
            $this->session->set_flashdata('error', implode(' | ', $failed));
            redirect('onboarding');
        }

        if (!empty($failed)) {
            $notes[] = 'Beberapa gambar gagal upload: ' . implode(' | ', $failed);
        }

        $processor_notes = 'Diproses ' . count($files) . ' gambar. AI: ' . $used_ai_count . ', OCR lokal: ' . $used_ocr_count . '.';
        if (!empty($notes)) {
            $processor_notes .= ' ' . substr(implode(' | ', $notes), 0, 220);
        }

        $batch_id = $this->Import_model->create_batch(array(
            'source_type' => 'screenshot',
            'platform' => $platform,
            'file_path' => null,
            'processor_type' => $used_ai_count > 0 ? 'ai' : 'ocr',
            'processor_name' => $used_ai_count > 0 ? $processor['provider'] : 'OCR lokal',
            'processor_model' => $used_ai_count > 0 ? $processor['model'] : 'Windows OCR + parser lokal',
            'processor_notes' => $processor_notes,
            'status' => 'review'
        ));

        $this->Import_model->replace_items($batch_id, $all_items);

        redirect('onboarding/review/' . $batch_id);
    }

    private function normalize_upload_files($field)
    {
        if (empty($_FILES[$field]['name'])) {
            return array();
        }

        if (!is_array($_FILES[$field]['name'])) {
            return array($_FILES[$field]);
        }

        $files = array();
        foreach ($_FILES[$field]['name'] as $index => $name) {
            if ($name === '') {
                continue;
            }

            $files[] = array(
                'name' => $_FILES[$field]['name'][$index],
                'type' => $_FILES[$field]['type'][$index],
                'tmp_name' => $_FILES[$field]['tmp_name'][$index],
                'error' => $_FILES[$field]['error'][$index],
                'size' => $_FILES[$field]['size'][$index]
            );
        }

        return $files;
    }

    private function parse_uploaded_image($file, $platform, $processor, $number)
    {
        $parsed_items = $this->ai_screenshot_parser->parse($file['full_path'], $platform);
        $ai_error = $this->ai_screenshot_parser->last_error();
        $used_ai = !empty($parsed_items);
        $note = $used_ai ? 'Gambar ' . $number . ' diproses dengan AI Vision.' : '';

        if (empty($parsed_items)) {
            $parsed_items = $this->screenshot_parser->parse($file['full_path'], $platform);
            $note = $ai_error
                ? 'Gambar ' . $number . ' AI belum menghasilkan JSON lengkap. Fallback OCR lokal.'
                : 'Gambar ' . $number . ' fallback OCR lokal.';
        }

        if (empty($parsed_items)) {
            $ocr_text = method_exists($this->screenshot_parser, 'last_ocr_text') ? trim($this->screenshot_parser->last_ocr_text()) : '';
            $ai_note = $ai_error ? 'AI belum menghasilkan JSON lengkap. ' : '';
            $fallback_note = $ocr_text === ''
                ? $ai_note . 'OCR tidak membaca teks dari gambar ' . $number . '. Coba crop screenshot lebih dekat ke kartu aset lalu upload ulang.'
                : $ai_note . 'Gambar ' . $number . ' belum terbaca otomatis. Teks OCR: ' . substr(preg_replace('/\s+/', ' ', $ocr_text), 0, 220);
            $parsed_items = array(array(
                'asset_type' => 'lainnya',
                'platform' => $platform,
                'portfolio_name' => '',
                'name' => '',
                'symbol' => '',
                'quantity' => 0,
                'unit' => 'unit',
                'avg_price' => 0,
                'market_price' => 0,
                'invested_amount' => 0,
                'confidence' => 0,
                'notes' => $fallback_note
            ));
        } else {
            foreach ($parsed_items as &$item) {
                $prefix = 'Gambar ' . $number;
                $item['notes'] = trim($prefix . (empty($item['notes']) ? '' : ': ' . $item['notes']));
            }
            unset($item);
        }

        return array(
            'items' => $parsed_items,
            'used_ai' => $used_ai,
            'note' => $note
        );
    }

    public function review($batch_id)
    {
        $batch = $this->Import_model->batch($batch_id);
        if (!$batch) {
            show_404();
        }

        $data = array(
            'title' => 'Review Import',
            'batch' => $batch,
            'items' => $this->Import_model->items($batch_id),
            'types' => $this->Asset_model->types(),
            'units' => $this->Asset_model->units(),
            'content' => 'onboarding/review'
        );
        $this->load->view('layouts/main', $data);
    }

    public function reprocess($batch_id)
    {
        $batch = $this->Import_model->batch($batch_id);
        if (!$batch) {
            show_404();
        }

        $absolute_path = FCPATH . $batch->file_path;
        $parsed_items = is_file($absolute_path)
            ? $this->screenshot_parser->parse($absolute_path, '')
            : array();

        if (empty($parsed_items)) {
            $parsed_items = array(array(
                'asset_type' => 'lainnya',
                'platform' => $batch->platform,
                'portfolio_name' => '',
                'name' => '',
                'symbol' => '',
                'quantity' => 0,
                'unit' => 'unit',
                'avg_price' => 0,
                'market_price' => 0,
                'invested_amount' => 0,
                'confidence' => 0,
                'notes' => 'Belum terbaca otomatis setelah proses ulang. Isi manual dari screenshot.'
            ));
        }

        $this->Import_model->replace_items($batch_id, $parsed_items);
        $this->session->set_flashdata('success', 'Gambar berhasil diproses ulang dari file upload batch ini.');
        redirect('onboarding/review/' . $batch_id);
    }

    public function save($batch_id)
    {
        $names = $this->input->post('name');
        $items = array();

        foreach ($names as $i => $name) {
            if (trim($name) === '') {
                continue;
            }

            $items[] = array(
                'asset_type' => $this->input->post('asset_type')[$i],
                'platform' => $this->input->post('platform')[$i],
                'portfolio_name' => $this->input->post('portfolio_name')[$i],
                'name' => $name,
                'symbol' => $this->input->post('symbol')[$i],
                'quantity' => $this->clean_number($this->input->post('quantity')[$i]) ?: 0,
                'unit' => $this->input->post('unit')[$i],
                'avg_price' => $this->clean_number($this->input->post('avg_price')[$i]) ?: 0,
                'market_price' => $this->clean_number($this->input->post('market_price')[$i]) ?: 0,
                'invested_amount' => $this->clean_number($this->input->post('invested_amount')[$i]) ?: 0,
                'confidence' => 100,
                'notes' => $this->input->post('notes')[$i]
            );
        }

        $this->Import_model->replace_items($batch_id, $items);

        foreach ($items as $item) {
            $this->Asset_model->insert(array(
                'name' => $item['name'],
                'type' => $item['asset_type'],
                'symbol' => $item['symbol'],
                'platform' => $item['platform'],
                'portfolio_name' => $item['portfolio_name'],
                'quantity_current' => $item['quantity'],
                'unit' => $item['unit'],
                'avg_price' => $item['avg_price'],
                'market_price' => $item['market_price'],
                'invested_amount' => $item['invested_amount'],
                'target_buy_price' => $item['asset_type'] === 'emas' ? 24000 : null,
                'lot_size' => $item['asset_type'] === 'saham' ? 100 : 1,
                'min_purchase_amount' => $item['asset_type'] === 'saham' ? ((float) $item['market_price'] * 100) : 10000,
                'is_planned' => 1,
                'notes' => $item['notes']
            ));
        }

        $this->Import_model->mark_saved($batch_id);
        $this->session->set_flashdata('success', 'Portfolio hasil review berhasil disimpan.');
        redirect('dashboard');
    }
}
