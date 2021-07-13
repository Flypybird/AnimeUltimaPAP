<?php  $this->load->view('comuns/header'); ?>
<?php  $this->load->view('comuns/menu'); ?>
<body>

<div class="container-fluid">
    <?php if($perms == 4 || $perms == 5): ?>
    <div class="row">
        <div class="col">
            <a class="logButton text-center" href="<?php echo base_url('logs/commentLogs'); ?>">Gerir Logs dos Comentários (Séries)</a>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <a class="logButton text-center" href="<?php echo base_url('logs/commentCLogs'); ?>">Gerir Logs dos Comentários (Hub)</a>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <a class="logButton text-center" href="<?php echo base_url('logs/compostLogs'); ?>">Gerir Logs dos Posts da Comunidade</a>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <a class="logButton text-center" href="<?php echo base_url('logs/logMod'); ?>">Gerir Logs Gerais</a>
        </div>
    </div>
    <?php endif; ?>
    <div class="row">
        <div class="col">
            <a class="logButton text-center" href="<?php echo base_url('RequestAnime/animesRequested'); ?>">Gerir Séries Solicitadas</a>
        </div>
    </div>
</div>


<?php $this->load->view('comuns/footer'); ?>
