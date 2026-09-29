<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Assets extends MY_Controller
{
    public function index()
    {
        $data = array(
            'title' => 'Master Aset',
            'assets' => $this->Asset_model->all(),
            'types' => $this->Asset_model->types(),
            'content' => 'assets/index'
        );

        $this->load->view('layouts/main', $data);
    }

    public function create()
    {
        $this->save();
    }

    public function edit($id)
    {
        $asset = $this->Asset_model->find($id);
        if (!$asset) {
            show_404();
        }

        $this->save($id, $asset);
    }

    private function save($id = null, $asset = null)
    {
        foreach (array('quantity_current', 'avg_price', 'market_price', 'invested_amount', 'target_buy_price', 'price_alert_target', 'lot_size', 'min_purchase_amount', 'target_allocation') as $field) {
            if ($this->input->post($field) !== null) {
                $_POST[$field] = $this->clean_number($this->input->post($field));
            }
        }

        $this->form_validation->set_rules('name', 'Nama aset', 'required|max_length[120]');
        $this->form_validation->set_rules('type', 'Tipe aset', 'required');

        if ($this->form_validation->run()) {
            $type = $this->input->post('type', TRUE);
            $quantity = $this->input->post('quantity_current') ?: 0;
            $unit = $this->input->post('unit', TRUE);
            $avg_price = $this->input->post('avg_price') ?: 0;
            $market_price = $this->input->post('market_price') ?: 0;
            $invested_amount = $this->input->post('invested_amount') ?: 0;
            $min_purchase_amount = $this->input->post('min_purchase_amount') ?: 0;
            $is_planned = $this->input->post('is_planned') ? 1 : 0;
            $price_alert_enabled = $this->input->post('price_alert_enabled') ? 1 : 0;
            $price_alert_target = $this->input->post('price_alert_target') ?: null;
            $price_alert_direction = $this->input->post('price_alert_direction', TRUE);

            if (!in_array($price_alert_direction, array('below', 'above'), true)) {
                $price_alert_direction = 'below';
            }

            if ($type === 'rdn') {
                $unit = 'idr';
                $avg_price = 1;
                $market_price = 1;
                $invested_amount = $invested_amount ?: $quantity;
                $min_purchase_amount = 0;
                $is_planned = 0;
                $price_alert_enabled = 0;
                $price_alert_target = null;
            }

            if ($type === 'kas') {
                $price_alert_enabled = 0;
                $price_alert_target = null;
            }

            if (!$price_alert_enabled || !$price_alert_target) {
                $price_alert_enabled = 0;
                $price_alert_target = null;
            }

            $payload = array(
                'name' => $this->input->post('name', TRUE),
                'type' => $type,
                'symbol' => $this->input->post('symbol', TRUE),
                'platform' => $this->input->post('platform', TRUE),
                'portfolio_name' => $this->input->post('portfolio_name', TRUE),
                'quantity_current' => $quantity,
                'unit' => $unit,
                'avg_price' => $avg_price,
                'market_price' => $market_price,
                'invested_amount' => $invested_amount,
                'target_buy_price' => $this->input->post('target_buy_price') ?: null,
                'lot_size' => $this->input->post('lot_size') ?: 1,
                'min_purchase_amount' => $min_purchase_amount,
                'target_allocation' => $this->input->post('target_allocation') ?: 0,
                'is_planned' => $is_planned,
                'price_alert_enabled' => $price_alert_enabled,
                'price_alert_target' => $price_alert_target,
                'price_alert_direction' => $price_alert_direction,
                'notes' => $this->input->post('notes', TRUE)
            );

            if ($asset) {
                $old_alert_enabled = isset($asset->price_alert_enabled) ? (int) $asset->price_alert_enabled : 0;
                $old_alert_target = isset($asset->price_alert_target) ? (float) $asset->price_alert_target : 0;
                $old_alert_direction = isset($asset->price_alert_direction) ? $asset->price_alert_direction : 'below';
                $target_changed = (float) ($price_alert_target ?: 0) !== $old_alert_target;

                if ($old_alert_enabled !== $price_alert_enabled || $target_changed || $old_alert_direction !== $price_alert_direction) {
                    $payload['price_alert_triggered_at'] = null;
                    $payload['price_alert_last_price'] = null;
                }
            } else {
                $payload['price_alert_triggered_at'] = null;
                $payload['price_alert_last_price'] = null;
            }

            if ($id) {
                $this->Asset_model->update($id, $payload);
                $this->session->set_flashdata('success', 'Aset berhasil diperbarui.');
            } else {
                $this->Asset_model->insert($payload);
                $this->session->set_flashdata('success', 'Aset berhasil ditambahkan.');
            }

            redirect('aset');
        }

        $data = array(
            'title' => $id ? 'Edit Aset' : 'Tambah Aset',
            'asset' => $asset,
            'types' => $this->Asset_model->types(),
            'units' => $this->Asset_model->units(),
            'content' => 'assets/form'
        );

        $this->load->view('layouts/main', $data);
    }

    public function delete($id)
    {
        $this->Asset_model->delete($id);
        $this->session->set_flashdata('success', 'Aset berhasil dihapus.');
        redirect('aset');
    }

    public function toggle_plan($id)
    {
        $asset = $this->Asset_model->find($id);
        if (!$asset) {
            show_404();
        }

        $this->Asset_model->set_planned($id, !(int) $asset->is_planned);
        $this->session->set_flashdata('success', 'Flag planner aset berhasil diperbarui.');
        redirect('aset');
    }
}
