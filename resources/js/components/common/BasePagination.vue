<template>
  <div v-if="totalPages > 1" class="base-pagination">
    <!-- Pagination info -->
    <div v-if="showInfo" class="pagination-info">
      <span class="text-sm text-gray-700">
        Mostrando
        <span class="font-medium">{{ startItem }}</span>
        a
        <span class="font-medium">{{ endItem }}</span>
        de
        <span class="font-medium">{{ total }}</span>
        {{ total === 1 ? 'resultado' : 'resultados' }}
      </span>
    </div>

    <!-- Pagination controls -->
    <nav class="pagination-nav" aria-label="Paginación">
      <div class="pagination-controls">
        <!-- Previous button -->
        <button
          type="button"
          class="pagination-button pagination-button--prev"
          :disabled="currentPage === 1"
          :aria-label="'Ir a la página anterior'"
          @click="goToPage(currentPage - 1)"
        >
          <svg
            class="w-4 h-4"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M15 19l-7-7 7-7"
            />
          </svg>
          <span v-if="showLabels" class="hidden sm:inline ml-1">Anterior</span>
        </button>

        <!-- Page numbers -->
        <div class="pagination-pages">
          <!-- First page -->
          <button
            v-if="showFirstLast && currentPage > maxVisiblePages"
            type="button"
            class="pagination-button pagination-button--page"
            :aria-label="'Ir a la página 1'"
            @click="goToPage(1)"
          >
            1
          </button>

          <!-- First ellipsis -->
          <span
            v-if="showFirstLast && currentPage > maxVisiblePages + 1"
            class="pagination-ellipsis"
          >
            ...
          </span>

          <!-- Visible page numbers -->
          <button
            v-for="page in visiblePages"
            :key="page"
            type="button"
            class="pagination-button pagination-button--page"
            :class="{
              'pagination-button--current': page === currentPage
            }"
            :aria-label="`${page === currentPage ? 'Página actual, página' : 'Ir a la página'} ${page}`"
            :aria-current="page === currentPage ? 'page' : undefined"
            @click="goToPage(page)"
          >
            {{ page }}
          </button>

          <!-- Last ellipsis -->
          <span
            v-if="showFirstLast && currentPage < totalPages - maxVisiblePages"
            class="pagination-ellipsis"
          >
            ...
          </span>

          <!-- Last page -->
          <button
            v-if="showFirstLast && currentPage < totalPages - maxVisiblePages + 1"
            type="button"
            class="pagination-button pagination-button--page"
            :aria-label="`Ir a la página ${totalPages}`"
            @click="goToPage(totalPages)"
          >
            {{ totalPages }}
          </button>
        </div>

        <!-- Next button -->
        <button
          type="button"
          class="pagination-button pagination-button--next"
          :disabled="currentPage === totalPages"
          :aria-label="'Ir a la página siguiente'"
          @click="goToPage(currentPage + 1)"
        >
          <span v-if="showLabels" class="hidden sm:inline mr-1">Siguiente</span>
          <svg
            class="w-4 h-4"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M9 5l7 7-7 7"
            />
          </svg>
        </button>
      </div>

      <!-- Page size selector -->
      <div v-if="showPageSizeSelector" class="pagination-page-size">
        <label for="page-size" class="text-sm text-gray-700 mr-2">
          Mostrar:
        </label>
        <select
          id="page-size"
          v-model="localPageSize"
          class="input-base text-sm py-1 pr-8"
          @change="handlePageSizeChange"
        >
          <option
            v-for="size in pageSizeOptions"
            :key="size"
            :value="size"
          >
            {{ size }}
          </option>
        </select>
        <span class="text-sm text-gray-700 ml-2">por página</span>
      </div>
    </nav>

    <!-- Mobile pagination (simplified) -->
    <div v-if="simpleMobile" class="pagination-mobile sm:hidden">
      <button
        type="button"
        class="pagination-button pagination-button--mobile"
        :disabled="currentPage === 1"
        @click="goToPage(currentPage - 1)"
      >
        <svg
          class="w-4 h-4 mr-1"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M15 19l-7-7 7-7"
          />
        </svg>
        Anterior
      </button>

      <span class="pagination-mobile-info">
        {{ currentPage }} de {{ totalPages }}
      </span>

      <button
        type="button"
        class="pagination-button pagination-button--mobile"
        :disabled="currentPage === totalPages"
        @click="goToPage(currentPage + 1)"
      >
        Siguiente
        <svg
          class="w-4 h-4 ml-1"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M9 5l7 7-7 7"
          />
        </svg>
      </button>
    </div>

    <!-- Jump to page (optional) -->
    <div v-if="showJumpToPage" class="pagination-jump">
      <label for="jump-page" class="text-sm text-gray-700 mr-2">
        Ir a la página:
      </label>
      <input
        id="jump-page"
        v-model.number="jumpToPageValue"
        type="number"
        min="1"
        :max="totalPages"
        class="input-base text-sm w-16 py-1"
        @keydown.enter="handleJumpToPage"
      />
      <button
        type="button"
        class="btn btn-sm btn-outline ml-2"
        @click="handleJumpToPage"
      >
        Ir
      </button>
    </div>
  </div>
</template>

<script>
import { ref, computed, watch } from 'vue'

export default {
  name: 'BasePagination',
  props: {
    // Current page (1-based)
    currentPage: {
      type: Number,
      default: 1,
      validator: (value) => value >= 1
    },
    
    // Total number of items
    total: {
      type: Number,
      required: true,
      validator: (value) => value >= 0
    },
    
    // Items per page
    pageSize: {
      type: Number,
      default: 10,
      validator: (value) => value >= 1
    },
    
    // Display options
    showInfo: {
      type: Boolean,
      default: true
    },
    showLabels: {
      type: Boolean,
      default: true
    },
    showFirstLast: {
      type: Boolean,
      default: true
    },
    showPageSizeSelector: {
      type: Boolean,
      default: false
    },
    showJumpToPage: {
      type: Boolean,
      default: false
    },
    simpleMobile: {
      type: Boolean,
      default: true
    },
    
    // Behavior
    maxVisiblePages: {
      type: Number,
      default: 2,
      validator: (value) => value >= 1
    },
    pageSizeOptions: {
      type: Array,
      default: () => [10, 25, 50, 100]
    },
    
    // Styling
    size: {
      type: String,
      default: 'md',
      validator: (value) => ['sm', 'md', 'lg'].includes(value)
    },
    variant: {
      type: String,
      default: 'default',
      validator: (value) => ['default', 'minimal', 'rounded'].includes(value)
    }
  },
  
  emits: ['page-change', 'page-size-change'],
  
  setup(props, { emit }) {
    // State
    const localPageSize = ref(props.pageSize)
    const jumpToPageValue = ref(props.currentPage)
    
    // Computed
    const totalPages = computed(() => {
      return Math.ceil(props.total / props.pageSize)
    })
    
    const startItem = computed(() => {
      if (props.total === 0) return 0
      return (props.currentPage - 1) * props.pageSize + 1
    })
    
    const endItem = computed(() => {
      const end = props.currentPage * props.pageSize
      return Math.min(end, props.total)
    })
    
    const visiblePages = computed(() => {
      const pages = []
      const total = totalPages.value
      const current = props.currentPage
      const maxVisible = props.maxVisiblePages
      
      if (total <= maxVisible * 2 + 1) {
        // Show all pages if total is small
        for (let i = 1; i <= total; i++) {
          pages.push(i)
        }
      } else {
        // Calculate start and end of visible range
        let start = Math.max(1, current - maxVisible)
        let end = Math.min(total, current + maxVisible)
        
        // Adjust if we're near the beginning or end
        if (current <= maxVisible) {
          end = Math.min(total, maxVisible * 2 + 1)
        } else if (current > total - maxVisible) {
          start = Math.max(1, total - maxVisible * 2)
        }
        
        for (let i = start; i <= end; i++) {
          pages.push(i)
        }
      }
      
      return pages
    })
    
    // Methods
    const goToPage = (page) => {
      if (page < 1 || page > totalPages.value || page === props.currentPage) {
        return
      }
      
      emit('page-change', page)
    }
    
    const handlePageSizeChange = () => {
      emit('page-size-change', localPageSize.value)
    }
    
    const handleJumpToPage = () => {
      const page = jumpToPageValue.value
      if (page && page >= 1 && page <= totalPages.value) {
        goToPage(page)
      }
    }
    
    // Watchers
    watch(() => props.pageSize, (newPageSize) => {
      localPageSize.value = newPageSize
    })
    
    watch(() => props.currentPage, (newPage) => {
      jumpToPageValue.value = newPage
    })
    
    return {
      // State
      localPageSize,
      jumpToPageValue,
      
      // Computed
      totalPages,
      startItem,
      endItem,
      visiblePages,
      
      // Methods
      goToPage,
      handlePageSizeChange,
      handleJumpToPage
    }
  }
}
</script>

<style scoped>
.base-pagination {
  @apply flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4;
}

.pagination-info {
  @apply flex-shrink-0;
}

.pagination-nav {
  @apply flex flex-col sm:flex-row sm:items-center gap-4;
}

.pagination-controls {
  @apply flex items-center space-x-1;
}

.pagination-pages {
  @apply flex items-center space-x-1;
}

.pagination-button {
  @apply relative inline-flex items-center justify-center px-3 py-2 text-sm font-medium;
  @apply border border-gray-300 bg-white text-gray-700;
  @apply hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2;
  @apply transition-colors duration-150;
  @apply disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-white;
}

.pagination-button--prev {
  @apply rounded-l-md;
}

.pagination-button--next {
  @apply rounded-r-md;
}

.pagination-button--page {
  @apply min-w-[2.5rem];
}

.pagination-button--current {
  @apply bg-blue-600 text-white border-blue-600;
  @apply hover:bg-blue-700 disabled:hover:bg-blue-600;
}

.pagination-button--mobile {
  @apply flex-1 justify-center py-2;
}

.pagination-ellipsis {
  @apply inline-flex items-center justify-center px-3 py-2 text-sm text-gray-500;
  @apply min-w-[2.5rem];
}

.pagination-page-size {
  @apply flex items-center;
}

.pagination-mobile {
  @apply flex items-center justify-between space-x-4;
}

.pagination-mobile-info {
  @apply text-sm text-gray-700 font-medium;
}

.pagination-jump {
  @apply flex items-center mt-4 sm:mt-0;
}
</style>
