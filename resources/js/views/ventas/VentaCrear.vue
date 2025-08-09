<template>
  <div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold">Nueva Venta</h1>
        <p class="text-gray-500 text-sm">Agrega productos y servicios, revisa el total y confirma.</p>
      </div>
      <div class="flex gap-2">
        <RouterLink to="/ventas" class="btn btn-secondary">Cancelar</RouterLink>
        <button class="btn btn-primary" :disabled="!items.length || saving" @click="confirmarVenta">
          <span v-if="!saving">Confirmar Venta ({{ items.length }} items)</span>
          <span v-else>Guardando...</span>
        </button>
      </div>
    </div>

    <div class="grid md:grid-cols-4 gap-4">
      <div class="md:col-span-1 space-y-4">
        <div>
          <label class="form-label">Cliente (opcional)</label>
          <select v-model="clienteId" class="form-select">
            <option value="">Cliente general...</option>
            <option v-for="c in clientes" :key="c.cliente_id" :value="c.cliente_id">{{ c.nombre }}</option>
          </select>
        </div>
        <div>
          <label class="form-label">Generar Factura?</label>
          <label class="inline-flex items-center gap-2 text-sm">
            <input type="checkbox" v-model="generarFactura" /> Factura electrónica
          </label>
        </div>
        <div class="p-4 bg-gray-50 rounded border">
          <div class="flex justify-between text-sm mb-1"><span>Subtotal</span><span>${{ formatCurrency(subtotal) }}</span></div>
          <div class="flex justify-between text-sm font-semibold"><span>Total</span><span>${{ formatCurrency(total) }}</span></div>
        </div>
      </div>
      <div class="md:col-span-3 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
          <div>
            <label class="form-label">Tipo</label>
            <select v-model="nuevo.tipo" class="form-select">
              <option value="producto_automotriz">Producto Automotriz</option>
              <option value="producto_despensa">Producto Despensa</option>
              <option value="servicio">Servicio</option>
            </select>
          </div>
          <div class="md:col-span-2">
            <label class="form-label">Referencia</label>
            <select v-model="nuevo.referencia_id" class="form-select" @change="() => syncPrecio('change')">
              <option value="">Seleccione...</option>
              <option v-for="opt in opcionesFiltradas" :key="'opt-'+opt.id" :value="opt.id">
                {{ opt.nombre }}<span v-if="opt.extra"> - {{ opt.extra }}</span><span class="text-gray-500" v-if="opt.precio"> • ${{ opt.precio }}</span>
              </option>
            </select>
          </div>
          <div>
            <label class="form-label">Cant.</label>
            <input v-model.number="nuevo.cantidad" type="number" min="1" class="form-input" />
          </div>
          <div>
            <label class="form-label">Precio</label>
            <input v-model.number="nuevo.precio_unitario" type="number" min="0" step="0.01" class="form-input" :readonly="nuevo.tipo !== 'servicio'" :class="{ 'bg-gray-100 cursor-not-allowed': nuevo.tipo !== 'servicio' }" />
          </div>
          <div class="md:col-span-5 flex justify-end">
            <button type="button" class="btn btn-primary" :disabled="!puedeAgregar" @click="agregarItem">Agregar</button>
          </div>
        </div>

        <div class="overflow-x-auto border rounded-md">
          <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-3 py-2 text-left">Tipo</th>
                <th class="px-3 py-2 text-left">Nombre</th>
                <th class="px-3 py-2 text-right">Cant.</th>
                <th class="px-3 py-2 text-right">Precio</th>
                <th class="px-3 py-2 text-right">Subtotal</th>
                <th class="px-3 py-2"></th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="items.length === 0">
                <td colspan="6" class="px-3 py-4 text-center text-gray-500">Sin items aún</td>
              </tr>
              <tr v-for="it in items" :key="it.idTemp" class="border-t">
                <td class="px-3 py-2">{{ labelTipo(it.tipo) }}</td>
                <td class="px-3 py-2">{{ it.nombre }}</td>
                <td class="px-3 py-2 text-right">
                  <div class="inline-flex items-center gap-1">
                    <button class="px-2 py-0.5 border rounded" @click="editarCantidad(it, -1)">-</button>
                    <span class="min-w-[2ch] text-center">{{ it.cantidad }}</span>
                    <button class="px-2 py-0.5 border rounded" @click="editarCantidad(it, 1)">+</button>
                  </div>
                </td>
                <td class="px-3 py-2 text-right">
                  <input type="number" min="0" step="0.01" class="w-24 border rounded px-1 py-0.5 text-right" :value="it.precio_unitario" @change="e => editarPrecio(it, Number((e.target as HTMLInputElement).value))" />
                </td>
                <td class="px-3 py-2 text-right font-medium">${{ formatCurrency(it.subtotal) }}</td>
                <td class="px-3 py-2 text-right">
                  <button class="text-red-600 hover:underline" @click="removeItem(it.idTemp)">Quitar</button>
                </td>
              </tr>
            </tbody>
            <tfoot v-if="items.length">
              <tr class="border-t bg-gray-50 font-semibold">
                <td colspan="4" class="px-3 py-2 text-right">TOTAL</td>
                <td class="px-3 py-2 text-right">${{ formatCurrency(total) }}</td>
                <td></td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>
<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useVentaStore } from '@/stores/venta';
import { useServicioStore } from '@/stores/servicio';
import type { VentaDraftItem } from '@/types';
import { useToast } from '@/composables/useToast';

const router = useRouter();
const { push: toast } = useToast();
const ventaStore = useVentaStore();
const servicioStore = useServicioStore();

const clienteId = ref<number | ''>('');
const generarFactura = ref(false);
const saving = ref(false);

const items = ref<VentaDraftItem[]>([]);
function genId() { return 'tmp-' + Date.now().toString(36) + '-' + Math.random().toString(36).slice(2,8); }

const nuevo = ref<{ tipo: VentaDraftItem['tipo']; referencia_id: number | ''; cantidad: number; precio_unitario: number }>({
  tipo: 'producto_automotriz', referencia_id: '', cantidad: 1, precio_unitario: 0
});

// Opciones
const opcionesProductosAutomotrices = computed(() => ventaStore.productosAutomotrices.map(p => ({ id: p.id, nombre: p.nombre, extra: `Stock:${p.stock ?? ''}`, precio: p.precio ?? 0, stock: p.stock ?? 0 })));
const opcionesProductosDespensa = computed(() => ventaStore.productosDespensa.map(p => ({ id: p.id, nombre: p.nombre, extra: `Stock:${p.stock ?? ''}`, precio: p.precio ?? 0, stock: p.stock ?? 0 })));
const opcionesServicios = computed(() => servicioStore.serviciosActivos.map(s => ({ id: (s as any).id ?? (s as any).servicio_id, nombre: s.nombre, extra: 'Servicio', precio: (s as any).precio_base ?? (s as any).precio ?? 0 })));

const opcionesFiltradas = computed(() => {
  switch (nuevo.value.tipo) {
    case 'producto_automotriz': return opcionesProductosAutomotrices.value;
    case 'producto_despensa': return opcionesProductosDespensa.value;
    case 'servicio': return opcionesServicios.value;
    default: return [] as any[];
  }
});
const todasOpciones = computed(() => ([...opcionesProductosAutomotrices.value, ...opcionesProductosDespensa.value, ...opcionesServicios.value]));
// Opción actualmente seleccionada (independiente del tipo) para simplificar
const selectedOpt = computed(() => {
  if (!nuevo.value.referencia_id) return null;
  const idNum = Number(nuevo.value.referencia_id);
  if (Number.isNaN(idNum)) return null;
  return todasOpciones.value.find(o => Number(o.id) === idNum) || null;
});

// Actualizar precio al seleccionar referencia
function syncPrecio(origen: string = 'watch') {
  const id = nuevo.value.referencia_id;
  if (!id) { console.debug('[syncPrecio] sin id seleccionado', { origen, tipo: nuevo.value.tipo }); return; }
  const lista = opcionesFiltradas.value || [];
  let opt: any = lista.find(o => Number(o.id) === Number(id));
  if (!opt) {
    // Buscar en todas las opciones por si el filtro aún no cambió
    opt = todasOpciones.value.find(o => Number(o.id) === Number(id));
  }
  console.debug('[syncPrecio] intento', { origen, tipo: nuevo.value.tipo, id: Number(id), listaLen: lista.length, listaTotal: todasOpciones.value.length, idsParciales: lista.slice(0,10).map(o=>o.id) });
  if (opt) {
    const nuevoPrecio = Number(opt.precio) || 0;
    nuevo.value.precio_unitario = nuevoPrecio; // siempre sincronizar al seleccionar
    console.debug('[syncPrecio] aplicado', { nuevoPrecio });
  } else {
    console.warn('[syncPrecio] opción no encontrada para id', id, lista.map(o=>o.id));
  }
}
watch(() => nuevo.value.referencia_id, () => { syncPrecio(); });
watch(opcionesFiltradas, () => { syncPrecio(); });
watch(() => nuevo.value.tipo, () => { nuevo.value.referencia_id = ''; nuevo.value.precio_unitario = 0; });

const clientes = computed(() => ventaStore.clientes);
const subtotal = computed(() => items.value.reduce((s, it) => s + it.subtotal, 0));
const total = subtotal; // Placeholder para impuestos futuros

function labelTipo(t: VentaDraftItem['tipo']) {
  if (t === 'producto_automotriz') return 'Automotriz';
  if (t === 'producto_despensa') return 'Despensa';
  return 'Servicio';
}

const puedeAgregar = computed(() => {
  if (!nuevo.value.referencia_id || nuevo.value.cantidad <= 0) return false;
  // Para productos exigir precio > 0; servicios pueden iniciar con 0 y editar
  if (nuevo.value.tipo !== 'servicio' && nuevo.value.precio_unitario <= 0) return false;
  return true;
});
function agregarItem() {
  if (!puedeAgregar.value) { console.debug('[agregarItem] bloqueo condiciones', { ref: nuevo.value.referencia_id, cant: nuevo.value.cantidad, precio: nuevo.value.precio_unitario, tipo: nuevo.value.tipo }); return; }
  const opt: any = (opcionesFiltradas.value || []).find(o => o.id === nuevo.value.referencia_id);
  if (!opt) { console.warn('[agregarItem] opción no encontrada', nuevo.value.referencia_id, opcionesFiltradas.value); return; }
  // Si precio_unitario está en 0 pero opción tiene precio, usarlo
  if (nuevo.value.precio_unitario === 0 && opt.precio) {
    nuevo.value.precio_unitario = Number(opt.precio) || 0;
  }
  // Seguridad: para productos nunca permitir push con precio 0
  if (nuevo.value.tipo !== 'servicio' && nuevo.value.precio_unitario <= 0) {
    toast({ type: 'error', text: 'Precio inválido para producto' });
    return;
  }
  // Validación de stock solo aplica a productos
  if (nuevo.value.tipo !== 'servicio' && opt.stock != null) {
    const existenteCant = items.value.filter(i => i.tipo === nuevo.value.tipo && i.referencia_id === nuevo.value.referencia_id)
      .reduce((s, i) => s + i.cantidad, 0);
    if (existenteCant + nuevo.value.cantidad > opt.stock) {
      toast({ type: 'error', text: `Stock insuficiente. Disponible: ${opt.stock - existenteCant}` });
      return;
    }
  }
  const existente = items.value.find(i => i.tipo === nuevo.value.tipo && i.referencia_id === nuevo.value.referencia_id);
  if (existente) {
    const nuevaCantidad = existente.cantidad + nuevo.value.cantidad;
    if (nuevo.value.tipo !== 'servicio' && opt.stock != null && nuevaCantidad > opt.stock) {
      toast({ type: 'error', text: `Stock insuficiente. Máximo permitido: ${opt.stock}` });
      return;
    }
    existente.cantidad = nuevaCantidad;
    existente.precio_unitario = nuevo.value.precio_unitario;
    existente.subtotal = existente.cantidad * existente.precio_unitario;
  } else {
    items.value.push({
      idTemp: genId(),
      tipo: nuevo.value.tipo,
      referencia_id: Number(nuevo.value.referencia_id),
      nombre: opt.nombre,
      cantidad: nuevo.value.cantidad,
      precio_unitario: nuevo.value.precio_unitario,
      subtotal: nuevo.value.cantidad * nuevo.value.precio_unitario
    });
  }
  nuevo.value.referencia_id = '';
  nuevo.value.cantidad = 1;
  nuevo.value.precio_unitario = 0;
}

function editarCantidad(it: VentaDraftItem, delta: number) {
  const opt: any = opcionesFiltradas.value.find(o => o.id === it.referencia_id) || {};
  const nueva = it.cantidad + delta;
  if (nueva <= 0) return;
  if (it.tipo !== 'servicio' && opt.stock != null && nueva > opt.stock) return;
  it.cantidad = nueva;
  it.subtotal = it.cantidad * it.precio_unitario;
}

function editarPrecio(it: VentaDraftItem, nuevoPrecio: number) {
  if (nuevoPrecio < 0) return;
  it.precio_unitario = nuevoPrecio;
  it.subtotal = it.cantidad * it.precio_unitario;
}

function removeItem(idTemp: string) { items.value = items.value.filter(i => i.idTemp !== idTemp); }

async function confirmarVenta() {
  if (!items.value.length) return;
  saving.value = true;
  try {
    if (items.value.length === 1) {
      const it = items.value[0];
      await ventaStore.createVentaGenerica({
        tipo: it.tipo,
        referencia_id: it.referencia_id,
        cantidad: it.cantidad,
        precio_unitario: it.precio_unitario,
        cliente_id: clienteId.value ? Number(clienteId.value) : undefined,
        generar_factura: generarFactura.value,
      });
    } else {
      if (typeof (ventaStore as any).createVentaMixta !== 'function') {
        console.error('[confirmarVenta] createVentaMixta no disponible en store', Object.keys(ventaStore));
        // fallback: múltiples llamadas (mantiene comportamiento anterior)
        for (const it of items.value) {
          await ventaStore.createVentaGenerica({
            tipo: it.tipo,
            referencia_id: it.referencia_id,
            cantidad: it.cantidad,
            precio_unitario: it.precio_unitario,
            cliente_id: clienteId.value ? Number(clienteId.value) : undefined,
            generar_factura: generarFactura.value,
          });
        }
      } else {
        await (ventaStore as any).createVentaMixta({
        items: items.value.map(it => ({
          tipo: it.tipo,
          referencia_id: it.referencia_id,
          cantidad: it.cantidad,
          precio_unitario: it.precio_unitario,
          nombre: it.nombre,
        })),
        cliente_id: clienteId.value ? Number(clienteId.value) : undefined,
        generar_factura: generarFactura.value,
        });
      }
    }
    router.push({ name: 'ventas.index' });
  } finally {
    saving.value = false;
  }
}

function formatCurrency(v: number) { return new Intl.NumberFormat('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(v); }

onMounted(async () => {
  await Promise.all([
    ventaStore.fetchProductosDisponibles(),
    ventaStore.fetchClientes(),
    servicioStore.fetchServiciosActivos?.(),
  ]);
  // después de cargar datos intentar sincronizar (por si usuario ya seleccionó algo rápido)
  syncPrecio('mounted');
});

// Watcher adicional más directo: si es producto y hay opción seleccionada, fuerza precio
watch([() => nuevo.value.tipo, () => nuevo.value.referencia_id, selectedOpt], () => {
  if (nuevo.value.tipo === 'servicio') return; // servicios pueden editar libre
  const opt = selectedOpt.value;
  if (opt) {
    const precio = Number(opt.precio) || 0;
    if (precio && nuevo.value.precio_unitario !== precio) {
      nuevo.value.precio_unitario = precio;
      console.debug('[precio-sync watcher] aplicado', { precio, id: opt.id });
    }
  }
}, { immediate: true });
</script>
<style scoped>
.form-label { display:block; font-size:0.75rem; font-weight:600; color:#374151; margin-bottom:0.25rem; text-transform:uppercase; letter-spacing:.05em; }
.form-input, .form-select { width:100%; border:1px solid #d1d5db; border-radius:0.375rem; padding:0.4rem 0.55rem; font-size:0.875rem; }
.btn { display:inline-flex; align-items:center; gap:0.25rem; padding:0.55rem 0.9rem; border-radius:0.375rem; font-size:0.875rem; font-weight:500; }
.btn-primary { background:#2563eb; color:#fff; }
.btn-primary:hover { background:#1d4ed8; }
.btn-secondary { background:#6b7280; color:#fff; }
.btn-secondary:hover { background:#4b5563; }
</style>
