<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
body { font-family: DejaVu Sans; font-size: 12px; }
h2 { margin-bottom: 10px; }
table { width:100%; border-collapse: collapse; }
th, td { border:1px solid #333; padding:6px; }
.header { margin-bottom: 12px; }
</style>
</head>
<body>

<h2>Reporte de Ventas</h2>

<div class="header">
<p><strong>Desde:</strong> {{ $start }}</p>
<p><strong>Hasta:</strong> {{ $end }}</p>
</div>

<table>
<thead>
<tr>
<th>Fecha</th>
<th>Tickets</th>
<th>Total</th>
</tr>
</thead>
<tbody>
@foreach($sales as $s)
<tr>
<td>{{ $s->sale_date }}</td>
<td>{{ $s->tickets_count }}</td>
<td>${{ number_format($s->total_amount,2) }}</td>
</tr>
@endforeach
</tbody>
</table>

</body>
</html>
