<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ai_screenshot_parser
{
    private $last_error = '';

    public function is_enabled()
    {
        $settings = $this->settings();
        return (int) $settings->enabled === 1 && $this->api_key($settings) !== '';
    }

    public function last_error()
    {
        return $this->last_error;
    }

    public function parse($absolute_path, $platform = '')
    {
        $this->last_error = '';

        $settings = $this->settings();
        if (!$this->is_enabled()) {
            $this->last_error = 'AI belum aktif atau API key belum diisi.';
            return array();
        }

        if (!is_file($absolute_path)) {
            $this->last_error = 'File gambar tidak ditemukan.';
            return array();
        }

        $mime = $this->mime_type($absolute_path);
        $image_data = base64_encode(file_get_contents($absolute_path));
        if ($image_data === '') {
            $this->last_error = 'File gambar kosong atau gagal dibaca.';
            return array();
        }

        $provider = $this->provider($settings);
        if ($provider === 'chatgpt') {
            $response = $this->request_responses($settings, $mime, $image_data, $platform);
        } elseif ($provider === 'gemini') {
            $response = $this->request_gemini($settings, $mime, $image_data, $platform);
        } elseif ($provider === 'claude') {
            $response = $this->request_claude($settings, $mime, $image_data, $platform);
        } else {
            $response = $this->request_chat_completions($settings, $mime, $image_data, $platform);
        }
        if (!$response) {
            return array();
        }

        return $this->normalize_rows($response);
    }

    public function analyze_planner($payload)
    {
        $this->last_error = '';

        $settings = $this->settings();
        if (!$this->is_enabled()) {
            $this->last_error = 'AI belum aktif atau API key belum diisi.';
            return array();
        }

        $prompt = $this->planner_prompt($payload);
        $provider = $this->provider($settings);
        if ($provider === 'chatgpt') {
            $response = $this->request_text_responses($settings, $prompt);
        } elseif ($provider === 'gemini') {
            $response = $this->request_text_gemini($settings, $prompt);
        } elseif ($provider === 'claude') {
            $response = $this->request_text_claude($settings, $prompt);
        } else {
            $response = $this->request_text_chat_completions($settings, $prompt);
        }

        if (!$response) {
            return array();
        }

        return $this->normalize_planner($response);
    }

    private function prompt($platform)
    {
        return 'Analisa screenshot portofolio aset Indonesia dari aplikasi seperti Bibit, Stockbit, Tring, Pegadaian, atau broker lain. '
            . 'Ekstrak semua aset yang terlihat sebagai JSON array saja, tanpa markdown. '
            . 'Schema per item: asset_type, platform, portfolio_name, name, symbol, quantity, unit, avg_price, market_price, invested_amount, confidence, notes. '
            . 'Gunakan asset_type salah satu: emas, saham, reksa_dana, crypto, properti, kas, rdn, lainnya. '
            . 'Untuk saham Stockbit: quantity = lot * 100, unit = share, avg_price = Avg Price, market_price = Current Price, invested_amount = Invested. '
            . 'Untuk reksadana Bibit: portfolio_name adalah nama goal/portofolio kecil di bawah judul; quantity = Jumlah Unit; avg_price = Harga Beli; invested_amount = Modal Investasi; market_price = Nilai Sekarang / Jumlah Unit. '
            . 'Untuk emas: quantity dalam gram bila terlihat; market_price harga per gram atau per unit yang terlihat; notes jelaskan satuannya. '
            . 'Jangan mengarang data yang tidak terlihat. Kalau angka tidak ada, isi 0. Platform pilihan user: ' . (string) $platform . '.';
    }

    private function planner_prompt($payload)
    {
        return 'Kamu adalah analis portfolio pribadi yang konservatif untuk investor ritel Indonesia. '
            . 'Tugasmu memberi analisa planning investasi yang masuk akal, aman, dan tetap mencari peluang menguntungkan. '
            . 'Jangan menjanjikan profit. Jangan menyarankan leverage, all-in, atau spekulasi agresif. '
            . 'RDN boleh dianggap modal tambahan hanya untuk beli saham/reksadana. Emas hanya memakai budget plan baru, bukan saldo RDN. '
            . 'Nominal dan simulasi AVG sudah dihitung aplikasi; jangan ubah angka eksekusi, cukup nilai apakah masuk akal dan beri alasan. '
            . 'Balas JSON object saja tanpa markdown dengan schema: risk_level, headline, strategy, market_view, cash_policy, warnings array, item_notes object. '
            . 'item_notes key harus asset_id dan value berisi alasan singkat untuk alokasi tersebut. Data: '
            . json_encode($payload);
    }

    private function request_text_responses($settings, $prompt)
    {
        $payload = array(
            'model' => $this->model($settings, 'gpt-4o-mini'),
            'input' => array(
                array(
                    'role' => 'user',
                    'content' => array(
                        array('type' => 'input_text', 'text' => $prompt)
                    )
                )
            ),
            'max_output_tokens' => 1400
        );

        $json = $this->post_json('https://api.openai.com/v1/responses', $settings, $payload);
        return $json ? $this->extract_text($json) : null;
    }

    private function request_text_gemini($settings, $prompt)
    {
        $payload = array(
            'contents' => array(
                array(
                    'role' => 'user',
                    'parts' => array(array('text' => $prompt))
                )
            ),
            'generationConfig' => array(
                'temperature' => 0.15,
                'maxOutputTokens' => 1400,
                'responseMimeType' => 'application/json'
            )
        );

        $json = $this->post_gemini_with_fallbacks($settings, $payload, 'gemini-flash-latest', 30);
        if (!$json || empty($json['candidates'][0]['content']['parts'])) {
            $this->last_error = $this->last_error ?: 'Gemini tidak mengembalikan analisa planner.';
            return null;
        }

        $parts = array();
        foreach ($json['candidates'][0]['content']['parts'] as $part) {
            if (isset($part['text'])) {
                $parts[] = $part['text'];
            }
        }
        return trim(implode("\n", $parts));
    }

    private function request_text_claude($settings, $prompt)
    {
        $payload = array(
            'model' => $this->model($settings, 'claude-sonnet-4-5'),
            'max_tokens' => 1400,
            'temperature' => 0.15,
            'messages' => array(
                array(
                    'role' => 'user',
                    'content' => array(array('type' => 'text', 'text' => $prompt))
                )
            )
        );

        $json = $this->post_json('https://api.anthropic.com/v1/messages', $settings, $payload);
        if (!$json || empty($json['content'])) {
            $this->last_error = $this->last_error ?: 'Claude tidak mengembalikan analisa planner.';
            return null;
        }

        $parts = array();
        foreach ($json['content'] as $content) {
            if (isset($content['text'])) {
                $parts[] = $content['text'];
            }
        }
        return trim(implode("\n", $parts));
    }

    private function request_text_chat_completions($settings, $prompt)
    {
        $payload = array(
            'model' => $this->model($settings, $this->default_model($settings)),
            'messages' => array(
                array('role' => 'user', 'content' => $prompt)
            ),
            'max_tokens' => 1400,
            'temperature' => 0.15
        );

        $endpoint = $this->chat_endpoint($settings);
        if ($endpoint === '') {
            $this->last_error = 'Base URL custom belum diisi.';
            return null;
        }

        $json = $this->post_json($endpoint, $settings, $payload);
        if (!$json || !isset($json['choices'][0]['message']['content'])) {
            $this->last_error = $this->last_error ?: 'Provider AI tidak mengembalikan analisa planner.';
            return null;
        }

        return trim((string) $json['choices'][0]['message']['content']);
    }

    private function request_responses($settings, $mime, $image_data, $platform)
    {
        $payload = array(
            'model' => $this->model($settings, 'gpt-4o-mini'),
            'input' => array(
                array(
                    'role' => 'user',
                    'content' => array(
                        array(
                            'type' => 'input_text',
                            'text' => $this->prompt($platform)
                        ),
                        array(
                            'type' => 'input_image',
                            'image_url' => 'data:' . $mime . ';base64,' . $image_data,
                            'detail' => 'high'
                        )
                    )
                )
            ),
            'max_output_tokens' => 4096
        );

        $json = $this->post_json('https://api.openai.com/v1/responses', $settings, $payload);
        if (!$json) {
            return null;
        }

        $text = $this->extract_text($json);
        if ($text === '') {
            $this->last_error = 'AI tidak mengembalikan teks hasil analisa.';
            return null;
        }

        return $text;
    }

    private function request_chat_completions($settings, $mime, $image_data, $platform)
    {
        $payload = array(
            'model' => $this->model($settings, $this->default_model($settings)),
            'messages' => array(
                array(
                    'role' => 'user',
                    'content' => array(
                        array(
                            'type' => 'text',
                            'text' => $this->prompt($platform)
                        ),
                        array(
                            'type' => 'image_url',
                            'image_url' => array(
                                'url' => 'data:' . $mime . ';base64,' . $image_data
                            )
                        )
                    )
                )
            ),
            'max_tokens' => 4096
        );

        $endpoint = $this->chat_endpoint($settings);
        if ($endpoint === '') {
            $this->last_error = 'Base URL custom belum diisi.';
            return null;
        }

        $json = $this->post_json($endpoint, $settings, $payload);
        if (!$json) {
            return null;
        }

        if (isset($json['choices'][0]['message']['content'])) {
            return trim((string) $json['choices'][0]['message']['content']);
        }

        $this->last_error = 'Provider AI tidak mengembalikan format chat completion.';
        return null;
    }

    private function request_gemini($settings, $mime, $image_data, $platform)
    {
        $payload = array(
            'contents' => array(
                array(
                    'role' => 'user',
                    'parts' => array(
                        array('text' => $this->prompt($platform)),
                        array(
                            'inline_data' => array(
                                'mime_type' => $mime,
                                'data' => $image_data
                            )
                        )
                    )
                )
            ),
            'generationConfig' => array(
                'temperature' => 0.1,
                'maxOutputTokens' => 4096,
                'responseMimeType' => 'application/json'
            )
        );

        $json = $this->post_gemini_with_fallbacks($settings, $payload, 'gemini-flash-latest', 35);
        if (!$json) {
            return null;
        }

        if (!empty($json['candidates'][0]['content']['parts'])) {
            $parts = array();
            foreach ($json['candidates'][0]['content']['parts'] as $part) {
                if (isset($part['text'])) {
                    $parts[] = $part['text'];
                }
            }
            return trim(implode("\n", $parts));
        }

        $this->last_error = 'Gemini tidak mengembalikan teks hasil analisa.';
        return null;
    }

    private function post_gemini_with_fallbacks($settings, $payload, $default_model, $timeout = 12)
    {
        $preferred = $this->model($settings, $default_model);
        $models = array_values(array_unique(array_filter(array(
            $preferred,
            'gemini-flash-latest',
            'gemini-3.5-flash-lite',
            'gemini-3.1-flash-lite',
            'gemini-3.5-flash',
            'gemini-3.6-flash',
            'gemini-3.7-flash',
            'gemini-3.8-flash'
        ))));

        $errors = array();
        $attempts = 0;
        foreach ($models as $model) {
            $attempts++;
            if ($attempts > 6) {
                break;
            }

            $this->last_error = '';
            $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($model) . ':generateContent?key=' . rawurlencode($this->api_key($settings));
            $json = $this->post_json($url, $settings, $payload, false, $timeout);
            if ($json) {
                if ($model !== $preferred) {
                    $this->last_error = 'Gemini model utama sedang sibuk, berhasil fallback ke ' . $model . '.';
                }
                return $json;
            }

            $errors[] = $model . ': ' . $this->last_error;
            if (!$this->is_retryable_ai_error($this->last_error)) {
                break;
            }

            usleep(min(2500000, 350000 * $attempts));
        }

        $this->last_error = $this->summarize_gemini_errors($errors);
        return null;
    }

    private function summarize_gemini_errors($errors)
    {
        $raw = implode(' | ', $errors);
        $lower = strtolower($raw);

        if (strpos($lower, '503') !== false || strpos($lower, 'unavailable') !== false || strpos($lower, 'high demand') !== false) {
            return 'Gemini sedang high demand/UNAVAILABLE di sisi Google setelah mencoba beberapa model fallback. Sistem fallback ke OCR lokal; coba lagi beberapa menit lagi atau pakai provider AI lain.';
        }

        if (strpos($lower, '429') !== false || strpos($lower, 'quota') !== false || strpos($lower, 'rate limit') !== false || strpos($lower, 'resource_exhausted') !== false) {
            return 'Kuota Gemini API sudah habis atau terkena rate limit. Cek plan, billing, dan quota Google AI Studio; sementara sistem fallback ke OCR lokal.';
        }

        if (strpos($lower, 'timed out') !== false || strpos($lower, 'timeout') !== false) {
            return 'Gemini timeout setelah mencoba beberapa model fallback. Sistem fallback ke OCR lokal; coba lagi nanti atau pakai model/provider yang lebih ringan.';
        }

        return substr($raw, 0, 420);
    }

    private function is_retryable_ai_error($message)
    {
        $message = strtolower((string) $message);
        return strpos($message, '503') !== false
            || strpos($message, 'unavailable') !== false
            || strpos($message, 'high demand') !== false
            || strpos($message, 'timed out') !== false
            || strpos($message, 'timeout') !== false
            || strpos($message, 'overload') !== false
            || strpos($message, 'temporarily') !== false;
    }

    private function request_claude($settings, $mime, $image_data, $platform)
    {
        $payload = array(
            'model' => $this->model($settings, 'claude-sonnet-4-5'),
            'max_tokens' => 4096,
            'temperature' => 0.1,
            'messages' => array(
                array(
                    'role' => 'user',
                    'content' => array(
                        array(
                            'type' => 'image',
                            'source' => array(
                                'type' => 'base64',
                                'media_type' => $mime,
                                'data' => $image_data
                            )
                        ),
                        array(
                            'type' => 'text',
                            'text' => $this->prompt($platform)
                        )
                    )
                )
            )
        );

        $json = $this->post_json('https://api.anthropic.com/v1/messages', $settings, $payload);
        if (!$json) {
            return null;
        }

        if (!empty($json['content']) && is_array($json['content'])) {
            $parts = array();
            foreach ($json['content'] as $content) {
                if (isset($content['text'])) {
                    $parts[] = $content['text'];
                }
            }
            return trim(implode("\n", $parts));
        }

        $this->last_error = 'Claude tidak mengembalikan teks hasil analisa.';
        return null;
    }

    private function post_json($url, $settings, $payload, $with_auth = true, $timeout = 25)
    {
        $headers = array(
            'Content-Type: application/json'
        );

        if ($with_auth && $this->provider($settings) === 'claude') {
            $headers[] = 'x-api-key: ' . $this->api_key($settings);
            $headers[] = 'anthropic-version: 2023-06-01';
        } elseif ($with_auth) {
            $headers[] = 'Authorization: Bearer ' . $this->api_key($settings);
        }

        if ($this->uses_openrouter($settings)) {
            $headers[] = 'HTTP-Referer: http://127.0.0.1:8090';
            $headers[] = 'X-Title: Analisa Aset';
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => json_encode($payload)
        ));

        $body = curl_exec($ch);
        $error = curl_error($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($body === false || $status >= 400) {
            if (stripos($error, 'SSL certificate') !== false) {
                $this->last_error = 'Koneksi AI gagal karena sertifikat SSL XAMPP/PHP belum lengkap.';
                return null;
            }

            $this->last_error = $error ?: ('AI API gagal. HTTP ' . $status . ': ' . substr((string) $body, 0, 240));
            return null;
        }

        $json = json_decode($body, true);
        if (!is_array($json)) {
            $this->last_error = 'Response AI bukan JSON valid.';
            return array();
        }

        return $json;
    }

    private function extract_text($json)
    {
        if (isset($json['output_text']) && is_string($json['output_text'])) {
            return trim($json['output_text']);
        }

        $parts = array();
        if (!empty($json['output']) && is_array($json['output'])) {
            foreach ($json['output'] as $item) {
                if (empty($item['content']) || !is_array($item['content'])) {
                    continue;
                }
                foreach ($item['content'] as $content) {
                    if (isset($content['text']) && is_string($content['text'])) {
                        $parts[] = $content['text'];
                    }
                }
            }
        }

        return trim(implode("\n", $parts));
    }

    private function normalize_rows($text)
    {
        $original_text = trim($text);
        $text = $this->extract_json_payload($original_text, '[', ']');

        $rows = json_decode($text, true);
        if (!is_array($rows)) {
            $this->last_error = 'JSON AI gagal dibaca atau terpotong. Potongan respons: ' . substr($original_text, 0, 220);
            return array();
        }

        $normalized = array();
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $name = trim((string) (isset($row['name']) ? $row['name'] : ''));
            if ($name === '') {
                continue;
            }

            $normalized[] = array(
                'asset_type' => $this->asset_type(isset($row['asset_type']) ? $row['asset_type'] : 'lainnya'),
                'platform' => trim((string) (isset($row['platform']) ? $row['platform'] : '')),
                'portfolio_name' => trim((string) (isset($row['portfolio_name']) ? $row['portfolio_name'] : '')),
                'name' => $name,
                'symbol' => trim((string) (isset($row['symbol']) ? $row['symbol'] : '')),
                'quantity' => $this->number(isset($row['quantity']) ? $row['quantity'] : 0),
                'unit' => trim((string) (isset($row['unit']) ? $row['unit'] : 'unit')) ?: 'unit',
                'avg_price' => $this->number(isset($row['avg_price']) ? $row['avg_price'] : 0),
                'market_price' => $this->number(isset($row['market_price']) ? $row['market_price'] : 0),
                'invested_amount' => $this->number(isset($row['invested_amount']) ? $row['invested_amount'] : 0),
                'confidence' => max(0, min(100, (int) (isset($row['confidence']) ? $row['confidence'] : 80))),
                'notes' => 'AI Vision: ' . trim((string) (isset($row['notes']) ? $row['notes'] : ''))
            );
        }

        return $normalized;
    }

    private function normalize_planner($text)
    {
        $original_text = trim($text);
        $text = $this->extract_json_payload($original_text, '{', '}');

        $json = json_decode($text, true);
        if (!is_array($json)) {
            $this->last_error = 'JSON AI planner gagal dibaca atau terpotong: ' . substr($original_text, 0, 220);
            return array();
        }

        return array(
            'risk_level' => isset($json['risk_level']) ? (string) $json['risk_level'] : 'moderate',
            'headline' => isset($json['headline']) ? (string) $json['headline'] : '',
            'strategy' => isset($json['strategy']) ? (string) $json['strategy'] : '',
            'market_view' => isset($json['market_view']) ? (string) $json['market_view'] : '',
            'cash_policy' => isset($json['cash_policy']) ? (string) $json['cash_policy'] : '',
            'warnings' => isset($json['warnings']) && is_array($json['warnings']) ? $json['warnings'] : array(),
            'item_notes' => isset($json['item_notes']) && is_array($json['item_notes']) ? $json['item_notes'] : array()
        );
    }

    private function extract_json_payload($text, $open, $close)
    {
        $text = trim($text);
        $text = preg_replace('/^```(?:json)?\s*/i', '', $text);
        $text = preg_replace('/\s*```$/', '', $text);

        $start = strpos($text, $open);
        $end = strrpos($text, $close);
        if ($start !== false && $end !== false && $end > $start) {
            return substr($text, $start, $end - $start + 1);
        }

        return $text;
    }

    private function asset_type($type)
    {
        $type = strtolower(trim((string) $type));
        $allowed = array('emas', 'saham', 'reksa_dana', 'crypto', 'properti', 'kas', 'rdn', 'lainnya');
        return in_array($type, $allowed, true) ? $type : 'lainnya';
    }

    private function number($value)
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        $value = trim((string) $value);
        $value = str_replace(array('Rp', 'rp', 'IDR', ' '), '', $value);
        if (strpos($value, ',') !== false && strpos($value, '.') !== false) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } elseif (strpos($value, ',') !== false) {
            $value = str_replace(',', '.', $value);
        }

        return (float) preg_replace('/[^\d.\-]/', '', $value);
    }

    private function mime_type($path)
    {
        $mime = function_exists('mime_content_type') ? mime_content_type($path) : '';
        if ($mime) {
            return $mime;
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if ($ext === 'png') {
            return 'image/png';
        }
        if ($ext === 'webp') {
            return 'image/webp';
        }

        return 'image/jpeg';
    }

    private function settings()
    {
        $ci =& get_instance();
        if (!isset($ci->Ai_settings_model)) {
            $ci->load->model('Ai_settings_model');
        }
        return $ci->Ai_settings_model->current();
    }

    private function model($settings, $fallback)
    {
        $model = trim((string) $settings->model);
        return $model !== '' ? $model : $fallback;
    }

    private function chat_endpoint($settings)
    {
        if ($this->uses_openrouter($settings)) {
            return 'https://openrouter.ai/api/v1/chat/completions';
        }

        $base = rtrim((string) $settings->base_url, '/');
        if ($base === '') {
            return '';
        }

        if (substr($base, -17) === '/chat/completions') {
            return $base;
        }

        return $base . '/chat/completions';
    }

    private function api_key($settings)
    {
        $key = trim((string) $settings->api_key);
        if ($key !== '') {
            return $key;
        }

        $env_keys = array(
            'chatgpt' => 'OPENAI_API_KEY',
            'gemini' => 'GEMINI_API_KEY',
            'claude' => 'ANTHROPIC_API_KEY'
        );
        $provider = $this->provider($settings);
        $key = getenv(isset($env_keys[$provider]) ? $env_keys[$provider] : 'OPENROUTER_API_KEY');
        return is_string($key) ? trim($key) : '';
    }

    private function provider($settings)
    {
        $provider = (string) $settings->provider;
        if ($provider === 'openai') {
            return 'chatgpt';
        }
        if ($provider === 'openrouter') {
            return 'gemini';
        }
        return $provider;
    }

    private function uses_openrouter($settings)
    {
        return in_array($this->provider($settings), array('qwen', 'llama'), true);
    }

    private function default_model($settings)
    {
        $provider = $this->provider($settings);
        $defaults = array(
            'gemini' => 'gemini-flash-latest',
            'claude' => 'claude-sonnet-4-5',
            'qwen' => 'qwen/qwen3.8-27b:free',
            'llama' => 'meta-llama/llama-3.2-90b-vision-instruct:free',
            'custom' => ''
        );

        return isset($defaults[$provider]) ? $defaults[$provider] : '';
    }
}
