$repl = @{
    'Ã¡'='á'; 'Ã©'='é'; 'Ã­'='í'; 'Ã³'='ó'; 'Ãº'='ú'; 'Ã±'='ñ';
    'Ã‘'='Ñ'; 'Ã'='Á'; 'Ã‰'='É'; 'Ã“'='Ó'; 'Ãš'='Ú';
    'Ã¼'='ü'; 'Ã¶'='ö'; 'Â¿'='¿'; 'Â¡'='¡';
    'â'='“'; 'â'='”'; 'â'='–'; 'â'='—'; 'â¦'='…';
    'â€™'='’'; 'â€œ'='“';
    'ðŸ“š'='📚'; 'ðŸ”½'='🔽'; 'ðŸ“‹'='📋'; 'ðŸ—‘ï¸'='🗑️'; 'ðŸ‘¨â€ðŸ«'='👨‍🏫'
}

Get-ChildItem -Path (Get-Location).Path -Recurse -Include *.php,*.html,*.js | ForEach-Object {
    $text = Get-Content -Raw -LiteralPath $_.FullName
    $orig = $text
    foreach ($key in $repl.Keys) {
        $text = $text -replace [regex]::Escape($key), $repl[$key]
    }
    if ($text -ne $orig) {
        Set-Content -LiteralPath $_.FullName -Value $text -Encoding utf8
        Write-Host "Updated: $($_.FullName)"
    }
}
Write-Host 'done'