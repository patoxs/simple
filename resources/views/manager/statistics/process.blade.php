<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?=url('manager')?>">Inicio</a></li>
        <li class="breadcrumb-item"><a href="<?=url('manager/estadisticas')?>">Estadisticas</a></li>
        <li class="breadcrumb-item"><a href="<?=url('manager/estadisticas/cuentas')?>">Cuentas</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?=$title?></li>
    </ol>
</nav>

<p style="text-align: right; color: red;">*Estadisticas con respecto a los últimos 30 días.</p>

<table class="table table-striped">
    <thead>
    <tr>
        <th>Proceso</th>
        <th>Nº de Trámites</th>
      
    </tr>
    </thead>
    <tbody>
    @php
        $total_tramites = 0;
    @endphp
    <?php foreach($tramites as $t): ?>
    <tr>
        <td><?=$t->proceso_nombre?></td>
        <td><?=$t->cantidad_tramites?></td>
        @php
            $total_tramites += $t->cantidad_tramites;
        @endphp
    </tr>
    <?php endforeach; ?>

    <tr class="table-success">
        <td>Total Trámites</td>
        <td><?=$total_tramites?></td>
    </tr>
    </tbody>
</table>