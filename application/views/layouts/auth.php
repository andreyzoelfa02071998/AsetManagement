<?php
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
    <link rel="stylesheet" href="<?php echo base_url('assets/css/app.css'); ?>">
    <script defer src="<?php echo base_url('assets/js/app.js'); ?>"></script>
</head>
<body>
    <main class="auth-page">
        <section class="auth-panel">
            <div class="brand auth-brand">
                <h1>Analisa Aset</h1>
                <span>Asisten keputusan investasi pribadi</span>
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
        </section>
    </main>
</body>
</html>
