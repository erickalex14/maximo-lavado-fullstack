# Documentación de Autenticación - Laravel Sanctum

## Problema Resuelto: Endpoint `/api/user` devolvía 401 Unauthorized

### Descripción del Problema
El endpoint `/api/user` protegido por Sanctum siempre devolvía **401 Unauthorized**, mientras que otros endpoints protegidos funcionaban correctamente con el mismo token Bearer.

### Causa Raíz
**El nombre de ruta `/user` está reservado o interceptado por Laravel/Sanctum**. Esto causa conflictos internos que resultan en respuestas 401 sin ejecutar el middleware correctamente.

### Solución Implementada
- **Cambiar la ruta de `/user` a `/usuario`**
- Mantener la misma lógica en el controlador
- Agregar comentario explicativo en las rutas

### Código Final (routes/api.php)
```php
// Endpoint para obtener usuario autenticado
// NOTA IMPORTANTE: No usar '/user' como ruta - conflicto con rutas internas de Laravel/Sanctum
Route::middleware('auth:sanctum')->get('/usuario', [AuthController::class, 'user']);
```

### Configuración del Frontend
El frontend debe actualizarse para consumir:
- **Antes**: `GET /api/user`
- **Después**: `GET /api/usuario`

### Mejores Prácticas
1. **Nunca usar `/user` como endpoint** en APIs protegidas por Sanctum
2. Usar nombres descriptivos y en español para consistencia: `/usuario`, `/perfil`, `/mi-cuenta`
3. Siempre probar endpoints con diferentes nombres si hay problemas de autenticación inexplicables

### Otros Endpoints que Podrían Tener Conflictos
Evitar estos nombres reservados:
- `/user`
- `/auth`
- `/login` (ya usado correctamente)
- `/logout` (ya usado correctamente)
- `/token`
- `/sanctum`

### Configuración Funcional
- **Guard**: `sanctum` (por defecto en config/auth.php)
- **Middleware**: `auth:sanctum`
- **Headers requeridos**: `Authorization: Bearer {token}`
- **Content-Type**: `application/json`

### Testing
```bash
# Endpoint funcional
curl -H "Authorization: Bearer {token}" http://localhost:8000/api/usuario

# Respuesta esperada
{
    "success": true,
    "data": {
        "id": 1,
        "name": "Usuario",
        "email": "usuario@ejemplo.com",
        "created_at": "...",
        "updated_at": "..."
    }
}
```

---
**Fecha de resolución**:
**Desarrollador**: Yo y ayudita del copilot pa debuguear xddd
