import type { PaginatedResponse } from '@/types';

export interface Pagination {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number;
  to: number;
}

export function emptyPagination(): Pagination {
  return {
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
    from: 0,
    to: 0
  };
}

export function buildPagination<T>(resp: PaginatedResponse<T> | undefined | null): Pagination {
  if (!resp) return emptyPagination();
  return {
    current_page: resp.current_page || 1,
    last_page: resp.last_page || 1,
    per_page: resp.per_page || 15,
    total: resp.total || 0,
    from: resp.from || 0,
    to: resp.to || 0
  };
}

export function setArrayFromPagination<T>(resp: PaginatedResponse<T> | undefined | null): T[] {
  return resp?.data ? [...resp.data] : [];
}

// Util simple para extraer mensaje seguro de error desconocido
export function extractErrorMessage(err: unknown, fallback = 'Ocurrió un error'): string {
  if (typeof err === 'string') return err;
  if (err && typeof err === 'object') {
    const anyErr = err as any;
    return anyErr.message || anyErr.error || fallback;
  }
  return fallback;
}
