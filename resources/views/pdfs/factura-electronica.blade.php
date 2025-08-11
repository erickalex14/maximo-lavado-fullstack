<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura #{{ str_pad($factura->secuencial, 9, '0', STR_PAD_LEFT) }}</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; font-size: 11px; margin:0; padding:12px; color:#000; }
        * { box-sizing: border-box; }
        .grid { width:100%; border-collapse: collapse; }
        .grid td, .grid th { border:1px solid #000; padding:4px 6px; vertical-align: top; }
        .sin-borde td, .sin-borde th { border: none !important; }
        .titulo { font-size:14px; font-weight:bold; text-align:center; }
        .seccion-titulo { background:#f0f0f0; font-weight:bold; text-transform:uppercase; font-size:10px; }
        .encabezado { width:100%; margin-bottom:8px; }
        .w-33 { width:33.33%; }
        .w-50 { width:50%; }
        .text-center { text-align:center; }
        .text-right { text-align:right; }
        .text-left { text-align:left; }
        .small { font-size:9px; }
        .bold { font-weight:bold; }
        .clave-acceso { font-family: 'Courier New', monospace; font-size:10px; word-break: break-all; }
        .estado { font-size:10px; font-weight:bold; }
        .totales { width:40%; margin-left:auto; margin-top:10px; border-collapse: collapse; }
        .totales td { padding:4px 6px; border:1px solid #000; }
        .watermark { position:fixed; top:45%; left:50%; transform:translate(-50%, -50%) rotate(-30deg); font-size:70px; color:rgba(0,0,0,0.04); font-weight:bold; pointer-events:none; }
        .observaciones { margin-top:12px; font-size:9px; }
        .separador { height:4px; }
        .tabla-detalle th { background:#f0f0f0; }
        .tabla-detalle td { font-size:10px; }
        .firma { margin-top:30px; font-size:9px; }
        .barcode { text-align:center; font-size:9px; margin-top:4px; }
    </style>
    </style>
</head>
<body>
    @php
        $logoPath = public_path('images/maximo-lavado-logo.png');
        $logoB64 = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;
    @endphp
    <div class="watermark" style="opacity:0.05;">
        @if($logoB64)
            <img src="data:image/png;base64,{{ $logoB64 }}" alt="Logo" style="width:500px;" />
        @else
            MÁXIMO LAVADO
        @endif
    </div>

    <!-- ENCABEZADO PRINCIPAL: EMISOR / FACTURA / AUTORIZACIÓN -->
    <table class="grid sin-borde" style="margin-bottom:6px;">
        <tr>
            <td class="w-33" style="border:none;">
                @if($logoB64)
                    <img src="data:image/png;base64,{{ $logoB64 }}" alt="Logo" style="height:60px; margin-bottom:4px;" />
                @endif
                <div class="bold" style="font-size:13px;">{{ strtoupper($factura->razon_social_emisor ?? 'MÁXIMO LAVADO') }}</div>
                <div style="font-size:10px; margin-top:4px;">
                    RUC: {{ $factura->ruc_emisor }}<br>
                    Dirección Matriz: {{ $factura->direccion_emisor }}<br>
                    Email: contacto@maximolavado.com<br>
                    Tel: +593 99 XXX XXXX
                </div>
            </td>
            <td class="w-33" style="border:none;">
                <div class="titulo">FACTURA</div>
                <div class="text-center" style="margin-top:4px; font-size:11px;">
                    No. {{ str_pad($factura->establecimiento,3,'0',STR_PAD_LEFT) }}-{{ str_pad($factura->punto_emision,3,'0',STR_PAD_LEFT) }}-{{ str_pad($factura->secuencial,9,'0',STR_PAD_LEFT) }}
                </div>
                <div class="text-center small" style="margin-top:3px;">
                    Fecha Emisión: {{ is_string($factura->venta->fecha) ? \Carbon\Carbon::parse($factura->venta->fecha)->format('d/m/Y') : $factura->venta->fecha->format('d/m/Y') }}
                </div>
                <div class="text-center small estado" style="margin-top:4px;">Estado SRI: {{ $factura->estado_sri }}</div>
            </td>
            <td class="w-33" style="border:none;">
                <div class="seccion-titulo" style="padding:4px;">AUTORIZACIÓN</div>
                <div style="font-size:9px;">
                    @if($factura->estado_sri === 'AUTORIZADA')
                        Nº Autorización:<br>
                        <span class="bold">{{ $factura->numero_autorizacion }}</span><br>
                        Fecha/Hora: {{ is_string($factura->fecha_autorizacion) ? \Carbon\Carbon::parse($factura->fecha_autorizacion)->format('d/m/Y H:i:s') : optional($factura->fecha_autorizacion)->format('d/m/Y H:i:s') }}
                    @else
                        Documento en estado: {{ $factura->estado_sri }}
                    @endif
                </div>
                <div class="small" style="margin-top:4px;">Ambiente: {{ $factura->ambiente == '2' ? 'PRODUCCIÓN' : 'PRUEBAS' }}</div>
                <div class="small">Emisión: {{ $factura->tipo_emision == '2' ? 'CONTINGENCIA' : 'NORMAL' }}</div>
            </td>
        </tr>
    </table>

    <!-- CLAVE DE ACCESO -->
    <table class="grid" style="margin-bottom:6px;">
        <tr>
            <td class="seccion-titulo" style="width:18%;">CLAVE DE ACCESO</td>
            <td class="clave-acceso">{{ $factura->clave_acceso }}</td>
        </tr>
    </table>

    <!-- DATOS DEL COMPRADOR -->
    <table class="grid" style="margin-bottom:8px;">
        <tr><td colspan="4" class="seccion-titulo">INFORMACIÓN DEL COMPRADOR</td></tr>
        <tr>
            <td style="width:30%;" class="bold">Razón Social / Nombres:</td>
            <td style="width:70%;">{{ $factura->razon_social_comprador }}</td>
        </tr>
        <tr>
            <td class="bold">Identificación:</td>
            <td>{{ $factura->identificacion_comprador }}</td>
        </tr>
        <tr>
            <td class="bold">Dirección:</td>
            <td>{{ $factura->direccion_comprador ?? '---' }}</td>
        </tr>
        @if($factura->email_comprador)
        <tr>
            <td class="bold">Email:</td>
            <td>{{ $factura->email_comprador }}</td>
        </tr>
        @endif
    </table>

    <!-- DETALLE DE LA FACTURA -->
    <table class="grid tabla-detalle" style="margin-bottom:10px;">
        <thead>
        <tr>
            <th style="width:10%;" class="text-center">Cant.</th>
            <th style="width:50%;">Descripción</th>
            <th style="width:15%;" class="text-right">P.Unit</th>
            <th style="width:15%;" class="text-right">Subtotal</th>
            <th style="width:10%;" class="text-right">IVA</th>
        </tr>
        </thead>
        <tbody>
        @php $hayDetalles = $factura->venta && $factura->venta->detalles && $factura->venta->detalles->count() > 0; @endphp
        @if($hayDetalles)
            @foreach($factura->venta->detalles as $detalle)
                @php
                    // Fallback de descripción usando snapshot o relaciones
                    $descripcion = $detalle->descripcion
                        ?? $detalle->item_descripcion
                        ?? $detalle->item_nombre
                        ?? ($detalle->servicio->nombre ?? null)
                        ?? ($detalle->productoAutomotriz->nombre ?? null)
                        ?? ($detalle->productoDespensa->nombre ?? null)
                        ?? 'Item';
                    $pu = (float)$detalle->precio_unitario;
                    $cant = (float)$detalle->cantidad;
                    $sub = $cant * $pu;
                    $ivaLinea = round($sub * 0.15, 2); // IVA 15%
                @endphp
                <tr>
                    <td class="text-center">{{ number_format($cant, 2) }}</td>
                    <td>{{ $descripcion }}</td>
                    <td class="text-right">{{ number_format($pu, 2) }}</td>
                    <td class="text-right">{{ number_format($sub, 2) }}</td>
                    <td class="text-right">{{ number_format($ivaLinea, 2) }}</td>
                </tr>
            @endforeach
        @else
            <tr>
                <td colspan="5" class="text-center" style="font-style:italic;">Sin detalles registrados</td>
            </tr>
        @endif
        </tbody>
    </table>

    <!-- TOTALES -->
    <table class="totales">
        <tr><td class="bold" style="width:60%;">Subtotal</td><td class="text-right">{{ number_format($factura->subtotal, 2) }}</td></tr>
        @if($factura->descuento > 0)
            <tr><td class="bold">Descuento</td><td class="text-right">-{{ number_format($factura->descuento, 2) }}</td></tr>
        @endif
    <tr><td class="bold">IVA 15%</td><td class="text-right">{{ number_format($factura->iva, 2) }}</td></tr>
        <tr><td class="bold">Total</td><td class="text-right bold">{{ number_format($factura->total, 2) }}</td></tr>
    </table>

    <!-- ESTADO SRI / MENSAJES -->
    <div class="observaciones">
        @if($factura->estado_sri === 'AUTORIZADA')
            <div class="bold" style="margin-bottom:4px;">Documento AUTORIZADO por el SRI</div>
            <div>Mensaje: {{ $factura->mensaje_sri ?? 'Autorizado correctamente.' }}</div>
        @elseif($factura->estado_sri === 'RECHAZADA')
            <div class="bold" style="margin-bottom:4px;">Documento RECHAZADO por el SRI</div>
            <div>Motivo: {{ $factura->mensaje_sri }}</div>
            @if($factura->errores_sri)
                <ul style="margin:4px 0; padding-left:15px;">
                    @foreach($factura->errores_sri as $error)
                        <li>{{ $error['mensaje'] ?? $error }}</li>
                    @endforeach
                </ul>
            @endif
        @else
            <div class="bold" style="margin-bottom:4px;">Documento en proceso: {{ $factura->estado_sri }}</div>
            <div>El comprobante se encuentra en trámite ante el SRI.</div>
        @endif
    </div>

    <div class="firma">
        <div class="small">Este documento es una representación impresa de la Factura Electrónica.</div>
        <div class="small">Generado el {{ now()->format('d/m/Y H:i:s') }} - Ambiente: {{ $factura->ambiente == '2' ? 'Producción' : 'Pruebas' }}</div>
    </div>
</body>
</html>
