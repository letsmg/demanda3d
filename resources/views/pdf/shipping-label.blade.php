<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Etiqueta de Envio — Pedido #{{ $order->id }}</title>
    <style>
        @page {
            margin: 0;
            size: a4 portrait;
        }
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            margin: 0;
            padding: 60px 80px;
            color: #1a1a1a;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 16px;
            margin-bottom: 32px;
        }
        .header h1 {
            font-size: 28px;
            margin: 0 0 4px 0;
            color: #2563eb;
        }
        .header p {
            font-size: 12px;
            color: #6b7280;
            margin: 0;
        }
        .content {
            display: flex;
            gap: 40px;
            margin-bottom: 32px;
        }
        .info {
            flex: 2;
        }
        .qrcode {
            flex: 1;
            text-align: center;
        }
        .qrcode img {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 8px;
        }
        .qrcode p {
            font-size: 10px;
            color: #9ca3af;
            margin-top: 8px;
        }
        .field {
            margin-bottom: 12px;
        }
        .field .label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
            font-weight: 600;
        }
        .field .value {
            font-size: 14px;
            font-weight: 500;
            margin-top: 2px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 24px;
        }
        th {
            background: #f3f4f6;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.5px;
            color: #6b7280;
            padding: 8px 12px;
            text-align: left;
        }
        td {
            padding: 8px 12px;
            font-size: 13px;
            border-bottom: 1px solid #f3f4f6;
        }
        .footer {
            margin-top: 48px;
            text-align: center;
            font-size: 10px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Demanda3D — Etiqueta de Envio</h1>
        <p>Gerado em {{ $generatedAt }}</p>
    </div>

    <div class="content">
        <div class="info">
            <div class="field">
                <div class="label">Pedido</div>
                <div class="value">#{{ str_pad((string) $order->id, 8, '0', STR_PAD_LEFT) }}</div>
            </div>

            <div class="field">
                <div class="label">Status</div>
                <div class="value">{{ $order->status ?? '—' }}</div>
            </div>

            <div class="field">
                <div class="label">Data do Pedido</div>
                <div class="value">{{ $order->created_at?->format('d/m/Y H:i') ?? '—' }}</div>
            </div>

            @if ($order->client)
            <div class="field">
                <div class="label">Destinatário</div>
                <div class="value">{{ $order->client->display_name ?? '—' }}</div>
            </div>
            @endif

            <div class="field">
                <div class="label">Total</div>
                <div class="value">R$ {{ number_format((float) $order->total, 2, ',', '.') }}</div>
            </div>
        </div>

        <div class="qrcode">
            <img src="{{ $qrUrl }}" alt="QR Code" width="150" height="150">
            <p>Escaneie para rastrear o pedido</p>
        </div>
    </div>

    <p class="footer">
        &copy; {{ date('Y') }} Luiz Eduardo T. Silva. Todos os direitos reservados.
    </p>
</body>
</html>