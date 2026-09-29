<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Import_model extends CI_Model
{
    public function create_batch($payload)
    {
        $payload['user_id'] = (int) $this->session->userdata('user_id');
        $this->db->insert('portfolio_imports', $payload);
        return $this->db->insert_id();
    }

    public function batch($id)
    {
        return $this->db
            ->where('id', $id)
            ->where('user_id', (int) $this->session->userdata('user_id'))
            ->get('portfolio_imports')
            ->row();
    }

    public function items($batch_id)
    {
        return $this->db
            ->where('import_id', $batch_id)
            ->order_by('id')
            ->get('portfolio_import_items')
            ->result();
    }

    public function replace_items($batch_id, $items)
    {
        $this->db->where('import_id', $batch_id)->delete('portfolio_import_items');

        foreach ($items as $item) {
            if (trim($item['name']) === '' && (!isset($item['confidence']) || (int) $item['confidence'] > 0)) {
                continue;
            }

            $item['import_id'] = $batch_id;
            $this->db->insert('portfolio_import_items', $item);
        }
    }

    public function mark_saved($batch_id)
    {
        return $this->db
            ->where('id', $batch_id)
            ->where('user_id', (int) $this->session->userdata('user_id'))
            ->update('portfolio_imports', array('status' => 'saved'));
    }
}
