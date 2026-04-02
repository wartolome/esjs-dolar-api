# WP Modal Ads

Plugin de WordPress que muestra anuncios en ventanas modales interstitiales, completamente configurables desde el panel de administración.

## Características

- **Modal configurable** — pantalla completa o porcentaje personalizable (10–100 % de la pantalla).
- **URL del anuncio** — el contenido se carga dentro de un `<iframe>` desde cualquier URL.
- **Botón "Saltar anuncio"** — aparece en la esquina superior derecha. Se puede configurar cuántos segundos deben pasar antes de que sea clickeable.
- **Múltiples disparadores**:
  - Clic en cualquier lugar de la página.
  - Scroll (porcentaje configurable de la página).
  - Tiempo de espera (segundos configurables).
  - Intención de salida (mover el cursor hacia la parte superior del viewport).
- **Límite por hora** — controla cuántas veces como máximo se muestra el anuncio al mismo visitante en una hora (almacenado en `localStorage`).
- **Vista previa desde el admin** — botón para ver el modal antes de publicar los cambios.

## Instalación

1. Copia la carpeta `wp-modal-ads` en el directorio `wp-content/plugins/` de tu instalación de WordPress.
2. Ve a **Plugins → Plugins instalados** y activa **WP Modal Ads**.
3. Ve a **Ajustes → Modal Ads** para configurar el plugin.

## Configuración

| Campo | Descripción | Valor por defecto |
|---|---|---|
| Activar anuncios | Habilita o deshabilita el plugin sin desactivarlo. | Activado |
| URL del anuncio | URL que se cargará dentro del iframe del modal. | — |
| Tamaño del modal (%) | Porcentaje de pantalla que ocupará el modal (10–100). | 100 |
| Tiempo antes del botón Saltar (s) | Segundos que deben pasar para que el botón sea clickeable. `0` lo muestra de inmediato. | 5 |
| Disparador | `click`, `scroll`, `time` o `exit`. | click |
| Porcentaje de scroll | Porcentaje de scroll que activa el modal (disparador `scroll`). | 30 |
| Tiempo de espera (s) | Segundos antes de abrir el modal automáticamente (disparador `time`). | 3 |
| Máximo por hora | Número máximo de veces que se muestra al mismo visitante por hora. | 3 |

## Requisitos

- WordPress 5.8 o superior.
- PHP 7.4 o superior.
- El navegador del visitante debe permitir `localStorage` (activo por defecto en todos los navegadores modernos).

## Estructura de archivos

```
wp-modal-ads/
├── wp-modal-ads.php              # Cabecera del plugin e inicialización
├── includes/
│   ├── class-admin.php           # Panel de administración (Settings API)
│   └── class-modal-ads.php       # Frontend: enqueue de assets y HTML del modal
├── admin/
│   └── partials/
│       └── admin-panel.php       # Plantilla de la página de ajustes
└── assets/
    ├── css/
    │   ├── modal-ads.css         # Estilos del modal (frontend)
    │   └── admin.css             # Estilos del panel de administración
    └── js/
        └── modal-ads.js          # Lógica del modal (frontend)
```

## Licencia

MIT
