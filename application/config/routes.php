<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'dashboard';
$route['aset'] = 'assets';
$route['aset/create'] = 'assets/create';
$route['aset/edit/(:num)'] = 'assets/edit/$1';
$route['aset/delete/(:num)'] = 'assets/delete/$1';
$route['aset/toggle-plan/(:num)'] = 'assets/toggle_plan/$1';
$route['onboarding'] = 'onboarding';
$route['onboarding/upload'] = 'onboarding/upload';
$route['onboarding/review/(:num)'] = 'onboarding/review/$1';
$route['onboarding/reprocess/(:num)'] = 'onboarding/reprocess/$1';
$route['onboarding/save/(:num)'] = 'onboarding/save/$1';
$route['planner'] = 'planner';
$route['planner/calculate'] = 'planner/calculate';
$route['market'] = 'gold_prices';
$route['market/sync-all'] = 'gold_prices/sync_all';
$route['market/sync-tring'] = 'gold_prices/sync_tring';
$route['market/sync-stocks'] = 'gold_prices/sync_stocks';
$route['settings/ai'] = 'settings/ai';
$route['settings/ai/save'] = 'settings/save_ai';
$route['login'] = 'auth/login';
$route['register'] = 'auth/register';
$route['logout'] = 'auth/logout';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;
