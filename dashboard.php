<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

function loadItemNames($file) {
    $names = [];
    if (file_exists($file)) {
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (preg_match('/^\s*(\d+)\s+(\d+)\s+.+?"([^"]+)"/', $line, $matches)) {
                $cat = (int)$matches[1];
                $index = (int)$matches[2];
                $name = trim($matches[3]);
                $names[$cat][$index] = $name;
            }
        }
    }
    return $names;
}

// Lógica do Conversor (mantida do código anterior)
ob_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['xmlFiles']) && isset($_GET['tool']) && $_GET['tool'] === 'converter') {
    // Código do conversor aqui...
}

// Lógica do Gerador de Item Bags
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate']) && isset($_GET['tool']) && $_GET['tool'] === 'item_bag_generator') {
    $itemFile = __DIR__ . '/Item.txt';
    $itemNames = loadItemNames($itemFile);

    $xml = new DOMDocument('1.0', 'utf-8');
    $xml->formatOutput = true;

    $itemBag = $xml->createElement('ItemBag');
    $xml->appendChild($itemBag);

    $bagConfig = $xml->createElement('BagConfig');
    $bagConfig->setAttribute('Name', $_POST['bag_name'] ?? 'Custom_Bag');
    $bagConfig->setAttribute('ItemRate', $_POST['item_rate'] ?? '10000');
    $bagConfig->setAttribute('SetItemRate', '0');
    $bagConfig->setAttribute('SetItemCount', '1');
    $bagConfig->setAttribute('MasterySetItemInclude', '0');
    $bagConfig->setAttribute('MoneyDrop', $_POST['money_drop'] ?? '0');
    $bagConfig->setAttribute('IsPentagramForBeginnersDrop', '0');
    $bagConfig->setAttribute('PartyDropRate', '0');
    $bagConfig->setAttribute('PartyOneDropOnly', '0');
    $bagConfig->setAttribute('PartyShareType', '0');
    $bagConfig->setAttribute('BagUseEffect', '-1');
    $bagConfig->setAttribute('BagUseRate', '10000');
    $itemBag->appendChild($bagConfig);

    $sections = $_POST['sections'] ?? [];
    foreach ($sections as $index => $sectionData) {
        $dropSection = $xml->createElement('DropSection');
        $dropSection->setAttribute('UseMode', '-1');
        $dropSection->setAttribute('DisplayName', "Section " . ($index + 1));
        $itemBag->appendChild($dropSection);

        $dropAllow = $xml->createElement('DropAllow');
        $dropAllow->setAttribute('DW', '1');
        $dropAllow->setAttribute('DK', '1');
        $dropAllow->setAttribute('ELF', '1');
        $dropAllow->setAttribute('MG', '1');
        $dropAllow->setAttribute('DL', '1');
        $dropAllow->setAttribute('SU', '1');
        $dropAllow->setAttribute('RF', '1');
        $dropAllow->setAttribute('GL', '1');
        $dropAllow->setAttribute('RW', '1');
        $dropAllow->setAttribute('SLA', '1');
        $dropAllow->setAttribute('GC', '1');
        $dropAllow->setAttribute('LW', '1');
        $dropAllow->setAttribute('LM', '1');
        $dropAllow->setAttribute('IK', '1');
        $dropAllow->setAttribute('AC', '1');
        $dropAllow->setAttribute('PlayerMinLevel', '1');
        $dropAllow->setAttribute('PlayerMaxLevel', 'MAX');
        $dropAllow->setAttribute('PlayerMinReset', '0');
        $dropAllow->setAttribute('PlayerMaxReset', 'MAX');
        $dropAllow->setAttribute('MapNumber', '-1');
        $dropSection->appendChild($dropAllow);

        $drop = $xml->createElement('Drop');
        $drop->setAttribute('Rate', $sectionData['rate'] ?? '10000');
        $drop->setAttribute('Type', '0');
        $drop->setAttribute('Count', '1');
        $dropAllow->appendChild($drop);

        $items = $sectionData['items'] ?? [];
        foreach ($items as $itemData) {
            $item = $xml->createElement('Item');
            $item->setAttribute('Cat', $itemData['cat']);
            $item->setAttribute('Index', $itemData['index']);
            $item->setAttribute('ItemMinLevel', $itemData['min_level'] ?? '0');
            $item->setAttribute('ItemMaxLevel', $itemData['max_level'] ?? '0');
            $item->setAttribute('Skill', $itemData['skill'] ?? '0');
            $item->setAttribute('Luck', $itemData['luck'] ?? '0');
            $item->setAttribute('Option', $itemData['option'] ?? '0');
            $item->setAttribute('Exc', $itemData['exc'] ?? '-1');
            $item->setAttribute('SetItem', '0');
            $item->setAttribute('SocketCount', '0');
            $item->setAttribute('ElementalItem', '0');
            $drop->appendChild($item);
        }
    }

    $xmlContent = $xml->saveXML();
    header('Content-Type: application/xml');
    header('Content-Disposition: attachment; filename="' . ($_POST['bag_name'] ?? 'Custom_Bag') . '.xml"');
    echo $xmlContent;
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background-color: #f0f0f0; }
        .sidebar { width: 200px; background: #333; color: white; height: 100vh; position: fixed; padding-top: 20px; }
        .sidebar a { display: block; padding: 15px; color: white; text-decoration: none; }
        .sidebar a:hover { background: #555; }
        .content { margin-left: 200px; padding: 20px; min-height: 100vh; display: flex; justify-content: center; align-items: center; }
        .container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); max-width: 800px; width: 100%; }
        h1 { margin-bottom: 20px; color: #333; text-align: center; }
        .form-group { display: flex; flex-wrap: wrap; justify-content: center; gap: 20px; margin-bottom: 20px; }
        .form-group div { display: flex; flex-direction: column; align-items: center; }
        label { margin-bottom: 5px; font-weight: bold; }
        input, select { padding: 8px; width: 150px; border: 1px solid #ddd; border-radius: 4px; }
        button { padding: 10px 20px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; margin: 5px; }
        button:hover { background-color: #45a049; }
        .section { border: 1px solid #ddd; padding: 10px; margin: 10px 0; border-radius: 4px; }
        .section .form-group { justify-content: flex-start; }
        .item { border: 1px solid #ddd; padding: 10px; margin: 10px 0; border-radius: 4px; }
        .item .form-group { justify-content: flex-start; gap: 10px; }
        .remove-btn { background-color: #ff4444; }
        .remove-btn:hover { background-color: #cc0000; }
        .button-group { text-align: center; }
    </style>
    <script>
        function addSection() {
            const sectionsDiv = document.getElementById('sections');
            const sectionCount = sectionsDiv.children.length;
            const sectionDiv = document.createElement('div');
            sectionDiv.className = 'section';
            sectionDiv.innerHTML = `
                <h3>Seção ${sectionCount + 1}</h3>
                <div class="form-group">
                    <div>
                        <label>Taxa de Drop da Seção:</label>
                        <input type="number" name="sections[${sectionCount}][rate]" placeholder="Taxa de Drop" value="10000" required>
                    </div>
                </div>
                <div class="items" id="items-${sectionCount}"></div>
                <div class="button-group">
                    <button type="button" onclick="addItem(${sectionCount})">Adicionar Item</button>
                    <button type="button" class="remove-btn" onclick="this.parentElement.parentElement.remove()">Remover Seção</button>
                </div>
            `;
            sectionsDiv.appendChild(sectionDiv);
        }

        function addItem(sectionIndex) {
            const itemsDiv = document.getElementById(`items-${sectionIndex}`);
            const itemCount = itemsDiv.children.length;
            const itemDiv = document.createElement('div');
            itemDiv.className = 'item';
            itemDiv.innerHTML = `
                <div class="form-group">
                    <div>
                        <label>Categoria:</label>
                        <select name="sections[${sectionIndex}][items][${itemCount}][cat]" onchange="updateItemOptions(this)" required>
                            <option value="">Selecione Categoria</option>
                            <?php
                            $itemFile = __DIR__ . '/Item.txt';
                            $itemNames = loadItemNames($itemFile);
                            foreach ($itemNames as $cat => $items) {
                                echo "<option value='$cat'>Categoria $cat</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div>
                        <label>Item:</label>
                        <select name="sections[${sectionIndex}][items][${itemCount}][index]" required>
                            <option value="">Selecione Item</option>
                        </select>
                    </div>
                    <div>
                        <label>Nível Mínimo:</label>
                        <input type="number" name="sections[${sectionIndex}][items][${itemCount}][min_level]" placeholder="Nível Mín." value="0">
                    </div>
                    <div>
                        <label>Nível Máximo:</label>
                        <input type="number" name="sections[${sectionIndex}][items][${itemCount}][max_level]" placeholder="Nível Máx." value="0">
                    </div>
                    <div>
                        <label>Skill:</label>
                        <select name="sections[${sectionIndex}][items][${itemCount}][skill]">
                            <option value="0">Sem Skill</option>
                            <option value="1">Com Skill</option>
                        </select>
                    </div>
                    <div>
                        <label>Luck:</label>
                        <select name="sections[${sectionIndex}][items][${itemCount}][luck]">
                            <option value="0">Sem Luck</option>
                            <option value="1">Com Luck</option>
                        </select>
                    </div>
                    <div>
                        <label>Opção:</label>
                        <input type="number" name="sections[${sectionIndex}][items][${itemCount}][option]" placeholder="Opção" value="0">
                    </div>
                    <div>
                        <label>Exc:</label>
                        <select name="sections[${sectionIndex}][items][${itemCount}][exc]">
                            <option value="-1">Exc -1</option>
                            <option value="-2">Exc -2</option>
                        </select>
                    </div>
                    <div>
                        <button type="button" class="remove-btn" onclick="this.parentElement.parentElement.parentElement.remove()">Remover Item</button>
                    </div>
                </div>
            `;
            itemsDiv.appendChild(itemDiv);
        }

        function updateItemOptions(select) {
            const cat = select.value;
            const itemSelect = select.nextElementSibling;
            itemSelect.innerHTML = '<option value="">Selecione Item</option>';
            const items = <?php echo json_encode($itemNames); ?>;
            if (items[cat]) {
                for (let index in items[cat]) {
                    const option = document.createElement('option');
                    option.value = index;
                    option.text = items[cat][index];
                    itemSelect.appendChild(option);
                }
            }
        }
    </script>
</head>
<body>
    <div class="sidebar">
        <h3>Painel</h3>
        <a href="?tool=converter">Conversor XML-TXT</a>
        <a href="?tool=item_bag_generator">Gerador de Item Bags</a>
        <a href="logout.php">Sair</a>
    </div>
    <div class="content">
        <?php
        if (isset($_GET['tool']) && $_GET['tool'] === 'converter') {
            ?>
            <div class="container">
                <h1>XML to TXT Converter</h1>
                <form method="post" enctype="multipart/form-data">
                    <input type="file" name="xmlFiles[]" multiple accept=".xml" required>
                    <button type="submit">Convert</button>
                </form>
            </div>
            <?php
        } elseif (isset($_GET['tool']) && $_GET['tool'] === 'item_bag_generator') {
            ?>
            <div class="container">
                <h1>Gerador de Item Bags</h1>
                <form method="post">
                    <div class="form-group">
                        <div>
                            <label>Nome da Bag:</label>
                            <input type="text" name="bag_name" placeholder="Nome do Item Bag" required>
                        </div>
                        <div>
                            <label>DropRate da Bag:</label>
                            <input type="number" name="item_rate" placeholder="Taxa de Item" value="10000" required>
                        </div>
                        <div>
                            <label>Drop de Dinheiro:</label>
                            <input type="number" name="money_drop" placeholder="Drop de Dinheiro" value="0">
                        </div>
                    </div>
                    <div id="sections"></div>
                    <div class="button-group">
                        <button type="button" onclick="addSection()">Adicionar Seção</button>
                        <button type="submit" name="generate">Gerar XML</button>
                    </div>
                </form>
            </div>
            <?php
        } else {
            ?>
            <div class="container">
                <h1>Bem-vindo ao Painel!</h1>
            </div>
            <?php
        }
        ?>
    </div>
</body>
</html>