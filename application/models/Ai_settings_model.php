<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ai_settings_model extends CI_Model
{
    private function current_user_id()
    {
        return (int) $this->session->userdata('user_id');
    }

    public function current()
    {
        $row = $this->db
            ->where('user_id', $this->current_user_id())
            ->get('ai_settings')
            ->row();

        if ($row) {
            return $row;
        }

        return (object) array(
            'enabled' => 0,
            'provider' => 'chatgpt',
            'api_key' => '',
            'base_url' => '',
            'model' => ''
        );
    }

    public function save($payload)
    {
        $payload['user_id'] = $this->current_user_id();
        $existing = $this->db
            ->where('user_id', $payload['user_id'])
            ->get('ai_settings')
            ->row();

        if ($existing) {
            return $this->db
                ->where('user_id', $payload['user_id'])
                ->update('ai_settings', $payload);
        }

        return $this->db->insert('ai_settings', $payload);
    }
}
