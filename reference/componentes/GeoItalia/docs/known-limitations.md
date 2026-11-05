# Limitaciones conocidas

- Fallback agresivo: el cambio automático de endpoint a `/api/geo` se mantiene; solo se ajusta si hay resultados vacíos, puede ocultar errores de conectividad.
- Errores silenciosos: fallos de red en `/resolve` o `/geo/*` no muestran alertas al usuario (solo silencian en consola en dev).
- Detalles visuales menores: el enmascarado del placeholder en modo Estero depende de Select2; pueden aparecer pequeñas desalineaciones sin impacto funcional.
- No se tocan ahora: cualquier cambio en contratos, estilos o lógica queda fuera de alcance salvo fix crítico.
