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
            $this->Transaction_model->insert(array(
                'asset_id' => $this->input->post('asset_id'),
                'transaction_date' => $this->input->post('transaction_date', TRUE),
                'transaction_type' => $this->input->post('transaction_type', TRUE),
                'quantity' => $this->input->post('quantity'),
                'price' => $this->input->post('price'),
                'fee' => $this->input->post('fee') ?: 0,
                'notes' => $this->input->post('notes', TRUE)
            ));

            $this->session->set_flashdata('success', 'Transaksi berhasil dicatat.');
            redirect('transactions');
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
