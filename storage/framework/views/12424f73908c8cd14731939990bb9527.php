<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura #<?php echo e(str_pad($factura->secuencial, 9, '0', STR_PAD_LEFT)); ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 15px;
            color: #333;
            line-height: 1.4;
        }
        
        .header {
            width: 100%;
            margin-bottom: 30px;
            border-bottom: 3px solid #1976d2;
            padding-bottom: 20px;
            overflow: hidden;
        }
        
        .header-left {
            float: left;
            width: 60%;
        }
        
        .header-right {
            float: right;
            width: 35%;
            text-align: right;
        }
        
        .empresa-logo {
            font-size: 28px;
            font-weight: bold;
            color: #1976d2;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .empresa-slogan {
            font-size: 12px;
            color: #666;
            font-style: italic;
            margin: 5px 0 15px 0;
        }
        
        .empresa-info {
            font-size: 10px;
            color: #555;
        }
        
        .empresa-info p {
            margin: 2px 0;
        }
        
        .factura-info {
            background: #1976d2;
            color: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        
        .factura-numero {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
        }
        
        .factura-fecha {
            font-size: 11px;
            margin: 5px 0 0 0;
        }
        
        .cliente-section {
            margin: 25px 0;
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #1976d2;
        }
        
        .cliente-section h3 {
            margin: 0 0 15px 0;
            color: #1976d2;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .cliente-info {
            width: 100%;
            overflow: hidden;
        }
        
        .cliente-left {
            float: left;
            width: 48%;
        }
        
        .cliente-right {
            float: right;
            width: 48%;
        }
        
        .info-row {
            margin: 8px 0;
        }
        
        .info-label {
            font-weight: bold;
            color: #2c3e50;
            display: inline-block;
            width: 100px;
            margin-right: 10px;
        }
        
        .cliente-section {
            background: #f8f9fa;
            border: 2px solid #1976d2;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }

        .cliente-section h3 {
            color: #1976d2;
            margin: 0 0 15px 0;
            font-size: 16px;
            font-weight: bold;
            border-bottom: 2px solid #1976d2;
            padding-bottom: 8px;
        }

        .cliente-info {
            display: flex;
            width: 100%;
        }

        .cliente-left, .cliente-right {
            width: 50%;
        }

        .cliente-right {
            margin-left: 20px;
        }

        .info-row {
            margin-bottom: 10px;
            line-height: 1.6;
        }
        
        .detalle-table {
            width: 100%;
            border-collapse: collapse;
            margin: 25px 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .detalle-table th {
            background: #1976d2;
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .detalle-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .detalle-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .detalle-table tr:hover {
            background-color: #e3f2fd;
        }
        
        .totales {
            float: right;
            width: 300px;
            margin: 20px 0;
            background: #ffffff;
            border: 2px solid #1976d2;
            border-radius: 8px;
            padding: 20px;
        }
        
        .totales table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .totales td {
            padding: 8px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .subtotal-row td {
            color: #2c3e50;
            font-size: 14px;
        }
        
        .total-final {
            background: #1976d2;
            color: white !important;
            border-radius: 8px;
        }
        
        .total-final td {
            padding: 12px 8px !important;
            border: none !important;
            color: white !important;
            font-size: 16px !important;
            font-weight: bold !important;
        }
        
        .sri-info {
            margin-top: 40px;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #4caf50;
        }
        
        .sri-autorizada {
            background-color: #e8f5e8;
            border-left-color: #4caf50;
        }
        
        .sri-rechazada {
            background-color: #ffebee;
            border-left-color: #f44336;
        }
        
        .sri-proceso {
            background-color: #fff8e1;
            border-left-color: #ff9800;
        }
        
        .sri-info h3 {
            margin: 0 0 15px 0;
            font-size: 14px;
        }
        
        .sri-autorizada h3 {
            color: #2e7d32;
        }
        
        .sri-rechazada h3 {
            color: #c62828;
        }
        
        .sri-proceso h3 {
            color: #ef6c00;
        }
        
        .sri-info p {
            margin: 5px 0;
            font-size: 10px;
        }
        
        .clave-acceso {
            background-color: #f5f5f5;
            padding: 8px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            word-break: break-all;
            font-size: 9px;
            margin: 10px 0;
        }
        
        .footer {
            margin-top: 40px;
            padding: 30px 20px 20px;
            background: #f8f9fa;
            border-top: 3px solid #1976d2;
            border-radius: 8px;
            position: relative;
        }

        .footer .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 60px;
            font-weight: bold;
            color: rgba(25, 118, 210, 0.1);
            z-index: 1;
            pointer-events: none;
        }

        .empresa-info {
            position: relative;
            z-index: 2;
            text-align: center;
            margin-bottom: 20px;
        }

        .empresa-info h4 {
            color: #1976d2;
            font-size: 16px;
            margin: 0 0 15px 0;
            font-weight: bold;
        }

        .contacto-grid {
            width: 100%;
            margin: 15px 0;
            text-align: left;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        .contacto-item {
            font-size: 12px;
            color: #2c3e50;
            margin: 5px 0;
            display: block;
        }

        .contacto-item span {
            font-size: 14px;
            margin-right: 8px;
        }

        .agradecimiento {
            background: #1976d2;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin: 20px auto;
            max-width: 400px;
            text-align: center;
        }

        .agradecimiento p {
            margin: 5px 0;
            font-size: 13px;
        }

        .legal-info {
            position: relative;
            z-index: 2;
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #e0e0e0;
            padding-top: 15px;
            line-height: 1.3;
        }

        .legal-info p {
            margin: 5px 0;
        }
        
        .estado-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .estado-autorizada {
            background-color: #e8f5e8;
            color: #2e7d32;
        }
        
        .estado-rechazada {
            background-color: #ffebee;
            color: #c62828;
        }
        
        .estado-borrador {
            background-color: #fff8e1;
            color: #ef6c00;
        }
        
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 120px;
            color: rgba(25, 118, 210, 0.05);
            font-weight: bold;
            z-index: -1;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <!-- Marca de agua sutil -->
    <div class="watermark">MÁXIMO LAVADO</div>

    <div class="header">
        <div class="header-left">
            <h1 class="empresa-logo">Máximo Lavado</h1>
            <p class="empresa-slogan">Servicios Automotrices de Calidad</p>
            <div class="empresa-info">
                <p><strong>RUC:</strong> <?php echo e($factura->ruc_emisor); ?></p>
                <p><strong>Dirección:</strong> <?php echo e($factura->direccion_emisor); ?></p>
                <p><strong>Teléfono:</strong> +593 99 XXX XXXX</p>
                <p><strong>Email:</strong> contacto@maximolavado.com</p>
            </div>
        </div>
        
        <div class="header-right">
            <div class="factura-info">
                <h2 class="factura-numero">FACTURA</h2>
                <p style="font-size: 14px; margin: 5px 0;">
                    <?php echo e(str_pad($factura->establecimiento, 3, '0', STR_PAD_LEFT)); ?>-<?php echo e(str_pad($factura->punto_emision, 3, '0', STR_PAD_LEFT)); ?>-<?php echo e(str_pad($factura->secuencial, 9, '0', STR_PAD_LEFT)); ?>

                </p>
                <p class="factura-fecha">
                    Fecha: <?php echo e(is_string($factura->venta->fecha) 
                        ? \Carbon\Carbon::parse($factura->venta->fecha)->format('d/m/Y') 
                        : $factura->venta->fecha->format('d/m/Y')); ?>

                </p>
                <p style="font-size: 9px; margin: 8px 0 0 0;">
                    <span class="estado-badge estado-<?php echo e(strtolower($factura->estado_sri)); ?>">
                        <?php echo e($factura->estado_sri); ?>

                    </span>
                </p>
            </div>
        </div>
        <div style="clear: both;"></div>
    </div>

    <div class="cliente-section">
        <h3>👤 Datos del Cliente</h3>
        <div class="cliente-info">
            <div class="cliente-left">
                <div class="info-row">
                    <span class="info-label">Cliente:</span>
                    <span><?php echo e($factura->razon_social_comprador); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Cédula/RUC:</span>
                    <span><?php echo e($factura->identificacion_comprador); ?></span>
                </div>
            </div>
            <div class="cliente-right">
                <?php if($factura->direccion_comprador): ?>
                    <div class="info-row">
                        <span class="info-label">Dirección:</span>
                        <span><?php echo e($factura->direccion_comprador); ?></span>
                    </div>
                <?php endif; ?>
                <?php if($factura->email_comprador): ?>
                    <div class="info-row">
                        <span class="info-label">Email:</span>
                        <span><?php echo e($factura->email_comprador); ?></span>
                    </div>
                <?php endif; ?>
            </div>
            <div style="clear: both;"></div>
        </div>
    </div>

    <table class="detalle-table">
        <thead>
            <tr>
                <th style="width: 50%;">Descripción del Servicio/Producto</th>
                <th style="width: 15%; text-align: center;">Cant.</th>
                <th style="width: 17.5%; text-align: right;">Precio Unit.</th>
                <th style="width: 17.5%; text-align: right;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php if($factura->venta->detalles && $factura->venta->detalles->count() > 0): ?>
                <?php $__currentLoopData = $factura->venta->detalles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detalle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($detalle->descripcion); ?></td>
                        <td style="text-align: center;"><?php echo e($detalle->cantidad); ?></td>
                        <td style="text-align: right;">$<?php echo e(number_format($detalle->precio_unitario, 2)); ?></td>
                        <td style="text-align: right;">$<?php echo e(number_format($detalle->cantidad * $detalle->precio_unitario, 2)); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" style="text-align: center; font-style: italic; padding: 20px; color: #666;">
                        📄 Servicios procesados - Detalles incluidos en el total
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="totales">
        <table>
            <tr class="subtotal-row">
                <td><strong>Subtotal:</strong></td>
                <td style="text-align: right;">$<?php echo e(number_format($factura->subtotal, 2)); ?></td>
            </tr>
            <?php if($factura->descuento > 0): ?>
                <tr class="subtotal-row">
                    <td><strong>Descuento:</strong></td>
                    <td style="text-align: right; color: #d32f2f;">-$<?php echo e(number_format($factura->descuento, 2)); ?></td>
                </tr>
            <?php endif; ?>
            <tr class="subtotal-row">
                <td><strong>IVA (12%):</strong></td>
                <td style="text-align: right;">$<?php echo e(number_format($factura->iva, 2)); ?></td>
            </tr>
            <tr class="total-final">
                <td><strong>TOTAL A PAGAR:</strong></td>
                <td style="text-align: right;"><strong>$<?php echo e(number_format($factura->total, 2)); ?></strong></td>
            </tr>
        </table>
    </div>

    <div style="clear: both;"></div>

    <?php if($factura->estado_sri === 'AUTORIZADA'): ?>
        <div class="sri-info">
            <h3>✅ DOCUMENTO AUTORIZADO POR EL SRI</h3>
            <p><strong>Clave de Acceso:</strong> <?php echo e($factura->clave_acceso); ?></p>
            <p><strong>Número de Autorización:</strong> <?php echo e($factura->numero_autorizacion); ?></p>
            <p><strong>Fecha y Hora de Autorización:</strong> <?php echo e(is_string($factura->fecha_autorizacion) 
                ? \Carbon\Carbon::parse($factura->fecha_autorizacion)->format('d/m/Y H:i:s') 
                : $factura->fecha_autorizacion->format('d/m/Y H:i:s')); ?></p>
            <?php if($factura->mensaje_sri): ?>
                <p><strong>Mensaje SRI:</strong> <?php echo e($factura->mensaje_sri); ?></p>
            <?php endif; ?>
        </div>
    <?php elseif($factura->estado_sri === 'RECHAZADA'): ?>
        <div class="sri-info" style="background-color: #f8d7da; border-color: #dc3545;">
            <h3>❌ DOCUMENTO RECHAZADO POR EL SRI</h3>
            <?php if($factura->mensaje_sri): ?>
                <p><strong>Motivo:</strong> <?php echo e($factura->mensaje_sri); ?></p>
            <?php endif; ?>
            <?php if($factura->errores_sri): ?>
                <p><strong>Errores:</strong></p>
                <ul>
                    <?php $__currentLoopData = $factura->errores_sri; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error['mensaje'] ?? $error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="sri-info" style="background-color: #fff3cd; border-color: #ffc107;">
            <h3>⏳ DOCUMENTO EN PROCESO</h3>
            <p>Estado actual: <strong><?php echo e($factura->estado_sri); ?></strong></p>
            <p>Este documento está siendo procesado por el SRI.</p>
        </div>
    <?php endif; ?>

    <div class="footer">
        <div class="watermark">MÁXIMO LAVADO</div>
        <div class="empresa-info">
            <h4>💧 Máximo Lavado - Servicio Premium de Autolavado</h4>
            <div class="contacto-grid">
                <div class="contacto-item">
                    <span>📍</span> Av. Principal 123, Quito - Ecuador
                </div>
                <div class="contacto-item">
                    <span>📞</span> (02) 123-4567 | 📱 099-123-4567
                </div>
                <div class="contacto-item">
                    <span>✉️</span> info@maximolavado.com
                </div>
                <div class="contacto-item">
                    <span>🌐</span> www.maximolavado.com
                </div>
            </div>
            <div class="agradecimiento">
                <p><strong>¡Gracias por confiar en nuestros servicios!</strong></p>
                <p>Esperamos verle pronto para brindarle el mejor cuidado a su vehículo.</p>
            </div>
        </div>
        <div class="legal-info">
            <p>Este documento ha sido generado electrónicamente y es válido sin firma autógrafa de conformidad con la Ley de Comercio Electrónico, Firmas Electrónicas y Mensajes de Datos.</p>
            <p><small>Generado el <?php echo e(now()->format('d/m/Y H:i:s')); ?></small></p>
        </div>
    </div>
</body>
</html>
<?php /**PATH D:\lavado-autos\backend-lavado-autos\resources\views/pdfs/factura-electronica.blade.php ENDPATH**/ ?>