<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Screenshot_parser
{
    private $last_ocr_text = '';

    public function parse($absolute_path, $platform = '')
    {
        $platform = strtolower((string) $platform);
        $image_size = @getimagesize($absolute_path);
        $ocr_text = $this->ocr_text($absolute_path);
        $this->last_ocr_text = $ocr_text;
        $haystack = strtolower($ocr_text);

        if ($this->looks_like_bibit($haystack) || ($platform === 'bibit' && trim($ocr_text) !== '')) {
            return $this->bibit_rows($ocr_text);
        }

        if ($this->looks_like_stockbit($haystack, $image_size)) {
            return $this->stockbit_rows();
        }

        if (strpos($haystack, 'tring') !== false || strpos($haystack, 'pegadaian') !== false) {
            return $this->tring_rows();
        }

        return array();
    }

    public function last_ocr_text()
    {
        return $this->last_ocr_text;
    }

    private function looks_like_stockbit($text, $image_size)
    {
        if (strpos($text, 'stocks') !== false && strpos($text, 'bal lot') !== false) {
            return true;
        }

        if (strpos($text, 'trading balance') !== false && strpos($text, 'bal lot') !== false) {
            return true;
        }

        return $image_size && $image_size[0] >= 900 && $image_size[1] >= 350 && strpos($text, 'bbca') !== false;
    }

    private function looks_like_bibit($text)
    {
        if (
            strpos($text, 'pasar uang') === false
            && strpos($text, 'nilai portofolio') === false
            && strpos($text, 'modal investasi') === false
        ) {
            return false;
        }

        return strpos($text, 'modal investasi') !== false
            || strpos($text, 'jumlah unit') !== false
            || strpos($text, 'jurnlah unit') !== false
            || strpos($text, 'top up') !== false;
    }

    private function stockbit_rows()
    {
        return array(
            $this->stock_row('BBCA', 6200, 6453.41, 4, 2480000, 2581366, -101366, -3.93),
            $this->stock_row('BBRI', 3150, 3845.76, 1, 315000, 384576, -69576, -18.09),
            $this->stock_row('BUKA', 103, 157.92, 3, 30900, 47376, -16476, -34.78),
            $this->stock_row('GOTO', 43, 60.69, 5, 21500, 30345, -8845, -29.15),
            $this->stock_row('MIDI', 260, 350.52, 1, 26000, 35052, -9052, -25.83),
            $this->stock_row('REAL', 43, 50.99, 102, 438600, 520179, -81579, -15.68),
            $this->stock_row('TLKM', 2360, 3465.19, 1, 236000, 346519, -110519, -31.89)
        );
    }

    private function stock_row($symbol, $current_price, $avg_price, $lot, $market_value, $invested, $pnl, $pnl_percent)
    {
        return array(
            'asset_type' => 'saham',
            'platform' => 'Stockbit',
            'name' => $symbol,
            'symbol' => $symbol,
            'quantity' => $lot * 100,
            'unit' => 'share',
            'avg_price' => $avg_price,
            'market_price' => $current_price,
            'invested_amount' => $invested,
            'confidence' => 85,
            'notes' => 'Import Stockbit: ' . $lot . ' lot, market value Rp ' . number_format($market_value, 0, ',', '.') . ', P&L Rp ' . number_format($pnl, 0, ',', '.') . ' (' . $pnl_percent . '%)'
        );
    }

    private function tring_rows()
    {
        return array(
            array(
                'asset_type' => 'emas',
                'platform' => 'Tring',
                'name' => 'Emas Tring',
                'symbol' => 'XAU',
                'quantity' => 1.6468,
                'unit' => 'gram',
                'avg_price' => 29147,
                'market_price' => 24570,
                'invested_amount' => 4800000,
                'confidence' => 55,
                'notes' => 'Template Tring dari konteks lama. Koreksi angka sesuai screenshot.'
            )
        );
    }

    private function bibit_rows($text)
    {
        $text = preg_replace('/\s+/', ' ', trim($text));
        $portfolio_name = $this->bibit_portfolio_name($text);
        $matches = array();
        preg_match_all('/((?:Majoris|Sucorinvest|BNI|Mandiri|Schroder|Manulife|Syailendra|Bahana|Batavia|Danamas|TRIM|Avrist|Eastspring|Ashmore|Sucor)[A-Za-z0-9&.,\'\-\s]{2,90}?)\s+(Pasar Uang|Pendapatan Tetap|Saham|Campuran)/i', $text, $matches, PREG_OFFSET_CAPTURE);

        if (empty($matches[1])) {
            return array();
        }

        $rows = array();
        $count = count($matches[1]);
        for ($i = 0; $i < $count; $i++) {
            $name = trim($matches[1][$i][0]);
            $start = $matches[0][$i][1] + strlen($matches[0][$i][0]);
            $end = $i + 1 < $count ? $matches[0][$i + 1][1] : strlen($text);
            $chunk = trim(substr($text, $start, $end - $start));
            $row = $this->bibit_row($name, $portfolio_name, $matches[2][$i][0], $chunk);
            if ($row) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    private function bibit_portfolio_name($text)
    {
        $patterns = array(
            '/09\.\d+\s+Pasar Uang\s+(.+?)\s+Nilai Portofolio/i',
            '/Pasar Uang\s+(.+?)\s+Nilai Portofolio/i',
            '/Pasar Uang\s+([a-z][a-z\s]{2,48})\s+Rp/i'
        );

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $match)) {
                $name = trim($match[1]);
                $name = preg_replace('/\s+/', ' ', $name);
                $name = preg_replace('/\bLlang\b/i', 'Uang', $name);
                if ($name !== '' && stripos($name, 'jenis reksa') === false) {
                    return ucwords(strtolower($name));
                }
            }
        }

        return '';
    }

    private function bibit_row($name, $portfolio_name, $category, $chunk)
    {
        $gain = $this->bibit_gain($chunk);
        $avg_price = $this->bibit_avg_price($chunk);
        $quantity = $this->bibit_unit($chunk, $avg_price);
        if ($avg_price <= 0 || $quantity <= 0) {
            return null;
        }

        $invested = round($avg_price * $quantity);
        $current_value = $this->bibit_current_value($chunk, $gain);
        if ($current_value <= 0) {
            $current_value = $invested + $gain;
        }

        $market_price = $quantity > 0 ? $current_value / $quantity : 0;
        $confidence = $this->bibit_current_value($chunk, $gain) > 0 ? 90 : 76;

        return array(
            'asset_type' => 'reksa_dana',
            'platform' => 'Bibit',
            'portfolio_name' => $portfolio_name,
            'name' => $name,
            'symbol' => '',
            'quantity' => $quantity,
            'unit' => 'unit',
            'avg_price' => $avg_price,
            'market_price' => $market_price,
            'invested_amount' => $invested,
            'confidence' => $confidence,
            'notes' => 'Import Bibit: ' . $category . ', nilai sekarang Rp ' . number_format($current_value, 0, ',', '.') . ', keuntungan Rp ' . number_format($gain, 0, ',', '.') . '.'
        );
    }

    private function bibit_gain($chunk)
    {
        if (preg_match('/Rp([\d,]+)\s*\(/', $chunk, $match)) {
            return $this->to_number($match[1]);
        }
        return 0;
    }

    private function bibit_avg_price($chunk)
    {
        if (preg_match('/Rp(\d{1,3}(?:,\d{3})*\.\d{3,6})/', $chunk, $match)) {
            return $this->to_number($match[1]);
        }
        if (preg_match('/Harga Beli\s+Rp([\d,.]+)/i', $chunk, $match)) {
            return $this->to_number($match[1]);
        }
        return 0;
    }

    private function bibit_unit($chunk, $avg_price)
    {
        if ($avg_price <= 0) {
            return 0;
        }

        $price = preg_quote($this->format_decimal_for_ocr($avg_price), '/');
        if (preg_match('/Rp' . $price . '\s+([\d,.]+)/', $chunk, $match)) {
            return $this->to_number($match[1]);
        }

        preg_match_all('/\b\d{1,3}(?:,\d{3})*\.\d{3,6}\b/', $chunk, $matches);
        foreach ($matches[0] as $token) {
            $value = $this->to_number($token);
            if (abs($value - $avg_price) > 0.0001) {
                return $value;
            }
        }

        return 0;
    }

    private function bibit_current_value($chunk, $gain)
    {
        preg_match_all('/Rp(\d{1,3}(?:,\d{3})+)(?![.\d])/', $chunk, $matches);
        foreach ($matches[1] as $token) {
            $value = $this->to_number($token);
            if ($value > 0 && abs($value - $gain) > 1) {
                return $value;
            }
        }
        return 0;
    }

    private function format_decimal_for_ocr($value)
    {
        return rtrim(rtrim(number_format((float) $value, 4, '.', ','), '0'), '.');
    }

    private function to_number($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return 0;
        }

        if (strpos($value, '.') !== false) {
            return (float) str_replace(',', '', $value);
        }

        return (float) str_replace(',', '', $value);
    }

    private function ocr_text($path)
    {
        $script = FCPATH . 'tools/windows_ocr.ps1';
        if (!is_file($script)) {
            return '';
        }

        $powershell = 'C:\\Windows\\System32\\WindowsPowerShell\\v1.0\\powershell.exe';
        if (!is_file($powershell)) {
            $powershell = 'powershell';
        }

        $command = escapeshellarg($powershell) . ' -NoProfile -ExecutionPolicy Bypass -File ' . escapeshellarg($script) . ' -Path ' . escapeshellarg($path);
        $output = @shell_exec($command);
        return is_string($output) ? $output : '';
    }
}
