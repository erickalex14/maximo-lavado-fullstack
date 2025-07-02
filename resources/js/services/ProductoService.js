import { BaseService } from './BaseService';

//SERVICIO PARA GESTIONAR PRODUCTOS

class ProductoService extends BaseService {
  constructor() {
    super('/productos');
  }

  // LOS METODOS CRUD BÁSICOS YA ESTÁN HEREDADOS DEL BaseService:

  // METODOS ESPECÍFICOS DE PRODUCTOS

  // OBTENER MÉTRICAS DE PRODUCTOS

  async getMetricas(params = {}) {
    return this.metricas(params);
  }

  // PRODUCTOS AUTOMOTRICES

  // OBTENER TODOS LOS PRODUCTOS AUTOMOTRICES

  async getProductosAutomotrices() {
    return this.customAction('automotrices', { method: 'GET' });
  }

  // CREAR UN PRODUCTO AUTOMOTRIZ

  async createProductoAutomotriz(data) {
    return this.customAction('automotrices', {
      method: 'POST',
      data: data
    });
  }

  // OBTENER UN PRODUCTO AUTOMOTRIZ POR ID

  async getProductoAutomotriz(id) {
    return this.customAction(`automotrices/${id}`, { method: 'GET' });
  }

  // ACTUALIZAR UN PRODUCTO AUTOMOTRIZ

  async updateProductoAutomotriz(id, data) {
    return this.customAction(`automotrices/${id}`, {
      method: 'PUT',
      data: data
    });
  }

  // ELIMINAR UN PRODUCTO AUTOMOTRIZ

  async deleteProductoAutomotriz(id) {
    return this.customAction(`automotrices/${id}`, { method: 'DELETE' });
  }

  // RESTAURAR UN PRODUCTO AUTOMOTRIZ

  async restoreProductoAutomotriz(id) {
    return this.customAction(`automotrices/${id}/restore`, { method: 'PUT' });
  }

  // OBTENER PRODUCTOS AUTOMOTRICES ELIMINADOS

  async getTrashedProductosAutomotrices() {
    return this.customAction('automotrices/trashed', { method: 'GET' });
  }

  // ACTUALIZAR STOCK DE PRODUCTO AUTOMOTRIZ

  async updateStockAutomotriz(id, stock) {
    return this.customAction(`automotrices/${id}/stock`, {
      method: 'PUT',
      data: { stock }
    });
  }

  // PRODUCTOS DE DESPENSA

  // OBTENER TODOS LOS PRODUCTOS DE DESPENSA

  async getProductosDespensa() {
    return this.customAction('despensa', { method: 'GET' });
  }

  // CREAR UN PRODUCTO DE DESPENSA

  async createProductoDespensa(data) {
    return this.customAction('despensa', {
      method: 'POST',
      data: data
    });
  }

  // OBTENER UN PRODUCTO DE DESPENSA POR ID

  async getProductoDespensa(id) {
    return this.customAction(`despensa/${id}`, { method: 'GET' });
  }

  // ACTUALIZAR UN PRODUCTO DE DESPENSA

  async updateProductoDespensa(id, data) {
    return this.customAction(`despensa/${id}`, {
      method: 'PUT',
      data: data
    });
  }

  // ELIMINAR UN PRODUCTO DE DESPENSA

  async deleteProductoDespensa(id) {
    return this.customAction(`despensa/${id}`, { method: 'DELETE' });
  }

  // RESTAURAR UN PRODUCTO DE DESPENSA

  async restoreProductoDespensa(id) {
    return this.customAction(`despensa/${id}/restore`, { method: 'PUT' });
  }

  // OBTENER PRODUCTOS DE DESPENSA ELIMINADOS

  async getTrashedProductosDespensa() {
    return this.customAction('despensa/trashed', { method: 'GET' });
  }

  // ACTUALIZAR STOCK DE PRODUCTO DE DESPENSA

  async updateStockDespensa(id, stock) {
    return this.customAction(`despensa/${id}/stock`, {
      method: 'PUT',
      data: { stock }
    });
  }

  // METODOS DE CONVENIENCIA PARA PRODUCTOS

  // OBTENER TODOS LOS PRODUCTOS (AUTOMOTRICES Y DESPENSA)

  async getTodosLosProductos() {
    return this.index();
  }

  // OBTENER PRODUCTOS CON STOCK BAJO

  async getProductosConStockBajo(minimo = 5) {
    try {
      const [automotrices, despensa] = await Promise.all([
        this.getProductosAutomotrices(),
        this.getProductosDespensa()
      ]);

      const productosStockBajo = [];

      // Filtrar productos automotrices con stock bajo
      if (automotrices.data) {
        const automotricesStockBajo = automotrices.data.filter(producto => producto.stock <= minimo);
        productosStockBajo.push(...automotricesStockBajo);
      }

      // Filtrar productos de despensa con stock bajo
      if (despensa.data) {
        const despensaStockBajo = despensa.data.filter(producto => producto.stock <= minimo);
        productosStockBajo.push(...despensaStockBajo);
      }

      return productosStockBajo;
    } catch (error) {
      throw error;
    }
  }

  // OBTENER PRODUCTOS ACTIVOS POR TIPO

  async getProductosActivosPorTipo(tipo) {
    if (tipo === 'automotriz') {
      const productos = await this.getProductosAutomotrices();
      return productos.data ? productos.data.filter(p => p.activo) : [];
    } else if (tipo === 'despensa') {
      const productos = await this.getProductosDespensa();
      return productos.data ? productos.data.filter(p => p.activo) : [];
    } else {
      throw new Error('Tipo de producto no válido. Use "automotriz" o "despensa"');
    }
  }

  // BUSCAR PRODUCTOS POR NOMBRE

  async buscarProductos(termino) {
    try {
      const [automotrices, despensa] = await Promise.all([
        this.getProductosAutomotrices(),
        this.getProductosDespensa()
      ]);

      const resultados = [];

      // Buscar en productos automotrices
      if (automotrices.data) {
        const automotricesEncontrados = automotrices.data.filter(producto =>
          producto.nombre.toLowerCase().includes(termino.toLowerCase()) ||
          (producto.codigo && producto.codigo.toLowerCase().includes(termino.toLowerCase()))
        );
        resultados.push(...automotricesEncontrados);
      }

      // Buscar en productos de despensa
      if (despensa.data) {
        const despensaEncontrados = despensa.data.filter(producto =>
          producto.nombre.toLowerCase().includes(termino.toLowerCase())
        );
        resultados.push(...despensaEncontrados);
      }

      return resultados;
    } catch (error) {
      throw error;
    }
  }

  // OBTENER RESUMEN DE INVENTARIO

  async getResumenInventario() {
    try {
      const [automotrices, despensa, metricas] = await Promise.all([
        this.getProductosAutomotrices(),
        this.getProductosDespensa(),
        this.getMetricas()
      ]);

      const totalAutomotrices = automotrices.data ? automotrices.data.length : 0;
      const totalDespensa = despensa.data ? despensa.data.length : 0;
      const stockBajo = await this.getProductosConStockBajo();

      return {
        total_productos: totalAutomotrices + totalDespensa,
        productos_automotrices: totalAutomotrices,
        productos_despensa: totalDespensa,
        productos_stock_bajo: stockBajo.length,
        metricas: metricas.data || {}
      };
    } catch (error) {
      throw error;
    }
  }

  // ACTUALIZAR STOCK MASIVO

  async updateStockMasivo(actualizaciones) {
    const resultados = [];
    
    for (const actualizacion of actualizaciones) {
      try {
        const { id, tipo, stock } = actualizacion;
        
        let resultado;
        if (tipo === 'automotriz') {
          resultado = await this.updateStockAutomotriz(id, stock);
        } else if (tipo === 'despensa') {
          resultado = await this.updateStockDespensa(id, stock);
        } else {
          throw new Error(`Tipo de producto no válido: ${tipo}`);
        }
        
        resultados.push({ id, tipo, success: true, data: resultado });
      } catch (error) {
        resultados.push({ 
          id: actualizacion.id, 
          tipo: actualizacion.tipo, 
          success: false, 
          error: error.message 
        });
      }
    }
    
    return resultados;
  }
}

// Instancia única del servicio
const productoService = new ProductoService();

export default productoService;
