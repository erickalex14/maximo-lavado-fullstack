<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuración SRI Ecuador - Facturación Electrónica
    |--------------------------------------------------------------------------
    |
    | Configuración para integración con el Servicio de Rentas Internas (SRI)
    | del Ecuador para facturación electrónica
    |
    */

    // Ambiente SRI
    'ambiente' => env('SRI_AMBIENTE', '1'), // 1: Pruebas, 2: Producción

    // URLs del SRI
    'urls' => [
        'pruebas' => [
            'recepcion' => 'https://celcer.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline?wsdl',
            'autorizacion' => 'https://celcer.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl',
        ],
        'produccion' => [
            'recepcion' => 'https://cel.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline?wsdl',
            'autorizacion' => 'https://cel.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl',
        ],
    ],

    // Datos del emisor (empresa)
    'ruc' => env('SRI_RUC', '1310675341001'), // 13 dígitos - Edison Almeida Zambrano
    'razon_social' => env('SRI_RAZON_SOCIAL', 'ALMEIDA ZAMBRANO EDISON ERNESTO'),
    'nombre_comercial' => env('SRI_NOMBRE_COMERCIAL', 'MAXIMO LAVADO CIA. LTDA.'),
    'direccion_matriz' => env('SRI_DIRECCION_MATRIZ', 'AVENIDA ELOY ALFARO, CHONE'),
    'direccion_establecimiento' => env('SRI_DIRECCION_ESTABLECIMIENTO', 'AVENIDA ELOY ALFARO, CHONE'),
    
    // Establecimiento y punto de emisión
    // Valores por defecto ajustados para pruebas según SRI: Establecimiento 002, Punto 100
    'establecimiento' => env('SRI_ESTABLECIMIENTO', '002'),
    'punto_emision' => env('SRI_PUNTO_EMISION', '101'),

    // Configuración de certificados digitales
    'certificado' => [
        'archivo' => env('SRI_CERTIFICADO_ARCHIVO', storage_path('certificates/certificado.p12')),
        'clave' => env('SRI_CERTIFICADO_CLAVE', ''),
        'validar_fecha' => env('SRI_VALIDAR_CERTIFICADO', true),
    // Opcional: usar archivos PEM si el PKCS12 falla en OpenSSL 3 (Windows)
    'pem_key' => env('SRI_CERTIFICADO_PEM_KEY', null), // ruta a private key en PEM
    'pem_cert' => env('SRI_CERTIFICADO_PEM_CERT', null), // ruta a certificado X.509 en PEM
    ],

    // Configuración de archivos
    'archivos' => [
        'xml_path' => storage_path('app/sri/xml'),
        'pdf_path' => storage_path('app/sri/pdf'),
        'signed_path' => storage_path('app/sri/signed'),
        'backup_path' => storage_path('app/sri/backup'),
    ],

    // Configuración de reintentos
    'reintentos' => [
        'maximo' => env('SRI_REINTENTOS_MAXIMO', 3),
        'intervalo' => env('SRI_REINTENTOS_INTERVALO', 60), // segundos
    ],

    // Timeouts
    'timeout' => [
        'conexion' => env('SRI_TIMEOUT_CONEXION', 30),
        'respuesta' => env('SRI_TIMEOUT_RESPUESTA', 120),
    ],

    // Configuración de IVA
    'iva' => [
        'porcentajes_validos' => [0, 12, 14, 15],
        'codigo_porcentaje' => [
            0 => '0',   // 0% IVA
            12 => '2',  // 12% IVA
            14 => '3',  // 14% IVA
            15 => '4',  // 15% IVA (futuro)
        ],
    ],

    // Tipos de documento
    'tipos_documento' => [
        '01' => 'Factura',
        '03' => 'Liquidación de compra de bienes y prestación de servicios',
        '04' => 'Nota de crédito',
        '05' => 'Nota de débito',
        '06' => 'Guía de remisión',
        '07' => 'Comprobante de retención',
        '08' => 'Boleto o entrada a espectáculos públicos',
    ],

    // Tipos de identificación
    'tipos_identificacion' => [
        '04' => 'RUC',
        '05' => 'Cédula',
        '06' => 'Pasaporte',
        '07' => 'Venta a consumidor final',
        '08' => 'Identificación del exterior',
    ],

    // Configuración de email
    'email' => [
        'enviar_automatico' => env('SRI_EMAIL_AUTO', false),
        'plantilla' => 'emails.factura_electronica',
        'asunto' => 'Factura Electrónica - {numero}',
    ],

    // Configuración de logging
    'logging' => [
        'canal' => env('SRI_LOG_CANAL', 'sri'),
        'nivel' => env('SRI_LOG_NIVEL', 'info'),
        'archivo' => storage_path('logs/sri.log'),
    ],

    // Configuración de desarrollo/pruebas
    'debug' => [
        'activado' => env('SRI_DEBUG', true), // Cambiado a true para desarrollo
        'guardar_xml_request' => env('SRI_DEBUG_XML_REQUEST', false),
        'guardar_xml_response' => env('SRI_DEBUG_XML_RESPONSE', false),
        'mock_sri_response' => env('SRI_MOCK_RESPONSE', true), // Simulación activada por defecto
    ],

    // Límites del SRI
    'limites' => [
        'max_detalles_factura' => 1000,
        'max_info_adicional' => 15,
        'max_longitud_descripcion' => 300,
        'max_longitud_razon_social' => 300,
        'max_longitud_direccion' => 300,
    ],

    // Configuración de secuenciales
    'secuenciales' => [
        'auto_incrementar' => env('SRI_AUTO_INCREMENTAR', true),
        'formato' => env('SRI_FORMATO_SECUENCIAL', '000000000'), // 9 dígitos
        'por_punto_emision' => env('SRI_SECUENCIAL_POR_PUNTO', true),
    ],

    // Configuración de firma electrónica
    'firma' => [
        'algoritmo' => 'RSA-SHA1',
        'metodo_canonicalizacion' => 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315',
        'metodo_firma' => 'http://www.w3.org/2000/09/xmldsig#rsa-sha1',
        'metodo_digest' => 'http://www.w3.org/2000/09/xmldsig#sha1',
    ],

    // Validaciones específicas
    'validaciones' => [
        'validar_ruc_comprador' => env('SRI_VALIDAR_RUC_COMPRADOR', false),
        'validar_fechas_futuras' => env('SRI_VALIDAR_FECHAS_FUTURAS', true),
        'validar_montos_negativos' => env('SRI_VALIDAR_MONTOS_NEGATIVOS', true),
        'validar_estructura_xml' => env('SRI_VALIDAR_XML', true),
    ],

    // Configuración de cache
    'cache' => [
        'activado' => env('SRI_CACHE_ACTIVADO', true),
        'ttl_autorizacion' => env('SRI_CACHE_TTL_AUTH', 3600), // 1 hora
        'ttl_consulta' => env('SRI_CACHE_TTL_QUERY', 300), // 5 minutos
    ],

    // Mensajes de error personalizados
    'mensajes' => [
        'conexion_fallida' => 'No se pudo conectar con el SRI. Inténtelo más tarde.',
        'certificado_invalido' => 'El certificado digital es inválido o ha expirado.',
        'xml_malformado' => 'El documento XML no cumple con la estructura requerida.',
        'comprobante_duplicado' => 'Ya existe un comprobante con esta clave de acceso.',
        'ambiente_incorrecto' => 'El ambiente especificado no es válido.',
    ],
];
