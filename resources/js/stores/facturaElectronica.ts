import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import facturaElectronicaService from '@/services/factura-electronica.service';
import type { 
  FacturaElectronica, 
  PaginatedResponse,
  FacturaElectronicaFilters,
  CreateFacturaElectronicaRequest,
  UpdateFacturaElectronicaRequest,
  ApiResponse
} from '@/types';

export const useFacturaElectronicaStore = defineStore('facturaElectronica', () => {
  // === STATE ===
  
  // Facturas Electrónicas
  const facturasElectronicas = ref<FacturaElectronica[]>([]);
  const facturaActual = ref<FacturaElectronica | null>(null);
  
  // Paginación
  const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 10,
    total: 0
  });
  
  // Estados de carga
  const loading = ref(false);
  const processingAutorizacion = ref(false);
  const generatingXML = ref(false);
  
  // Filtros
  const filtros = ref<FacturaElectronicaFilters>({
    page: 1,
    per_page: 10,
    search: '',
    estado: undefined,
    venta_id: undefined,
    cliente_identificacion: '',
    numero_factura: '',
    fecha_emision_inicio: '',
    fecha_emision_fin: ''
  });
  
  // === COMPUTED ===
  
  const facturasCount = computed(() => facturasElectronicas.value.length);
  const hasFacturas = computed(() => facturasCount.value > 0);
  
  const facturasBorrador = computed(() => 
    facturasElectronicas.value.filter(f => f.estado === 'borrador')
  );
  
  const facturasAutorizadas = computed(() => 
    facturasElectronicas.value.filter(f => f.estado === 'autorizada')
  );
  
  const facturasConError = computed(() => 
    facturasElectronicas.value.filter(f => f.estado === 'error')
  );
  
  const facturasAnuladas = computed(() => 
    facturasElectronicas.value.filter(f => f.estado === 'anulada')
  );
  
  // === ACTIONS ===
  
  // Obtener facturas electrónicas con filtros
  async function fetchFacturasElectronicas(filters: FacturaElectronicaFilters = {}) {
    if (loading.value) return;
    
    try {
      loading.value = true;
      const mergedFilters = { ...filtros.value, ...filters } as FacturaElectronicaFilters;
      console.debug('[FE Store] fetchFacturasElectronicas filtros ->', mergedFilters);
      const response = await facturaElectronicaService.getFacturasElectronicas(mergedFilters);
      if (!response) {
        console.warn('[FE Store] Respuesta vacía de getFacturasElectronicas');
        facturasElectronicas.value = [];
      } else {
        // Validar estructura
        const raw = response as any;
        if (!Array.isArray(raw.data)) {
          console.error('[FE Store] Estructura inesperada', raw);
          facturasElectronicas.value = [];
        } else {
          facturasElectronicas.value = raw.data.map((r:any)=> mapBackendFactura(r)) as FacturaElectronica[];
        }
        pagination.value = {
          current_page: raw.current_page ?? 1,
          last_page: raw.last_page ?? 1,
          per_page: (raw.per_page ?? (raw.data?.length || 10)),
          total: (raw.total ?? (raw.data?.length || 0))
        };
      }
      console.debug('[FE Store] facturas cargadas ->', facturasElectronicas.value.length);
      Object.assign(filtros.value, mergedFilters);
    } catch (error) {
      console.error('Error al obtener facturas electrónicas:', error);
      facturasElectronicas.value = [];
    } finally {
      loading.value = false;
    }
  }
  
  // Obtener factura electrónica por ID
  async function fetchFacturaElectronicaById(id: number) {
    try {
      loading.value = true;
      const resp = await facturaElectronicaService.getFacturaElectronicaById(id);
  facturaActual.value = resp?.data ? mapBackendFactura(resp.data) : null;
      return facturaActual.value;
    } catch (error) {
      console.error('Error al obtener factura electrónica:', error);
      facturaActual.value = null;
      return null;
    } finally {
      loading.value = false;
    }
  }
  
  // Crear nueva factura electrónica
  async function createFacturaElectronica(data: CreateFacturaElectronicaRequest) {
    try {
      loading.value = true;
      const resp = await facturaElectronicaService.createFacturaElectronica(data);
  const nueva = resp?.data ? mapBackendFactura(resp.data) : null;
      if (nueva) {
        facturasElectronicas.value.unshift(nueva);
      }
      return nueva ?? null;
    } catch (error) {
      console.error('Error al crear factura electrónica:', error);
      throw error;
    } finally {
      loading.value = false;
    }
  }
  
  // Actualizar factura electrónica
  async function updateFacturaElectronica(id: number, data: UpdateFacturaElectronicaRequest) {
    try {
      loading.value = true;
      const resp = await facturaElectronicaService.updateFacturaElectronica(id, data);
  const facturaActualizada = resp?.data ? mapBackendFactura(resp.data) : null;
      
      if (facturaActualizada) {
        const index = facturasElectronicas.value.findIndex(f => f.id === id);
        if (index !== -1) {
          facturasElectronicas.value[index] = facturaActualizada;
        }
      
        if (facturaActual.value?.id === id) {
          facturaActual.value = facturaActualizada;
        }
      }
      
      return facturaActualizada ?? null;
    } catch (error) {
      console.error('Error al actualizar factura electrónica:', error);
      throw error;
    } finally {
      loading.value = false;
    }
  }
  
  // Eliminar factura electrónica
  async function deleteFacturaElectronica(id: number) {
    try {
      loading.value = true;
      // En facturación electrónica no se elimina, se anula
      await facturaElectronicaService.anular(id);
      
      // Remover de la lista
      facturasElectronicas.value = facturasElectronicas.value.filter(f => f.id !== id);
      
      // Limpiar si era la factura actual
      if (facturaActual.value?.id === id) {
        facturaActual.value = null;
      }
    } catch (error) {
      console.error('Error al eliminar factura electrónica:', error);
      throw error;
    } finally {
      loading.value = false;
    }
  }
  
  // Autorizar factura en el SRI
  async function autorizarFactura(id: number) {
    try {
      processingAutorizacion.value = true;
      const resp = await facturaElectronicaService.procesarConSRI(id);
  const facturaAutorizada = resp?.data ? mapBackendFactura(resp.data) : null;
      
      if (facturaAutorizada) {
        // Actualizar en la lista
        const index = facturasElectronicas.value.findIndex(f => f.id === id);
        if (index !== -1) {
          facturasElectronicas.value[index] = facturaAutorizada;
        }
      
        // Actualizar si es la factura actual
        if (facturaActual.value?.id === id) {
          facturaActual.value = facturaAutorizada;
        }
      }
      
      return facturaAutorizada ?? null;
    } catch (error) {
      console.error('Error al autorizar factura:', error);
      throw error;
    } finally {
      processingAutorizacion.value = false;
    }
  }
  
  // Anular factura
  async function anularFactura(id: number) {
    try {
      loading.value = true;
      const resp = await facturaElectronicaService.anular(id);
  const facturaAnulada = resp?.data ? mapBackendFactura(resp.data) : null;
      
      if (facturaAnulada) {
        // Actualizar en la lista
        const index = facturasElectronicas.value.findIndex(f => f.id === id);
        if (index !== -1) {
          facturasElectronicas.value[index] = facturaAnulada;
        }
      
        // Actualizar si es la factura actual
        if (facturaActual.value?.id === id) {
          facturaActual.value = facturaAnulada;
        }
      }
      
      return facturaAnulada ?? null;
    } catch (error) {
      console.error('Error al anular factura:', error);
      throw error;
    } finally {
      loading.value = false;
    }
  }
  
  // Generar XML firmado
  async function generarXML(id: number) {
    try {
      generatingXML.value = true;
      // El servicio expone getXML (string). Guardamos el XML en el estado actual.
      const resp = await facturaElectronicaService.getXML(id);
      const xml = resp?.data ?? null;
      if (facturaActual.value?.id === id && xml) {
        facturaActual.value.xml_firmado = xml;
      }
      return xml;
    } catch (error) {
      console.error('Error al generar XML:', error);
      throw error;
    } finally {
      generatingXML.value = false;
    }
  }
  
  // Descargar PDF de factura
  async function descargarPDF(id: number) {
    try {
      loading.value = true;
      await facturaElectronicaService.downloadPDF(id, `factura_${id}.pdf`);
    } catch (error) {
      console.error('Error al descargar PDF:', error);
      throw error;
    } finally {
      loading.value = false;
    }
  }
  
  // Descargar XML de factura
  async function descargarXML(id: number) {
    try {
      loading.value = true;
      const resp = await facturaElectronicaService.getXML(id);
      const xml = resp?.data ?? '';
      const blob = new Blob([xml], { type: 'application/xml;charset=utf-8' });
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = `factura_${id}.xml`;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      window.URL.revokeObjectURL(url);
    } catch (error) {
      console.error('Error al descargar XML:', error);
      throw error;
    } finally {
      loading.value = false;
    }
  }
  
  // Verificar estado en SRI
  async function verificarEstadoSRI(id: number) {
    try {
      loading.value = true;
      const resp = await facturaElectronicaService.getFacturaElectronicaById(id);
  const fe = resp?.data ? mapBackendFactura(resp.data) : null;

      if (fe) {
        // Actualizar en la lista
        const index = facturasElectronicas.value.findIndex(f => f.id === id);
        if (index !== -1) {
          facturasElectronicas.value[index] = fe;
        }
        if (facturaActual.value?.id === id) {
          facturaActual.value = fe;
        }
      }

      return fe ?? null;
    } catch (error) {
      console.error('Error al verificar estado SRI:', error);
      throw error;
    } finally {
      loading.value = false;
    }
  }
  
  // Cambiar página
  async function changePage(page: number) {
    if (page === filtros.value.page) return;
    
    filtros.value.page = page;
    await fetchFacturasElectronicas();
  }
  
  // Cambiar elementos por página
  async function changePerPage(perPage: number) {
    filtros.value.per_page = perPage;
    filtros.value.page = 1;
    await fetchFacturasElectronicas();
  }
  
  // Aplicar filtros
  async function applyFilters(newFilters: Partial<FacturaElectronicaFilters>) {
    Object.assign(filtros.value, newFilters);
    filtros.value.page = 1;
    await fetchFacturasElectronicas();
  }
  
  // Limpiar filtros
  function clearFilters() {
    filtros.value = {
      page: 1,
      per_page: 10,
      search: '',
      estado: undefined,
      venta_id: undefined,
      cliente_identificacion: '',
      numero_factura: '',
      fecha_emision_inicio: '',
      fecha_emision_fin: ''
    };
  }
  
  // Limpiar estado
  function clearState() {
    facturasElectronicas.value = [];
    facturaActual.value = null;
    clearFilters();
  }
  
  // Refrescar datos
  async function refreshAll() {
    console.debug('[FE Store] refreshAll');
    await fetchFacturasElectronicas();
  }
  
  // Forzar recarga ignorando bandera loading
  async function debugLoad() {
    loading.value = false;
    await fetchFacturasElectronicas({});
  }
  
  return {
    // State
    facturasElectronicas,
    facturaActual,
    pagination,
    loading,
    processingAutorizacion,
    generatingXML,
    filtros,
    
    // Computed
    facturasCount,
    hasFacturas,
    facturasBorrador,
    facturasAutorizadas,
    facturasConError,
    facturasAnuladas,
    
    // Actions
    fetchFacturasElectronicas,
    fetchFacturaElectronicaById,
    createFacturaElectronica,
    updateFacturaElectronica,
    deleteFacturaElectronica,
    autorizarFactura,
    anularFactura,
    generarXML,
    descargarPDF,
    descargarXML,
    verificarEstadoSRI,
    changePage,
    changePerPage,
    applyFilters,
    clearFilters,
    clearState,
    refreshAll,
    debugLoad
  };
});

// === Helpers de mapeo ===
function mapBackendFactura(r:any): FacturaElectronica {
  return {
    id: r.factura_electronica_id ?? r.id ?? 0,
    venta_id: r.venta_id,
    numero_factura: buildNumero(r),
    autorizacion: r.numero_autorizacion || r.clave_acceso || null,
    fecha_emision: r.created_at || r.fecha_emision || '',
    fecha_autorizacion: r.fecha_autorizacion || null,
    estado: mapEstado(r.estado_sri),
    xml_firmado: r.xml_autorizado || r.xml_documento || null,
    clave_acceso: r.clave_acceso || null,
    errores_sri: r.errores_sri || null,
    cliente_razon_social: r.razon_social_comprador || r.cliente_razon_social || '',
    cliente_identificacion: r.identificacion_comprador || r.cliente_identificacion || '',
    cliente_direccion: r.direccion_comprador || '',
    cliente_email: r.email_comprador || '',
    cliente_telefono: r.telefono_comprador || '',
    subtotal_0: 0,
    subtotal_12: r.subtotal ?? 0,
    iva: r.iva ?? 0,
    total: r.total ?? 0,
    venta: r.venta,
    created_at: r.created_at || '',
    updated_at: r.updated_at || '',
    deleted_at: r.deleted_at || null
  } as FacturaElectronica;
}

function buildNumero(r:any){
  if (r.establecimiento && r.punto_emision && r.secuencial) {
    return `${pad(r.establecimiento,3)}-${pad(r.punto_emision,3)}-${pad(r.secuencial,9)}`;
  }
  return r.numero_factura || r.secuencial || 'N/A';
}
function pad(v:any,l:number){ return String(v).padStart(l,'0'); }
function mapEstado(estadoSri:string){
  const e = (estadoSri||'').toUpperCase();
  if (e==='AUTORIZADA') return 'autorizada';
  if (e==='RECHAZADA') return 'error';
  if (e==='ANULADA') return 'anulada';
  return 'borrador';
}
