<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
body { font-family: DejaVu Sans; font-size: 12px; }
h2 { margin-bottom: 10px; }
table { width:100%; border-collapse: collapse; margin-top:10px; }
th, td { border:1px solid #333; padding:6px; text-align:left; }
.summary p { margin:4px 0; }
</style>
</head>
<body>

<h2>Corte de Caja</h2>

<div class="summary">
<p><strong>Apertura:</strong> {{ $summary->opened_at }}</p>
<p><strong>Cierre:</strong> {{ $summary->closed_at }}</p>
<p><strong>Monto inicial:</strong> ${{ number_format($summary->opening_amount,2) }}</p>
<p><strong>Total cobrado:</strong> ${{ number_format($summary->total_collected,2) }}</p>
<p><strong>Esperado:</strong> ${{ number_format($summary->expected_amount,2) }}</p>
<p><strong>Real:</strong> ${{ number_format($summary->closing_amount,2) }}</p>
<p><strong>Diferencia:</strong> ${{ number_format($summary->difference,2) }}</p>
<p><strong>Tickets:</strong> {{ $summary->tickets_count }}</p>
</div>

<h4>Pagos por método</h4>

<table>
<thead>
<tr>
<th>Método</th>
<th>Cantidad</th>
<th>Total</th>
</tr>
</thead>
<tbody>
@foreach($byPayment as $p)
<tr>
<td>{{ $p->payment_type_code }}</td>
<td>{{ $p->payments_count }}</td>
<td>${{ number_format($p->total_amount,2) }}</td>
</tr>
@endforeach
</tbody>
</table>

</body>
</html>
