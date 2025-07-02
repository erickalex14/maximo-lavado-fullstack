<template>
  <div class="auth-layout">
    <!-- Background Pattern -->
    <div class="auth-background">
      <div class="pattern-overlay"></div>
    </div>
    
    <!-- Main Content -->
    <div class="auth-container">
      <!-- Left Side - Branding -->
      <div class="auth-brand">
        <div class="brand-content">
          <div class="logo-section">
            <div class="logo-container">
              <svg class="logo-icon" viewBox="0 0 24 24" fill="currentColor">
                <path d="M19 7h-3V6a4 4 0 0 0-8 0v1H5a1 1 0 0 0-1 1v11a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3V8a1 1 0 0 0-1-1zM10 6a2 2 0 0 1 4 0v1h-4V6zm8 13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V9h2v1a1 1 0 0 0 2 0V9h4v1a1 1 0 0 0 2 0V9h2v10z"/>
              </svg>
              <div class="logo-text">
                <h1 class="app-name">Máximo</h1>
                <span class="app-tagline">Lavado</span>
              </div>
            </div>
          </div>
          
          <div class="brand-description">
            <h2 class="brand-title">Sistema de Gestión Integral</h2>
            <p class="brand-subtitle">
              Administra tu negocio de lavado de autos de forma profesional. 
              Controla clientes, servicios, inventario y finanzas desde una sola plataforma.
            </p>
          </div>
          
          <!-- Features List -->
          <div class="features-list">
            <div class="feature-item">
              <div class="feature-icon">
                <svg viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
              </div>
              <span>Gestión de clientes y vehículos</span>
            </div>
            <div class="feature-item">
              <div class="feature-icon">
                <svg viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
              </div>
              <span>Control de servicios de lavado</span>
            </div>
            <div class="feature-item">
              <div class="feature-icon">
                <svg viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
              </div>
              <span>Reportes financieros detallados</span>
            </div>
            <div class="feature-item">
              <div class="feature-icon">
                <svg viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
              </div>
              <span>Inventario y productos</span>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Right Side - Auth Form -->
      <div class="auth-form-container">
        <div class="auth-form-wrapper">
          <!-- Header -->
          <div class="auth-header">
            <slot name="header">
              <h3 class="auth-title">Iniciar Sesión</h3>
              <p class="auth-description">Accede a tu cuenta para continuar</p>
            </slot>
          </div>
          
          <!-- Form Content -->
          <div class="auth-content">
            <slot />
          </div>
          
          <!-- Footer -->
          <div class="auth-footer">
            <slot name="footer">
              <p class="auth-footer-text">
                © {{ currentYear }} Máximo Lavado. Todos los derechos reservados.
              </p>
            </slot>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

// Computed
const currentYear = computed(() => new Date().getFullYear())
</script>

<style scoped>
.auth-layout {
  min-height: 100vh;
  display: flex;
  position: relative;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.auth-background {
  position: absolute;
  inset: 0;
  z-index: 0;
}

.pattern-overlay {
  position: absolute;
  inset: 0;
  background-image: 
    radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.1) 1px, transparent 1px),
    radial-gradient(circle at 80% 50%, rgba(255, 255, 255, 0.1) 1px, transparent 1px),
    radial-gradient(circle at 40% 20%, rgba(255, 255, 255, 0.05) 1px, transparent 1px),
    radial-gradient(circle at 60% 80%, rgba(255, 255, 255, 0.05) 1px, transparent 1px);
  background-size: 50px 50px, 60px 60px, 80px 80px, 100px 100px;
  animation: float 20s ease-in-out infinite;
}

@keyframes float {
  0%, 100% { transform: translate(0, 0) rotate(0deg); }
  33% { transform: translate(30px, -30px) rotate(120deg); }
  66% { transform: translate(-20px, 20px) rotate(240deg); }
}

.auth-container {
  position: relative;
  z-index: 1;
  display: flex;
  width: 100%;
  min-height: 100vh;
}

/* Left Side - Branding */
.auth-brand {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 3rem;
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
}

.brand-content {
  max-width: 28rem;
  color: white;
}

.logo-container {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 3rem;
}

.logo-icon {
  width: 3rem;
  height: 3rem;
  color: rgba(255, 255, 255, 0.9);
}

.logo-text {
  display: flex;
  flex-direction: column;
}

.app-name {
  margin: 0;
  font-size: 2.25rem;
  font-weight: 800;
  color: white;
  line-height: 1;
}

.app-tagline {
  font-size: 1rem;
  color: rgba(255, 255, 255, 0.8);
  font-weight: 600;
  margin-top: 0.25rem;
}

.brand-description {
  margin-bottom: 2.5rem;
}

.brand-title {
  margin: 0 0 1rem 0;
  font-size: 1.875rem;
  font-weight: 700;
  color: white;
  line-height: 1.2;
}

.brand-subtitle {
  margin: 0;
  font-size: 1.125rem;
  color: rgba(255, 255, 255, 0.8);
  line-height: 1.6;
}

.features-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.feature-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 1rem;
  color: rgba(255, 255, 255, 0.9);
}

.feature-icon {
  width: 1.25rem;
  height: 1.25rem;
  flex-shrink: 0;
  color: rgba(255, 255, 255, 0.9);
}

/* Right Side - Auth Form */
.auth-form-container {
  flex: 0 0 28rem;
  display: flex;
  align-items: center;
  justify-content: center;
  background: white;
  box-shadow: -10px 0 25px rgba(0, 0, 0, 0.1);
}

.auth-form-wrapper {
  width: 100%;
  max-width: 20rem;
  padding: 0 1.5rem;
}

.auth-header {
  text-align: center;
  margin-bottom: 2rem;
}

.auth-title {
  margin: 0 0 0.5rem 0;
  font-size: 1.875rem;
  font-weight: 700;
  color: var(--color-text-900);
}

.auth-description {
  margin: 0;
  font-size: 1rem;
  color: var(--color-text-600);
}

.auth-content {
  margin-bottom: 2rem;
}

.auth-footer {
  text-align: center;
  padding-top: 1.5rem;
  border-top: 1px solid var(--color-border-200);
}

.auth-footer-text {
  margin: 0;
  font-size: 0.875rem;
  color: var(--color-text-500);
}

/* Mobile Responsive */
@media (max-width: 1024px) {
  .auth-brand {
    display: none;
  }
  
  .auth-form-container {
    flex: 1;
  }
}

@media (max-width: 640px) {
  .auth-form-container {
    padding: 1.5rem;
  }
  
  .auth-form-wrapper {
    max-width: none;
    padding: 0;
  }
  
  .auth-title {
    font-size: 1.5rem;
  }
  
  .brand-content {
    padding: 2rem;
  }
  
  .app-name {
    font-size: 1.875rem;
  }
  
  .brand-title {
    font-size: 1.5rem;
  }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
  .auth-form-container {
    background: var(--color-bg-900);
  }
  
  .auth-title {
    color: var(--color-text-50);
  }
  
  .auth-description {
    color: var(--color-text-400);
  }
  
  .auth-footer-text {
    color: var(--color-text-500);
  }
}
</style>
