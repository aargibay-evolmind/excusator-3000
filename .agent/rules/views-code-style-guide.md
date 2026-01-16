---
trigger: always_on
---

### Frontend & Webpack Strategy

**Contexto:** Generación de Vistas, JS y CSS.

1. **Regla "Cero Inline":**
    - Está PROHIBIDO escribir bloques `<script>` o `<style>` dentro de ficheros PHP/HTML.

2. **Estrategia "Un fichero por Vista":**
    - Para cada vista nueva (ej: `login.php`), se deben crear sus contrapartes compilables:
        - `assets/js/login.js`
        - `assets/scss/login.scss`
    - Solo usa archivos globales (`app.js`) para layouts comunes (Header/Footer).

3. **Webpack & Build:**
    - El código debe estar preparado para ser procesado por Webpack.
    - Usa **ES6 Modules** (import/export).
    - Usa **SCSS** con anidamiento (nesting).

4. **Selección DOM:**
    - Usa atributos `data-js` para seleccionar elementos en JS (ej: `querySelector('[data-js="btn-save"]')`), nunca clases CSS de estilo.

5. **Documentacion:**
    - En la raiz del proyecto existe un directorio llamado docs donde encontraras toda la documentacion necesaria.
