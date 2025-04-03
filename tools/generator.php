<?php
require_once __DIR__ . '/../functions/item_loader.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate'])) {
    $emulator = $_POST['emulator'] ?? '';
    $bagName = $_POST['bag_name'] ?? 'Unnamed Bag';
    $itemRate = $_POST['item_rate'] ?? 10000;
    $moneyDrop = $_POST['money_drop'] ?? 0;
    $sections = $_POST['sections'] ?? [];

    if ($emulator === 'IGCN') {
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><ItemBag></ItemBag>');
        $xml->addAttribute('Name', htmlspecialchars($bagName));

        $bagConfig = $xml->addChild('BagConfig');
        $bagConfig->addAttribute('ItemRate', (int)$itemRate);

        $dropSection = $xml->addChild('DropSection');
        $dropAllow = $dropSection->addChild('DropAllow');
        $dropAllow->addAttribute('DW', '1');
        $dropAllow->addAttribute('DK', '1');
        $dropAllow->addAttribute('ELF', '1');
        $dropAllow->addAttribute('MG', '1');
        $dropAllow->addAttribute('DL', '1');
        $dropAllow->addAttribute('SU', '1');
        $dropAllow->addAttribute('RF', '1');
        $dropAllow->addAttribute('PlayerMinLevel', '1');
        $dropAllow->addAttribute('PlayerMaxLevel', '400');

        foreach ($sections as $sectionIndex => $section) {
            $drop = $dropAllow->addChild('Drop');
            $drop->addAttribute('Rate', (int)$section['rate']);

            if (isset($section['items']) && is_array($section['items'])) {
                foreach ($section['items'] as $item) {
                    $itemNode = $drop->addChild('Item');
                    $itemNode->addAttribute('Cat', (int)$item['cat']);
                    $itemNode->addAttribute('Index', (int)$item['index']);
                    $itemNode->addAttribute('ItemMinLevel', (int)$item['min_level']);
                    $itemNode->addAttribute('ItemMaxLevel', (int)$item['max_level']);
                    $itemNode->addAttribute('Skill', (int)$item['skill']);
                    $itemNode->addAttribute('Luck', (int)$item['luck']);
                    $itemNode->addAttribute('Option', (int)$item['option']);
                    $itemNode->addAttribute('Exc', htmlspecialchars($item['exc']));
                }
            }
        }

        if ($moneyDrop > 0) {
            $ruud = $xml->addChild('Ruud');
            $ruud->addAttribute('GainRate', '1');
            $ruud->addAttribute('MaxValue', (int)$moneyDrop);
        }

        $xmlString = $xml->asXML();
        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $bagName) . '_IGCN.xml';

        ob_start();
        header('Content-Type: application/xml');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($xmlString));
        echo $xmlString;
        ob_end_flush();
        exit;
    } else {
        echo "Por enquanto, apenas o emulador IGCN está implementado. Selecione 'IGCN'.";
    }
}
?>