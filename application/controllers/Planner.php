<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Planner extends MY_Controller
{
    public function index()
    {
        $data = array(
            'title' => 'Investment Planner',
            'assets' => $this->Asset_model->portfolio(),
            'recommendation' => null,
            'budget' => null,
            'content' => 'planner/index'
        );
        $this->load->view('layouts/main', $data);
    }

    public function calculate()
    {
        $budget = (float) $this->clean_number($this->input->post('budget'));
        $data = array(
            'title' => 'Investment Planner',
            'assets' => $this->Asset_model->portfolio(),
            'recommendation' => $this->Recommendation_model->calculate($budget),
            'budget' => $budget,
            'content' => 'planner/index'
        );
        $this->load->view('layouts/main', $data);
    }
}
