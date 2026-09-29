<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Controller
{
    public function index()
    {
        $latest_gold = $this->Gold_price_model->latest();
        $target_price = 24000;

        $data = array(
            'title' => 'Dashboard',
            'summary' => $this->Asset_model->portfolio_summary(),
            'assets' => $this->Asset_model->all(),
            'price_alerts' => $this->Asset_model->active_price_alerts(),
            'latest_gold' => $latest_gold,
            'gold_signal' => $this->Gold_price_model->signal($latest_gold, $target_price),
            'recent_transactions' => $this->Transaction_model->recent(8),
            'target_price' => $target_price,
            'content' => 'dashboard/index'
        );

        $this->load->view('layouts/main', $data);
    }
}
