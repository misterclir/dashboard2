<?php
function loadItemNames($itemFile) {
    $itemNames = [];
    ini_set('memory_limit', '256M');
    ini_set('max_execution_time', 60);

    $url = 'http://vegasmu.ddns.net/Item.txt';
    $content = @file_get_contents($url);
    if ($content === false && file_exists($itemFile)) {
        $content = file_get_contents($itemFile);
    }
    if ($content === false) {
        error_log("Falha ao carregar Item.txt de $itemFile ou $url");
        return [];
    }

    $lines = explode("\n", $content);
    $currentSection = null;

    foreach ($lines as $line) {
        $line = trim($line);
        if (preg_match('/^(\d+)$/', $line) && $line >= 0 && $line <= 20) {
            $currentSection = (int)$line;
            $itemNames[$currentSection] = []; // Inicializa array para a categoria
            continue;
        }
        if ($line === 'end') {
            $currentSection = null;
            continue;
        }
        if ($currentSection !== null && !preg_match('/^\/\//', $line)) {
            if (preg_match('/^(\d+)\s+\"([^\"]+)\"/', $line, $matches)) {
                $index = (int)$matches[1];
                $name = $matches[2];
                $itemNames[$currentSection][$index] = $name;
            }
        }
    }
    return $itemNames;
}
?>