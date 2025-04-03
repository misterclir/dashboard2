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
    let categoryOptions = '<option value="">Selecione Categoria</option>';
    categories.forEach(cat => {
        categoryOptions += `<option value="${cat}">Categoria ${cat}</option>`;
    });
    itemDiv.innerHTML = `
        <div class="form-group">
            <div>
                <label>Categoria:</label>
                <select name="sections[${sectionIndex}][items][${itemCount}][cat]" onchange="updateItemOptions(this)" required>
                    ${categoryOptions}
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
                    <option value="901">Exc 901</option>
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
    const itemSelect = select.parentElement.nextElementSibling.querySelector('select');
    itemSelect.innerHTML = '<option value="">Selecione Item</option>';
    if (cat && itemNames[cat]) {
        Object.keys(itemNames[cat]).forEach(index => {
            const option = document.createElement('option');
            option.value = index;
            option.text = itemNames[cat][index];
            itemSelect.appendChild(option);
        });
    }
}

function updateFileList() {
    const files = document.getElementById('xmlFiles');
    if (!files) return;
    const fileList = document.getElementById('fileList');
    fileList.innerHTML = '';
    if (files.files.length > 0) {
        fileList.innerHTML = '<strong>Arquivos selecionados:</strong><br>' + 
            Array.from(files.files).map(file => file.name).join('<br>');
    }
}

function showProgress() {
    const form = document.getElementById('uploadForm');
    const progressContainer = document.getElementById('progressContainer');
    const progressBar = document.getElementById('progressBar');

    if (form && progressContainer && progressBar) {
        progressContainer.style.display = 'block';
        let width = 0;
        const interval = setInterval(() => {
            if (width >= 100) {
                clearInterval(interval);
            } else {
                width += 10;
                progressBar.style.width = width + '%';
            }
        }, 200);
    }
}