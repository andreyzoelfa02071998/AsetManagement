<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Planner extends MY_Controller
{
    public function index()
    {
        $planning_assets = $this->Asset_model->planning_assets();
        $data = array(
            'title' => 'Investment Planner',
            'assets' => $this->Asset_model->portfolio(),
            'planning_assets' => $planning_assets,
            'selected_asset_ids' => array_map(function ($asset) {
                return (int) $asset->id;
            }, $planning_assets),
            'recommendation' => null,
            'budget' => null,
            'content' => 'planner/index'
        );
        $this->load->view('layouts/main', $data);
    }

    public function calculate()
    {
        $budget = (float) $this->clean_number($this->input->post('budget'));
        $selected_asset_ids = array_map('intval', (array) $this->input->post('selected_assets'));
        $planning_assets = $this->Asset_model->planning_assets();

        $data = array(
            'title' => 'Investment Planner',
            'assets' => $this->Asset_model->portfolio(),
            'planning_assets' => $planning_assets,
            'selected_asset_ids' => $selected_asset_ids,
            'recommendation' => $this->Recommendation_model->calculate($budget, $selected_asset_ids),
            'budget' => $budget,
            'content' => 'planner/index'
        );
        $this->load->view('layouts/main', $data);
    }
}
