<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Settings extends MY_Controller
{
    public function ai()
    {
        $data = array(
            'title' => 'AI Settings',
            'settings' => $this->Ai_settings_model->current(),
            'content' => 'settings/ai'
        );
        $this->load->view('layouts/main', $data);
    }

    public function save_ai()
    {
        $provider = $this->input->post('provider', TRUE);
        if (!in_array($provider, array('chatgpt', 'gemini', 'claude', 'qwen', 'llama', 'custom'), true)) {
            $provider = 'chatgpt';
        }

        $this->Ai_settings_model->save(array(
            'enabled' => $this->input->post('enabled') ? 1 : 0,
            'provider' => $provider,
            'api_key' => trim((string) $this->input->post('api_key', TRUE)),
            'base_url' => trim((string) $this->input->post('base_url', TRUE)),
            'model' => trim((string) $this->input->post('model', TRUE))
        ));

        $this->session->set_flashdata('success', 'Pengaturan AI berhasil disimpan.');
        redirect('settings/ai');
    }
}
