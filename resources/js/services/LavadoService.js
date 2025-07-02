import { BaseService } from './BaseService';

//SERVICIO PARA GESTIONAR LAVADOS

class LavadoService extends BaseService {
  constructor() {
    super('/lavados');
  }

  // LOS METODOS CRUD BÁSICOS YA ESTÁN HEREDADOS DEL BaseService:

  // METODOS ESPECÍFICOS DE LAVADOS 

  // OBTENER LAVADOS POR EMPLEADO

  async getByEmpleado(empleadoId, params = {}) {
    return this.customAction(`empleado/${empleadoId}`, {
      method: 'GET',
      data: params,
      useParams: true
    });
  }

  // OBTENER LAVADOS POR VEHÍCULO

  async getByVehiculo(vehiculoId, params = {}) {
    return this.customAction(`vehiculo/${vehiculoId}`, {
      method: 'GET',
      data: params,
      useParams: true
    });
  }

  //METODOS DE CONVENIENCIA PARA LAVADOS

  // OBTENER LAVADOS DE HOY, ESTA SEMANA, ESTE MES Y ESTE AÑO

  async getLavadosHoy(params = {}) {
    const today = new Date().toISOString().split('T')[0];
    return this.getByDay(today, params);
  }


  async getLavadosEstaSemana(params = {}) {
    const today = new Date().toISOString().split('T')[0];
    return this.getByWeek(today, params);
  }


  async getLavadosEsteMes(params = {}) {
    const now = new Date();
    return this.getByMonth(now.getFullYear(), now.getMonth() + 1, params);
  }


  async getLavadosEsteAnio(params = {}) {
    const now = new Date();
    return this.getByYear(now.getFullYear(), params);
  }

  // FILTROS ESPECÍFICOS (usando el endpoint principal)

  // OBTENER LAVADOS POR ESTADO

  async getByEstado(estado, params = {}) {
    return this.index({ estado, ...params });
  }

  // OBTENER LAVADOS POR TIPO DE LAVADO

  async getByTipoLavado(tipoLavado, params = {}) {
    return this.index({ tipo_lavado: tipoLavado, ...params });
  }

  // OBTENER LAVADOS POR CLIENTE, RANGO DE FECHAS Y RANGO DE PRECIOS

  async getByCliente(clienteId, params = {}) {
    return this.index({ cliente_id: clienteId, ...params });
  }


  async getByRangoFechas(fechaInicio, fechaFin, params = {}) {
    return this.index({ 
      fecha_inicio: fechaInicio, 
      fecha_fin: fechaFin, 
      ...params 
    });
  }


  async getByRangoPrecios(precioMin, precioMax, params = {}) {
    return this.index({ 
      precio_min: precioMin, 
      precio_max: precioMax, 
      ...params 
    });
  }
}

// Instancia única del servicio
const lavadoService = new LavadoService();

export default lavadoService;
