<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Transactions extends MY_Controller
{
    public function index()
    {
        $data = array(
            'title' => 'Transaksi',
            'transactions' => $this->Transaction_model->all(),
            'content' => 'transactions/index'
        );

        $this->load->view('layouts/main', $data);
    }

    public function create()
    {
        foreach (array('quantity', 'price', 'fee') as $field) {
            if ($this->input->post($field) !== null) {
                $_POST[$field] = $this->clean_number($this->input->post($field));
            }
        }

        $this->form_validation->set_rules('asset_id', 'Aset', 'required|integer');
        $this->form_validation->set_rules('transaction_date', 'Tanggal', 'required');
        $this->form_validation->set_rules('transaction_type', 'Tipe transaksi', 'required');
        $this->form_validation->set_rules('quantity', 'Qty', 'required|numeric');
        $this->form_validation->set_rules('price', 'Harga', 'required|numeric');

        if ($this->form_validation->run()) {
            $asset = $this->Asset_model->find($this->input->post('asset_id'));
            $quantity = $this->input->post('quantity');
            $notes = $this->input->post('notes', TRUE);

            if ($asset && $asset->type === 'saham' && $asset->unit === 'share') {
                $lot_quantity = (float) $quantity;
                $quantity = $lot_quantity * 100;
                $notes = trim(($notes ?: '') . ' Input transaksi: ' . number_format($lot_quantity, 4, ',', '.') . ' lot.');
            }

            $saved = $this->Transaction_model->insert(array(
                'asset_id' => $this->input->post('asset_id'),
                'transaction_date' => $this->input->post('transaction_date', TRUE),
                'transaction_type' => $this->input->post('transaction_type', TRUE),
                'quantity' => $quantity,
                'price' => $this->input->post('price'),
                'fee' => $this->input->post('fee') ?: 0,
                'notes' => $notes
            ));

            if ($saved) {
                $this->session->set_flashdata('success', 'Transaksi berhasil dicatat dan portofolio otomatis diperbarui.');
                redirect('transactions');
            }

            $this->session->set_flashdata('error', $this->Transaction_model->error() ?: 'Transaksi gagal dicatat.');
            redirect('transactions/create');
        }

        $data = array(
            'title' => 'Tambah Transaksi',
            'assets' => $this->Asset_model->all(),
            'content' => 'transactions/form'
        );

        $this->load->view('layouts/main', $data);
    }

    public function delete($id)
    {
        $this->Transaction_model->delete($id);
        $this->session->set_flashdata('success', 'Transaksi berhasil dihapus.');
        redirect('transactions');
    }
}
