import apiService from './api';
import type { ProductoAutomotriz, ProductoDespensa, PaginatedResponse, CreateProductoAutomotrizRequest, UpdateProductoAutomotrizRequest, CreateProductoDespensaRequest, UpdateProductoDespensaRequest } from '@/types';

export interface ProductoFilters {
  page?: number;
  per_page?: number;
  search?: string;
  activo?: boolean;
  categoria?: string;
}

export interface StockUpdate {
  stock: number;
  motivo?: string;
}

class ProductoService {
  // ===== MÉTODOS GENERALES =====
  
  async getAllProductos(filters: ProductoFilters = {}): Promise<any> {
    const params = new URLSearchParams();
    
    Object.entries(filters).forEach(([key, value]) => {
      if (value !== undefined && value !== null && value !== '') {
        params.append(key, value.toString());
      }
    });

    const response = await apiService.get(`/api/productos?${params.toString()}`);
    return response.data;
  }

  async getMetricas(): Promise<any> {
    const response = await apiService.get('/api/productos/metricas');
    return response.data;
  }

  // ===== PRODUCTOS AUTOMOTRICES =====
  
  async getProductosAutomotrices(filters: ProductoFilters = {}): Promise<PaginatedResponse<ProductoAutomotriz>> {
    const params = new URLSearchParams();
    
    Object.entries(filters).forEach(([key, value]) => {
      if (value !== undefined && value !== null && value !== '') {
        params.append(key, value.toString());
      }
    });

    const response = await apiService.get(`/api/productos/automotrices?${params.toString()}`);
    return response.data;
  }

  async getProductoAutomotriz(id: number): Promise<ProductoAutomotriz> {
    const response = await apiService.get(`/api/productos/automotrices/${id}`);
    return response.data.data;
  }

  async createProductoAutomotriz(data: CreateProductoAutomotrizRequest): Promise<ProductoAutomotriz> {
    const response = await apiService.post('/api/productos/automotrices', data);
    return response.data.data;
  }

  async updateProductoAutomotriz(id: number, data: UpdateProductoAutomotrizRequest): Promise<ProductoAutomotriz> {
    const response = await apiService.put(`/api/productos/automotrices/${id}`, data);
    return response.data.data;
  }

  async deleteProductoAutomotriz(id: number): Promise<void> {
    await apiService.delete(`/api/productos/automotrices/${id}`);
  }

  async restoreProductoAutomotriz(id: number): Promise<ProductoAutomotriz> {
    const response = await apiService.put(`/api/productos/automotrices/${id}/restore`);
    return response.data.data;
  }

  async getTrashedAutomotrices(): Promise<ProductoAutomotriz[]> {
    const response = await apiService.get('/api/productos/automotrices/trashed');
    return response.data.data;
  }

  async updateStockAutomotriz(id: number, data: StockUpdate): Promise<ProductoAutomotriz> {
    const response = await apiService.put(`/api/productos/automotrices/${id}/stock`, data);
    return response.data.data;
  }

  // ===== PRODUCTOS DE DESPENSA =====
  
  async getProductosDespensa(filters: ProductoFilters = {}): Promise<PaginatedResponse<ProductoDespensa>> {
    const params = new URLSearchParams();
    
    Object.entries(filters).forEach(([key, value]) => {
      if (value !== undefined && value !== null && value !== '') {
        params.append(key, value.toString());
      }
    });

    const response = await apiService.get(`/api/productos/despensa?${params.toString()}`);
    return response.data;
  }

  async getProductoDespensa(id: number): Promise<ProductoDespensa> {
    const response = await apiService.get(`/api/productos/despensa/${id}`);
    return response.data.data;
  }

  async createProductoDespensa(data: CreateProductoDespensaRequest): Promise<ProductoDespensa> {
    const response = await apiService.post('/api/productos/despensa', data);
    return response.data.data;
  }

  async updateProductoDespensa(id: number, data: UpdateProductoDespensaRequest): Promise<ProductoDespensa> {
    const response = await apiService.put(`/api/productos/despensa/${id}`, data);
    return response.data.data;
  }

  async deleteProductoDespensa(id: number): Promise<void> {
    await apiService.delete(`/api/productos/despensa/${id}`);
  }

  async restoreProductoDespensa(id: number): Promise<ProductoDespensa> {
    const response = await apiService.put(`/api/productos/despensa/${id}/restore`);
    return response.data.data;
  }

  async getTrashedDespensa(): Promise<ProductoDespensa[]> {
    const response = await apiService.get('/api/productos/despensa/trashed');
    return response.data.data;
  }

  async updateStockDespensa(id: number, data: StockUpdate): Promise<ProductoDespensa> {
    const response = await apiService.put(`/api/productos/despensa/${id}/stock`, data);
    return response.data.data;
  }
}

export default new ProductoService();
