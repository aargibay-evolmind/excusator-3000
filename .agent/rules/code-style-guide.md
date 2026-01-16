---
trigger: always_on
---

### PHP Development Standards
**Contexto:** Generación de código Backend.

1.  **Modern PHP:** Usa estrictamente PHP 8.2+.
    - Todo archivo comienza con `declare(strict_types=1);`.
    - Usa *Constructor Property Promotion*, *Readonly Classes* y *Enums*.
    - Tipado estricto obligatorio en argumentos, propiedades y retornos (incluso `void`).

2.  **Arquitectura & SOLID:**
    - **Single Responsibility:** Una clase, una responsabilidad.
    - **Controladores:** Deben ser `__invoke` (invokable) y atender una única ruta/acción.
    - **Desacoplamiento:**
        - NUNCA pases objetos del Framework (Request, Eloquent Models) a la capa de Servicio/Dominio.
        - Usa **DTOs** (Data Transfer Objects) para mover datos entre Controlador y Servicio.
    - **Inyección de Dependencias:** Siempre por constructor. Prohibido usar Facades estáticas o helpers globales en lógica de negocio.

3.  **Clean Code:**
    - Aplica "Early Return" para reducir la complejidad ciclomática (evita `else`).

4. **Documentacion:**
    - En la raiz del proyecto existe un directorio llamado docs donde encontraras toda la documentacion necesaria.