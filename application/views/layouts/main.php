<?php
$current = $this->router->class;

function rupiah($value)
{
    return 'Rp ' . number_format((float) $value, 0, ',', '.');
}

function angka_input($value, $decimals = 0)
{
    if ($value === null || $value === '') {
        return '';
    }
    return number_format((float) $value, $decimals, ',', '.');
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo html_escape($title); ?> - Analisa Aset</title>
    <link rel="stylesheet" href="<?php echo base_url('assets/css/app.css?v=' . filemtime(FCPATH . 'assets/css/app.css')); ?>">
    <script defer src="<?php echo base_url('assets/js/app.js?v=' . filemtime(FCPATH . 'assets/js/app.js')); ?>"></script>
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar">
            <div class="brand">
                <h1>Analisa Aset</h1>
                <span>Portfolio tracker pribadi</span>
            </div>
            <nav class="nav">
                <a class="<?php echo $current === 'dashboard' ? 'active' : ''; ?>" href="<?php echo site_url('dashboard'); ?>">Dashboard</a>
                <a class="<?php echo $current === 'onboarding' ? 'active' : ''; ?>" href="<?php echo site_url('onboarding'); ?>">Onboarding</a>
                <a class="<?php echo $current === 'assets' ? 'active' : ''; ?>" href="<?php echo site_url('aset'); ?>">Master Aset</a>
                <a class="<?php echo $current === 'transactions' ? 'active' : ''; ?>" href="<?php echo site_url('transactions'); ?>">Transaksi</a>
                <a class="<?php echo $current === 'gold_prices' ? 'active' : ''; ?>" href="<?php echo site_url('market'); ?>">Market</a>
                <a class="<?php echo $current === 'planner' ? 'active' : ''; ?>" href="<?php echo site_url('planner'); ?>">Planner</a>
                <a class="<?php echo $current === 'settings' ? 'active' : ''; ?>" href="<?php echo site_url('settings/ai'); ?>">AI Settings</a>
                <?php if ($this->session->userdata('user_id')): ?>
                    <a href="<?php echo site_url('logout'); ?>">Logout</a>
                <?php else: ?>
                    <a class="<?php echo $current === 'auth' ? 'active' : ''; ?>" href="<?php echo site_url('login'); ?>">Login</a>
                <?php endif; ?>
            </nav>
        </aside>
        <main class="main">
            <div class="page-head">
                <div>
                    <div class="page-kicker">Analisa Aset</div>
                    <h2><?php echo html_escape($title); ?></h2>
                </div>
            </div>

            <?php if ($this->session->flashdata('success')): ?>
                <div class="alert success"><?php echo html_escape($this->session->flashdata('success')); ?></div>
            <?php endif; ?>
            <?php if ($this->session->flashdata('error')): ?>
                <div class="alert error"><?php echo html_escape($this->session->flashdata('error')); ?></div>
            <?php endif; ?>

            <?php if (validation_errors()): ?>
                <div class="alert error"><?php echo validation_errors(); ?></div>
            <?php endif; ?>

            <?php $this->load->view($content); ?>
        </main>
    </div>
</body>
</html>
