<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model
{
    public function find_by_email($email)
    {
        return $this->db->where('email', $email)->get('users')->row();
    }

    public function create($payload)
    {
        $payload['password_hash'] = password_hash($payload['password'], PASSWORD_DEFAULT);
        unset($payload['password']);
        $this->db->insert('users', $payload);
        return $this->db->insert_id();
    }
}

