# Diagrama: Resumidor por IA

```mermaid
flowchart TD
    Usuario[Usuario]
    Navegador[Navegador]
    Servidor[Servidor]
    IA[Motor de IA]
    Documento[Documento cargado]
    Resumen[Resumen generado]

    Usuario -->|Solicita resumen| Navegador
    Navegador -->|Envía documento| Servidor
    Servidor -->|Pasa texto a IA| IA
    IA -->|Genera resumen| Resumen
    Resumen -->|Devuelve resultado| Servidor
    Servidor -->|Muestra resumen| Navegador
    Navegador -->|Presenta resumen| Usuario
```
