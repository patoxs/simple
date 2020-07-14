<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Informe 1</title>
    <style type="text/css">
        @page {
            margin: 0px;
        }

        html {
            margin: 10px 40px;
        }

        body {
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            font-size: 13px;
        }

        table {
            border-collapse: collapse;
        }

        table, th, td {
        }

        .table {
            width: 100%;
            max-width: 100%;
            margin-bottom: 1rem;
            background-color: transparent;
        }

        table {
            border-collapse: collapse;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, 0.05);
        }

        .table th, .table td {
            padding: 0.75rem;
            vertical-align: top;
            border-top: 1px solid #dee2e6;
        }

        @page {
            margin: 7%;
            margin-header: 5mm;
            margin-footer: 5mm;
            header: myHeader;
            footer: myFooter;
        }

        .dl-horizontal dt {
            float: left;
            width: 160px;
            clear: left;
            text-align: right;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .dl-horizontal dd {
            margin-left: 180px;
        }

        dl {
            margin-bottom: 18px;
        }

        dt {
            font-weight: bold;
            display: block;

        }

        dt, dd {
            line-height: 18px;
        }

        dd {
            margin-left: 9px;
        }

    </style>
</head>
<body>
@php
    $col_size = count($reporte[0]);
    $row_size = count($reporte);
@endphp

<htmlpageheader name="myHeader">
    <H4>{{$title}}</H4>
    <p>Consultado por: {{Auth::user()->nombre .' '.  Auth::user()->apellidos}}, el {{date('d/m/Y')}}</p>
</htmlpageheader>

<pagefooter content-left="{DATE d/m/Y}" content-center="" content-right="{PAGENO}/{nbpg}" name="myFooter"/>
<div class="row-fluid">
    <div class="span12">
        <dl class="dl-horizontal">
            <dt>Duración promedio</dt>
            <dd> <?=$promedio_tramite ? abs($promedio_tramite) . ' días' : 'No hay tramites finalizados'?></dd>
            <dt>Cantidad de trámites</dt>
            <dd> <?=$tramites_completos + $tramites_pendientes?></dd>
            <dt>Completos</dt>
            <dd> <?=$tramites_completos?></dd>
            <dt>En curso</dt>
            <dd><?=$tramites_pendientes?></dd>
            <dt>En curso vencidos</dt>
            <dd> <?=$tramites_vencidos?></dd>
        </dl>
    </div>
</div>
<table class="table">
    <thead>

    <tr>
        <?php for($col = 0;$col < $col_size;$col++):?>
        <th><?php echo $reporte[0][$col];?></th>
        <?php endfor;?>
    </tr>

    </thead>

    <?php for($row = 1;$row < $row_size;$row++):?>
    <tr>
        <?php for($col = 0;$col < $col_size;$col++):?>
        <td><?php echo $reporte[$row][$col];?></td>
        <?php endfor;?>
    </tr>
    <?php endfor;?>
</table>

</body>
</html>