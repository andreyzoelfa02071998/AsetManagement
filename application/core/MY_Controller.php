<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->session->userdata('user_id')) {
            $this->session->set_userdata('redirect_after_login', current_url());
            redirect('login');
        }
    }

    protected function clean_number($value)
    {
        $value = trim((string) $value);
        $value = str_replace(' ', '', $value);
        $value = preg_replace('/[^0-9,.\-]/', '', $value);

        if (strpos($value, ',') !== false) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
            return preg_replace('/[^0-9.\-]/', '', $value);
        }

        if (substr_count($value, '.') > 1) {
            return str_replace('.', '', $value);
        }

        if (preg_match('/^-?\d{1,3}\.\d{3}$/', $value)) {
            return str_replace('.', '', $value);
        }

        return preg_replace('/[^0-9.\-]/', '', $value);
    }
}
