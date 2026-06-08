#============================================================================
# ARCHIVO: fix_tildes_symbols.ps1
#============================================================================
# PROPÓSITO:
#   Script PowerShell para corregir caracteres especiales mal codificados
#   en archivos PHP, HTML y JavaScript. Soluciona problemas de encoding
#   UTF-8 cuando caracteres acentuados y especiales se han dañado.
#
# FUNCIONALIDAD CLAVE:
#   - Define tabla de mapeo de caracteres dañados -> caracteres correctos
#   - Busca recursivamente en archivos *.php, *.html, *.js
#   - Reemplaza caracteres incorrectos por sus versiones correctas
#   - Guarda archivos en codificación UTF-8
#   - Muestra nombre de archivo si fue actualizado
#
# CARACTERES QUE CORRIGE:
#   - Vocales acentuadas: á, é, í, ó, ú, ñ (minúsculas y mayúsculas)
#   - Caracteres especiales: ¿, ¡
#   - Comillas curvas: ", ", –, —, …
#   - Emojis: 📚, 🔽, 📋, 🗑️, 👨‍🏫
#
# CÓMO EJECUTAR:
#   1. Abre PowerShell
#   2. Navega a la carpeta del proyecto: cd C:\xampp\htdocs\tfg\TFG
#   3. Ejecuta: .\fix_tildes_symbols.ps1
#   4. El script procesará todos los archivos y mostrará cambios
#   5. Verifica que los caracteres se hayan corregido
#
# REQUISITOS:
#   - PowerShell 5.0 o superior
#   - Permisos de lectura/escritura en los archivos
#   - Archivos en formato UTF-8 (recomendado)
#
# PRECAUCIÓN:
#   - Realiza una copia de seguridad antes de ejecutar
#   - El script modifica archivos directamente
#   - No se puede deshacer automáticamente
#
#============================================================================

# Tabla de reemplazos: caracteres mal codificados -> caracteres correctos
$repl = @{
    'Ã¡'='á'; 'Ã©'='é'; 'Ã­'='í'; 'Ã³'='ó'; 'Ãº'='ú'; 'Ã±'='ñ';
    'Ã''='Ñ'; 'Ã'='Á'; 'Ã‰'='É'; 'Ã"'='Ó'; 'Ãš'='Ú';
    'Ã¼'='ü'; 'Ã¶'='ö'; 'Â¿'='¿'; 'Â¡'='¡';
    'â'='"'; 'â'='"'; 'â'='–'; 'â'='—'; 'â¦'='…';
    'â€™'='''; 'â€œ'='"';
    'ðŸ"š'='📚'; 'ðŸ"½'='🔽'; 'ðŸ"‹'='📋'; 'ðŸ—'ï¸'='🗑️'; 'ðŸ'¨â€ðŸ«'='👨‍🏫'
}

# Procesar todos los archivos PHP, HTML y JS recursivamente
Get-ChildItem -Path (Get-Location).Path -Recurse -Include *.php,*.html,*.js | ForEach-Object {
    # Leer contenido del archivo
    $text = Get-Content -Raw -LiteralPath $_.FullName
    $orig = $text
    
    # Aplicar todos los reemplazos
    foreach ($key in $repl.Keys) {
        $text = $text -replace [regex]::Escape($key), $repl[$key]
    }
    
    # Si el contenido cambió, guardar el archivo
    if ($text -ne $orig) {
        Set-Content -LiteralPath $_.FullName -Value $text -Encoding utf8
        Write-Host "Updated: $($_.FullName)"
    }
}

Write-Host 'done'
