<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?=url('manager')?>">Inicio</a></li>
        <li class="breadcrumb-item"><a href="<?=url('manager/estadisticas')?>">Estadisticas</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?=$title?></li>
    </ol>
</nav>

<p style="text-align: right; color: red;">*Estadisticas con respecto a los últimos 30 días.</p>

<table class="table table-striped">
    <thead>
    <tr>
        <th>Cuenta</th>
        <th>Nº de Trámites</th>
        <th>Nº de Procesos</th>
    </tr>
    </thead>
    <tbody>
    @php
        $total_tramites = 0;
        $total_procesos = 0;
    @endphp
    <?php foreach($cuentas as $c):?>
    <tr>
        <td><a href="<?=url('manager/estadisticas/cuentas/' . $c->id)?>"><?=$c->cuenta_nombre?></a></td>
        <td><?=$c->cantidad_tramites?></td>
        <td><?=$c->procesosActivos->count()?></td>
        @php
            $total_tramites += $c->cantidad_tramites;
            $total_procesos += $c->procesosActivos->count();
        @endphp
    </tr>
    <?php endforeach; ?>

    <tr class="table-success">
        <td>Total</td>
        <td><?=$total_tramites?></td>
        <td><?=$total_procesos?></td>
    </tr>
    </tbody>
</table>