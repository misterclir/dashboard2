<?php
require_once __DIR__ . '/../functions/item_loader.php';

function writeItems($txt, $sectionNum, $items, $itemNames) {
    if ($items && count($items) > 0) {
        fwrite($txt, "$sectionNum\n");
        fwrite($txt, "//Index\tLevel\tGrade\tOption0\tOption1\tOption2\tOption3\tOption4\tOption5\tOption6\tDuration\tComment\n");
        foreach ($items as $item) {
            $cat = (string)$item['Cat'];
            $index = (string)$item['Index'];
            $minLevel = (string)$item['ItemMinLevel'];
            $maxLevel = (string)$item['ItemMaxLevel'];
            $level = ($minLevel === $maxLevel) ? $minLevel : "$minLevel-$maxLevel";
            $grade = '0';
            $option0 = '*';
            $option1 = (string)$item['Skill'] === '1' ? '1' : '0';
            $option2 = (string)$item['Luck'] === '1' ? '1' : '0';
            $option3 = '-1';
            $exc = (string)$item['Exc'];
            $option4 = ($exc === '-1') ? '-1' : '901';
            $option5 = '*';
            $option6 = '*';
            $duration = (string)$item['Duration'] ?: '0';
            $key = "$cat,$index";
            $comment = isset($itemNames[$key]) ? $itemNames[$key] : 'Unknown Item';
            $line = "$cat,$index\t$level\t$grade\t$option0\t$option1\t$option2\t$option3\t$option4\t$option5\t$option6\t$duration\t//$comment\n";
            fwrite($txt, $line);
        }
        fwrite($txt, "end\n\n");
        return true;
    }
    return false;
}

function xmlToTxt($inputFile, $outputFile, $itemNames) {
    $xml = simplexml_load_file($inputFile);
    if ($xml === false) {
        error_log("Falha ao carregar XML: $inputFile");
        return false;
    }

    $txt = fopen($outputFile, 'w');
    if ($txt === false) {
        error_log("Falha ao criar TXT: $outputFile");
        return false;
    }

    $bagConfig = $xml->BagConfig;
    $itemRate = (string)$bagConfig['ItemRate'];
    fwrite($txt, "0\n");
    fwrite($txt, "//Index\tDropRate\n");
    fwrite($txt, "0\t$itemRate\n");
    fwrite($txt, "end\n\n");

    $dropSections = $xml->DropSection;
    $ruud = $xml->Ruud;
    $moneyAmount = ($ruud && $ruud['GainRate'] == '1') ? (string)$ruud['MaxValue'] : '0';
    $optionValue = ($ruud && $ruud['GainRate'] == '1') ? '16' : '0';

    fwrite($txt, "1\n");
    fwrite($txt, "//Index\tSection\tSectionRate\tMoneyAmount\tOptionValue\tDW\tDK\tFE\tMG\tDL\tSU\tRF\tGL\tRW\tSL\tGC\tKM\tLM\tIK\n");
    $sectionIndex = 0;

    foreach ($dropSections as $dropSection) {
        $drops = $dropSection->DropAllow->Drop;
        $dropAllow = $dropSection->DropAllow;
        $classes = [
            (string)($dropAllow['DW'] ?? '1'), (string)($dropAllow['DK'] ?? '1'), (string)($dropAllow['ELF'] ?? '1'),
            (string)($dropAllow['MG'] ?? '1'), (string)($dropAllow['DL'] ?? '1'), (string)($dropAllow['SU'] ?? '1'),
            (string)($dropAllow['RF'] ?? '1'), (string)($dropAllow['GL'] ?? '1'), (string)($dropAllow['RW'] ?? '1'),
            (string)($dropAllow['SLA'] ?? '1'), (string)($dropAllow['GC'] ?? '1'), (string)($dropAllow['LW'] ?? '1'),
            (string)($dropAllow['LM'] ?? '1'), (string)($dropAllow['IK'] ?? '1')
        ];

        foreach ($drops as $drop) {
            $sectionNum = 2 + $sectionIndex;
            $sectionRate = (string)$drop['Rate'];
            $line = "0\t$sectionNum\t$sectionRate\t$moneyAmount\t$optionValue\t" . implode("\t", $classes) . "\n";
            fwrite($txt, $line);
            $sectionIndex++;
        }
    }
    fwrite($txt, "end\n\n");

    $sectionIndex = 0;
    foreach ($dropSections as $dropSection) {
        $drops = $dropSection->DropAllow->Drop;
        foreach ($drops as $drop) {
            $sectionNum = 2 + $sectionIndex;
            $items = $drop->Item;
            writeItems($txt, $sectionNum, $items, $itemNames);
            $sectionIndex++;
        }
    }

    fclose($txt);
    return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['xmlFiles'])) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    $itemFile = __DIR__ . '/../Item.txt';
    $itemNames = loadItemNames($itemFile);

    $files = $_FILES['xmlFiles'];
    $tempFiles = [];
    $zip = new ZipArchive();
    $zipFile = __DIR__ . '/../converted_files.zip';

    ob_start();

    if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        ob_end_clean();
        die("Erro ao criar o arquivo ZIP: $zipFile");
    }

    $success = false;
    for ($i = 0; $i < count($files['name']); $i++) {
        $inputFile = $files['tmp_name'][$i];
        if (!file_exists($inputFile)) {
            error_log("Arquivo temporário não encontrado: $inputFile");
            continue;
        }

        $originalName = pathinfo($files['name'][$i], PATHINFO_FILENAME);
        $newName = $originalName;
        if (preg_match('/^Item_\(\d+(?:,\d+)*\)_(.+)$/', $originalName, $matches)) {
            $newName = str_replace('_', ' ', $matches[1]);
        } elseif (preg_match('/^Monster_\(\d+\)_(.+)$/', $originalName, $matches)) {
            $newName = str_replace('_', ' ', $matches[1]);
        } elseif (preg_match('/^Event_(.+)$/', $originalName, $matches)) {
            $newName = str_replace('_', ' ', $matches[1]);
        } else {
            $newName = str_replace('_', ' ', $originalName);
        }

        $outputFile = __DIR__ . "/../{$newName}.txt";
        if (xmlToTxt($inputFile, $outputFile, $itemNames)) {
            if ($zip->addFile($outputFile, basename($outputFile))) {
                $tempFiles[] = $outputFile;
                $success = true;
            } else {
                error_log("Falha ao adicionar $outputFile ao ZIP");
            }
        } else {
            error_log("Falha ao converter $inputFile");
        }
    }

    $zip->close();

    if ($success) {
        ob_end_clean();
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="converted_files.zip"');
        header('Content-Length: ' . filesize($zipFile));
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Pragma: no-cache");
        readfile($zipFile);

        foreach ($tempFiles as $file) unlink($file);
        unlink($zipFile);
        exit;
    } else {
        ob_end_clean();
        die("Nenhum arquivo foi convertido. Verifique os logs.");
    }
}
?>