# Configuración de directorios SRI

Este proyecto requiere una estructura específica de directorios para el manejo de facturas electrónicas del SRI Ecuador:

## Directorios creados:

### `/storage/certificates/`
- **Propósito**: Almacena el certificado digital (.p12) para firmar facturas electrónicas
- **Archivo requerido**: `certificado.p12` (obtenido del SRI)
- **Configuración**: Variable `SRI_CERTIFICADO_ARCHIVO` en `.env`

### `/storage/app/sri/xml/`
- **Propósito**: Almacena los archivos XML de facturas antes de ser firmados
- **Formato**: XML sin firmar según estándar SRI

### `/storage/app/sri/signed/`
- **Propósito**: Almacena los archivos XML firmados digitalmente
- **Formato**: XML firmado listo para envío al SRI

### `/storage/app/sri/pdf/`
- **Propósito**: Almacena las representaciones PDF de las facturas
- **Uso**: Para envío por email y archivo físico

### `/storage/app/sri/backup/`
- **Propósito**: Respaldos de facturas procesadas
- **Periodicidad**: Respaldo automático según configuración

## Configuración requerida:

1. Colocar certificado digital en `/storage/certificates/certificado.p12`
2. Configurar clave del certificado en `.env`: `SRI_CERTIFICADO_CLAVE=tu_clave`
3. Configurar RUC emisor: `SRI_RUC=1310675341001`
4. Configurar ambiente: `SRI_AMBIENTE=1` (pruebas) o `SRI_AMBIENTE=2` (producción)

## Permisos:
Asegúrate de que el servidor web tenga permisos de lectura/escritura en todos estos directorios.
