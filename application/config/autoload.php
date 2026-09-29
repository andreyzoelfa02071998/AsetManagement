<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$autoload['packages'] = array();
$autoload['libraries'] = array('database', 'session', 'form_validation', 'upload');
$autoload['drivers'] = array();
$autoload['helper'] = array('url', 'form', 'date');
$autoload['config'] = array();
$autoload['language'] = array();
$autoload['model'] = array('Asset_model', 'Transaction_model', 'Gold_price_model', 'Import_model', 'Recommendation_model', 'User_model', 'Ai_settings_model');
