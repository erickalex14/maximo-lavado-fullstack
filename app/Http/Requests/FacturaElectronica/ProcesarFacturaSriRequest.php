<?php

namespace App\Http\Requests\FacturaElectronica;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 🏛️ ProcesarFacturaSriRequest - Request para procesar facturas en el SRI Ecuador
 * 
 * Validaciones específicas para el envío de comprobantes electrónicos al SRI
 * según normativa ecuatoriana vigente
 */
class ProcesarFacturaSriRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Configuración del ambiente SRI
            'ambiente' => 'sometimes|string|size:1|in:1,2', // 1: Pruebas, 2: Producción
            'tipo_emision' => 'sometimes|string|size:1|in:1,2', // 1: Normal, 2: Contingencia
            
            // Datos del emisor (empresa)
            'ruc_emisor' => 'sometimes|string|size:13|regex:/^[0-9]{13}$/',
            'razon_social_emisor' => 'sometimes|string|max:300',
            'nombre_comercial_emisor' => 'nullable|string|max:300',
            'direccion_emisor' => 'sometimes|string|max:300',
            
            // Establecimiento y punto de emisión
            'establecimiento' => 'sometimes|string|size:3|regex:/^[0-9]{3}$/',
            'punto_emision' => 'sometimes|string|size:3|regex:/^[0-9]{3}$/',
            
            // Datos del comprador
            'identificacion_comprador' => 'sometimes|string|max:13',
            'tipo_identificacion_comprador' => 'sometimes|string|size:2|in:04,05,06,07,08', // 04: RUC, 05: Cédula, 06: Pasaporte, etc.
            'razon_social_comprador' => 'sometimes|string|max:300',
            'direccion_comprador' => 'nullable|string|max:300',
            'telefono_comprador' => 'nullable|string|max:20',
            'email_comprador' => 'nullable|email|max:254',
            
            // Información del documento
            'tipo_documento' => 'sometimes|string|size:2|in:01,03,04,05,06,07,08', // 01: Factura, 04: Nota crédito, etc.
            'secuencial' => 'sometimes|string|size:9|regex:/^[0-9]{9}$/',
            'fecha_emision' => 'sometimes|date_format:d/m/Y',
            
            // Información tributaria
            'codigo_porcentaje_iva' => 'sometimes|string|size:1|in:0,2,3,4,5,6,7,8', // 0: 0%, 2: 12%, etc.
            'porcentaje_iva' => 'sometimes|numeric|in:0,12,14,15', // Porcentajes válidos Ecuador
            'codigo_porcentaje_ice' => 'nullable|string|size:4',
            'porcentaje_ice' => 'nullable|numeric|min:0|max:100',
            
            // Valores monetarios (centavos para precisión)
            'subtotal_sin_impuestos' => 'sometimes|numeric|min:0|max:999999999.99',
            'subtotal_0' => 'sometimes|numeric|min:0|max:999999999.99',
            'subtotal_12' => 'sometimes|numeric|min:0|max:999999999.99',
            'subtotal_no_objeto_iva' => 'sometimes|numeric|min:0|max:999999999.99',
            'subtotal_exento_iva' => 'sometimes|numeric|min:0|max:999999999.99',
            'descuento' => 'sometimes|numeric|min:0|max:999999999.99',
            'ice' => 'sometimes|numeric|min:0|max:999999999.99',
            'iva' => 'sometimes|numeric|min:0|max:999999999.99',
            'propina' => 'sometimes|numeric|min:0|max:999999999.99',
            'importe_total' => 'sometimes|numeric|min:0|max:999999999.99',
            
            // Detalles del comprobante
            'detalles' => 'sometimes|array|min:1|max:1000',
            'detalles.*.codigo_principal' => 'required_with:detalles|string|max:25',
            'detalles.*.codigo_auxiliar' => 'nullable|string|max:25',
            'detalles.*.descripcion' => 'required_with:detalles|string|max:300',
            'detalles.*.cantidad' => 'required_with:detalles|numeric|min:0|max:999999999.999999',
            'detalles.*.precio_unitario' => 'required_with:detalles|numeric|min:0|max:999999999.999999',
            'detalles.*.descuento' => 'sometimes|numeric|min:0|max:999999999.99',
            'detalles.*.precio_total_sin_impuesto' => 'required_with:detalles|numeric|min:0|max:999999999.99',
            
            // Información adicional opcional
            'informacion_adicional' => 'sometimes|array|max:15',
            'informacion_adicional.*.nombre' => 'required_with:informacion_adicional|string|max:300',
            'informacion_adicional.*.valor' => 'required_with:informacion_adicional|string|max:300',
            
            // Configuración de procesamiento
            'generar_xml' => 'sometimes|boolean',
            'generar_pdf' => 'sometimes|boolean',
            'enviar_email' => 'sometimes|boolean',
            'validar_sri_online' => 'sometimes|boolean',
            'forzar_regeneracion' => 'sometimes|boolean',
            
            // Configuración de reintento
            'reintentos_maximos' => 'sometimes|integer|min:1|max:5',
            'timeout_sri' => 'sometimes|integer|min:30|max:300', // segundos
        ];
    }

    public function messages(): array
    {
        return [
            // Ambiente y emisión
            'ambiente.in' => 'El ambiente debe ser 1 (Pruebas) o 2 (Producción).',
            'tipo_emision.in' => 'El tipo de emisión debe ser 1 (Normal) o 2 (Contingencia).',
            
            // RUC validaciones
            'ruc_emisor.size' => 'El RUC del emisor debe tener exactamente 13 dígitos.',
            'ruc_emisor.regex' => 'El RUC del emisor debe contener solo números.',
            
            // Establecimiento
            'establecimiento.size' => 'El establecimiento debe tener exactamente 3 dígitos.',
            'establecimiento.regex' => 'El establecimiento debe contener solo números.',
            'punto_emision.size' => 'El punto de emisión debe tener exactamente 3 dígitos.',
            'punto_emision.regex' => 'El punto de emisión debe contener solo números.',
            
            // Identificación comprador
            'tipo_identificacion_comprador.in' => 'Tipo de identificación inválido (04: RUC, 05: Cédula, 06: Pasaporte, 07: Consumidor Final, 08: Identificación exterior).',
            
            // Documento
            'tipo_documento.in' => 'Tipo de documento inválido (01: Factura, 04: Nota crédito, 05: Nota débito, 06: Guía remisión, 07: Comprobante retención).',
            'secuencial.size' => 'El secuencial debe tener exactamente 9 dígitos.',
            'secuencial.regex' => 'El secuencial debe contener solo números.',
            'fecha_emision.date_format' => 'La fecha de emisión debe tener formato dd/mm/yyyy.',
            
            // IVA
            'codigo_porcentaje_iva.in' => 'Código de porcentaje IVA inválido (0: 0%, 2: 12%, 3: 14%, 4: No objeto, 6: No gravado, 7: Exento).',
            'porcentaje_iva.in' => 'Porcentaje de IVA debe ser 0, 12, 14 o 15.',
            
            // Valores monetarios
            'subtotal_sin_impuestos.min' => 'El subtotal sin impuestos no puede ser negativo.',
            'importe_total.min' => 'El importe total no puede ser negativo.',
            
            // Detalles
            'detalles.min' => 'Debe incluir al menos un detalle en el comprobante.',
            'detalles.max' => 'No puede incluir más de 1000 detalles por comprobante.',
            'detalles.*.codigo_principal.required_with' => 'El código principal es obligatorio para cada detalle.',
            'detalles.*.codigo_principal.max' => 'El código principal no puede exceder 25 caracteres.',
            'detalles.*.descripcion.required_with' => 'La descripción es obligatoria para cada detalle.',
            'detalles.*.descripcion.max' => 'La descripción no puede exceder 300 caracteres.',
            'detalles.*.cantidad.required_with' => 'La cantidad es obligatoria para cada detalle.',
            'detalles.*.cantidad.min' => 'La cantidad no puede ser negativa.',
            'detalles.*.precio_unitario.required_with' => 'El precio unitario es obligatorio para cada detalle.',
            'detalles.*.precio_unitario.min' => 'El precio unitario no puede ser negativo.',
            
            // Información adicional
            'informacion_adicional.max' => 'No puede incluir más de 15 campos de información adicional.',
            
            // Configuración
            'reintentos_maximos.min' => 'Debe permitir al menos 1 reintento.',
            'reintentos_maximos.max' => 'No puede exceder 5 reintentos.',
            'timeout_sri.min' => 'El timeout mínimo es 30 segundos.',
            'timeout_sri.max' => 'El timeout máximo es 300 segundos (5 minutos).',
        ];
    }

    /**
     * Preparar datos para validación
     */
    protected function prepareForValidation(): void
    {
        // Establecer valores por defecto para ambiente de pruebas
        if (!$this->has('ambiente')) {
            $this->merge(['ambiente' => '1']); // Pruebas por defecto
        }
        
        if (!$this->has('tipo_emision')) {
            $this->merge(['tipo_emision' => '1']); // Normal por defecto
        }
        
        if (!$this->has('generar_xml')) {
            $this->merge(['generar_xml' => true]);
        }
        
        if (!$this->has('generar_pdf')) {
            $this->merge(['generar_pdf' => true]);
        }
        
        if (!$this->has('validar_sri_online')) {
            $this->merge(['validar_sri_online' => true]);
        }
        
        if (!$this->has('reintentos_maximos')) {
            $this->merge(['reintentos_maximos' => 3]);
        }
        
        if (!$this->has('timeout_sri')) {
            $this->merge(['timeout_sri' => 120]); // 2 minutos
        }
        
        // Formatear fecha si viene en formato diferente
        if ($this->has('fecha_emision') && $this->fecha_emision) {
            try {
                $fecha = \Carbon\Carbon::parse($this->fecha_emision);
                $this->merge(['fecha_emision' => $fecha->format('d/m/Y')]);
            } catch (\Exception $e) {
                // Mantener el valor original para que falle la validación
            }
        }
        
        // Limpiar y validar RUC
        if ($this->has('ruc_emisor')) {
            $ruc = preg_replace('/[^0-9]/', '', $this->ruc_emisor);
            $this->merge(['ruc_emisor' => $ruc]);
        }
        
        // Formatear establecimiento y punto de emisión
        if ($this->has('establecimiento')) {
            $this->merge(['establecimiento' => str_pad($this->establecimiento, 3, '0', STR_PAD_LEFT)]);
        }
        
        if ($this->has('punto_emision')) {
            $this->merge(['punto_emision' => str_pad($this->punto_emision, 3, '0', STR_PAD_LEFT)]);
        }
    }

    /**
     * Obtener datos validados con estructura para SRI
     */
    public function getDatosSri(): array
    {
        $validated = $this->validated();
        
        return [
            'infoTributaria' => [
                'ambiente' => $validated['ambiente'] ?? '1',
                'tipoEmision' => $validated['tipo_emision'] ?? '1',
                'razonSocial' => $validated['razon_social_emisor'] ?? config('sri.razon_social'),
                'nombreComercial' => $validated['nombre_comercial_emisor'] ?? config('sri.nombre_comercial'),
                'ruc' => $validated['ruc_emisor'] ?? config('sri.ruc'),
                'claveAcceso' => $this->generarClaveAcceso(),
                'codDoc' => $validated['tipo_documento'] ?? '01',
                'estab' => $validated['establecimiento'] ?? '001',
                'ptoEmi' => $validated['punto_emision'] ?? '001',
                'secuencial' => $validated['secuencial'] ?? $this->generarSecuencial(),
                'dirMatriz' => $validated['direccion_emisor'] ?? config('sri.direccion_matriz'),
            ],
            'infoFactura' => [
                'fechaEmision' => $validated['fecha_emision'] ?? now()->format('d/m/Y'),
                'dirEstablecimiento' => $validated['direccion_emisor'] ?? config('sri.direccion_establecimiento'),
                'tipoIdentificacionComprador' => $validated['tipo_identificacion_comprador'] ?? '05',
                'razonSocialComprador' => $validated['razon_social_comprador'] ?? 'CONSUMIDOR FINAL',
                'identificacionComprador' => $validated['identificacion_comprador'] ?? '9999999999999',
                'totalSinImpuestos' => number_format($validated['subtotal_sin_impuestos'] ?? 0, 2, '.', ''),
                'totalDescuento' => number_format($validated['descuento'] ?? 0, 2, '.', ''),
                'importeTotal' => number_format($validated['importe_total'] ?? 0, 2, '.', ''),
                'moneda' => 'DOLAR',
            ],
            'detalles' => $validated['detalles'] ?? [],
            'infoAdicional' => $validated['informacion_adicional'] ?? [],
            'configuracion' => [
                'generarXml' => $validated['generar_xml'] ?? true,
                'generarPdf' => $validated['generar_pdf'] ?? true,
                'enviarEmail' => $validated['enviar_email'] ?? false,
                'validarSriOnline' => $validated['validar_sri_online'] ?? true,
                'reintentosMaximos' => $validated['reintentos_maximos'] ?? 3,
                'timeoutSri' => $validated['timeout_sri'] ?? 120,
            ]
        ];
    }

    /**
     * Generar clave de acceso según normativa SRI
     */
    private function generarClaveAcceso(): string
    {
        // Implementación simplificada - en producción usar algoritmo oficial SRI
        $fecha = now()->format('dmY');
        $tipoComprobante = $this->input('tipo_documento', '01');
        $ruc = $this->input('ruc_emisor', config('sri.ruc'));
        $ambiente = $this->input('ambiente', '1');
        $serie = $this->input('establecimiento', '001') . $this->input('punto_emision', '001');
        $secuencial = $this->input('secuencial', str_pad(rand(1, 999999999), 9, '0', STR_PAD_LEFT));
        $codigoNumerico = str_pad(rand(1, 99999999), 8, '0', STR_PAD_LEFT);
        $tipoEmision = $this->input('tipo_emision', '1');
        
        $claveAcceso = $fecha . $tipoComprobante . $ruc . $ambiente . $serie . $secuencial . $codigoNumerico . $tipoEmision;
        
        // Calcular dígito verificador módulo 11
        $digitoVerificador = $this->calcularDigitoVerificador($claveAcceso);
        
        return $claveAcceso . $digitoVerificador;
    }

    /**
     * Calcular dígito verificador módulo 11
     */
    private function calcularDigitoVerificador(string $clave): int
    {
        $multiplicadores = [2, 3, 4, 5, 6, 7];
        $suma = 0;
        $multiplicadorIndex = 0;
        
        for ($i = strlen($clave) - 1; $i >= 0; $i--) {
            $suma += intval($clave[$i]) * $multiplicadores[$multiplicadorIndex];
            $multiplicadorIndex = ($multiplicadorIndex + 1) % 6;
        }
        
        $residuo = $suma % 11;
        return $residuo == 0 ? 0 : (11 - $residuo);
    }

    /**
     * Generar secuencial automático
     */
    private function generarSecuencial(): string
    {
        // En producción, esto debería consultar la base de datos para el siguiente secuencial
        return str_pad(rand(1, 999999999), 9, '0', STR_PAD_LEFT);
    }
}
