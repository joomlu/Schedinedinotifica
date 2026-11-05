# QA checklist

- Italia básico: seleccionar nazione=Italia, regione, provincia, comune, CAP; verificar payload y evento.
- Resolve CAP: buscar CAP existente (ej. 00100), validar que Comune/Provincia/Regione se hidraten y que `cap_default` se aplique.
- CAP manual inexistente: ingresar CAP no presente, confirmar que se conserva el valor y no se limpian selects.
- Resolve Comune: seleccionar un Comune y verificar lista de CAPs y jerarquía completa.
- Toggle Italia ↔ Estero: cambiar ambos sentidos verificando que el payload `manualMode` cambie y se limpien/oculten los bloques correctos.
- Estero manual: ingresar textos en región/provincia/città/CAP manual y validar payload manual.
- Resolve extranjero: si `/resolve` devuelve nación no italiana, confirmar preservación de la nación y máscara visual del placeholder.
- Inicialización manual=true: renderizar componente con `manual=1` en los iniciales y comprobar que arranque en Estero con campos precargados.
