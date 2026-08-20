<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔎 Recherche multi-critères - OpenGestiCourrier</title>
    <link rel="stylesheet" href="css/style_general.css">
    <link rel="stylesheet" href="css/arrive.css">
    <link rel="stylesheet" href="css/recherche.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bibliothèques Export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <!-- Inclure jQuery et jQuery UI -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css"/>
    <script>
    const urllogiciel = "<?php echo $urllogiciel; ?>";
    const repertoire = "<?php echo $repertoire; ?>";
    const chemin = "<?php echo $chemin; ?>";
    
    document.addEventListener("DOMContentLoaded", function() {
        fetch('get_categories.php')
        .then(response => response.json())
        .then(categories => {
            const categorieDiv = document.getElementById('categorieDiv');
            categorieDiv.className = 'categories-chips-grid';
            categories.forEach(categorie => {
                const label = document.createElement('label');
                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.name = 'categorie[]';
                checkbox.value = categorie;
                label.appendChild(checkbox);
                label.appendChild(document.createTextNode(` ${categorie}`));
                categorieDiv.appendChild(label);
            });
        });
    });
    </script>
</head>
<body>
    <?php include 'partials/header.html'; ?>
    
    <div class="main-container">
        <!-- Barre d'action et Titre -->
        <div class="page-action-bar">
            <div class="page-title-badge" style="color: #2563eb;">
                <i class="fas fa-search"></i> Recherche multi-critères des courriers
            </div>
            <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                <!-- Boutons Export - visibles uniquement après une recherche -->
                <div id="export-buttons" style="display: none; display: flex; gap: 8px; flex-wrap: wrap; visibility: hidden;">
                    <button type="button" onclick="exportPDF()" class="btn-action-search" style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); font-size: 0.88rem; padding: 9px 16px;" title="Exporter en PDF">
                        <i class="fas fa-file-pdf"></i> PDF
                    </button>
                    <button type="button" onclick="exportExcel()" class="btn-action-search" style="background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); font-size: 0.88rem; padding: 9px 16px;" title="Exporter en Excel">
                        <i class="fas fa-file-excel"></i> Excel
                    </button>
                    <button type="button" onclick="exportWord()" class="btn-action-search" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); font-size: 0.88rem; padding: 9px 16px;" title="Exporter en Word">
                        <i class="fas fa-file-word"></i> Word
                    </button>
                    <span id="export-count" style="display: flex; align-items: center; font-size: 0.85rem; font-weight: 700; color: #475569; background: #f1f5f9; padding: 6px 12px; border-radius: 8px; border: 1px solid #cbd5e1;"></span>
                </div>
                <div style="width: 1px; height: 32px; background: #e2e8f0;"></div>
                <button type="button" onclick="document.getElementById('search-form').reset()" class="btn-action-search" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; box-shadow: none;">
                    <i class="fas fa-undo"></i> Réinitialiser
                </button>
                <button type="submit" form="search-form" class="btn-action-search" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);">
                    <i class="fas fa-search"></i> Lancer la recherche
                </button>
            </div>
        </div>

        <!-- Panneau de recherche compact 3 colonnes -->
        <div class="form-container" id="form-recherche" style="margin-bottom: 24px;">
            <form id="search-form">
                <div class="form-section-card" style="border-top: 4px solid #2563eb; padding: 22px 26px;">
                    <div class="form-section-header" style="margin-bottom: 16px;">
                        <h2 style="color: #2563eb;"><i class="fas fa-sliders-h"></i> Critères de filtrage rapide</h2>
                    </div>

                    <div class="search-grid-3">
                        <div class="form-group">
                            <label for="flux">📌 Flux de courrier :</label>
                            <select id="flux" name="flux">
                                <option value="TOUS">Tous (Arrivé & Départ)</option>
                                <option value="ARRIVE">📥 Courriers entrants (Arrivé)</option>
                                <option value="DEPART">📤 Courriers sortants (Départ)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="num_ordre">📌 Numéro d'ordre :</label>
                            <input type="text" id="num_ordre" name="num_ordre" placeholder="Ex: 12">
                        </div>

                        <div class="form-group">
                            <label for="annee">📌 Année :</label>
                            <input type="number" id="annee" name="annee" placeholder="Ex: 2024" min="2000" max="2100">
                        </div>

                        <div class="form-group">
                            <label for="date_debut">📌 Date de début :</label>
                            <input type="date" id="date_debut" name="date_debut">
                        </div>

                        <div class="form-group">
                            <label for="date_fin">📌 Date de fin :</label>
                            <input type="date" id="date_fin" name="date_fin">
                        </div>

                        <div class="form-group">
                            <label for="type_courrier">📌 Format du courrier :</label>
                            <select id="type_courrier" name="type_courrier">
                                <option value="">Tous les formats</option>
                                <option value="papier">📄 Papier</option>
                                <option value="email">✉️ E-mail</option>
                                <option value="demat">💻 Dématérialisé</option>
                            </select>
                        </div>

                        <div class="form-group search-grid-span2">
                            <label for="expediteur">📌 Contact (Expéditeur / Destinataire) :</label>
                            <input type="text" id="expediteur" name="expediteur" placeholder="Tapez le nom d'un contact...">
                        </div>

                        <div class="form-group">
                            <label for="sujet">📌 Objet / Sujet du courrier :</label>
                            <input type="text" id="sujet" name="sujet" placeholder="Mots-clés dans le sujet...">
                        </div>
                    </div>

                    <!-- Section Catégories sous forme de puces compactes -->
                    <div style="margin-top: 18px; border-top: 1.5px solid #f1f5f9; padding-top: 14px;">
                        <label style="font-size: 0.92rem; font-weight: 600; color: #334155; margin-bottom: 8px; display: block;">
                            🏷️ Filtrer par catégories :
                        </label>
                        <div id="categorieDiv"></div>
                    </div>

                    <!-- Boutons d'action rapides -->
                    <div style="margin-top: 20px; display: flex; justify-content: flex-end; gap: 12px;">
                        <button type="button" onclick="document.getElementById('search-form').reset()" class="btn-action-search" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; box-shadow: none;">
                            <i class="fas fa-undo"></i> Réinitialiser
                        </button>
                        <button type="submit" class="btn-action-search" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);">
                            <i class="fas fa-search"></i> Lancer la recherche
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Section Résultats de la recherche -->
        <div class="table-header-bar" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); margin-top: 20px;">
            <h3><i class="fas fa-table"></i> Résultats de la recherche</h3>
            <div id="export-buttons-table" style="display: none; gap: 8px;">
                <button type="button" onclick="exportPDF()" style="background: rgba(255,255,255,0.2); color: #fff; border: 1.5px solid rgba(255,255,255,0.5); padding: 6px 14px; border-radius: 8px; font-weight: 700; font-size: 0.83rem; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all 0.2s ease;" onmouseover="this.style.background='rgba(255,255,255,0.35)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                    <i class="fas fa-file-pdf"></i> PDF
                </button>
                <button type="button" onclick="exportExcel()" style="background: rgba(255,255,255,0.2); color: #fff; border: 1.5px solid rgba(255,255,255,0.5); padding: 6px 14px; border-radius: 8px; font-weight: 700; font-size: 0.83rem; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all 0.2s ease;" onmouseover="this.style.background='rgba(255,255,255,0.35)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                    <i class="fas fa-file-excel"></i> Excel
                </button>
                <button type="button" onclick="exportWord()" style="background: rgba(255,255,255,0.2); color: #fff; border: 1.5px solid rgba(255,255,255,0.5); padding: 6px 14px; border-radius: 8px; font-weight: 700; font-size: 0.83rem; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all 0.2s ease;" onmouseover="this.style.background='rgba(255,255,255,0.35)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                    <i class="fas fa-file-word"></i> Word
                </button>
            </div>
        </div>
        <div class="table-container2" style="margin-top: 0; border-radius: 0 0 12px 12px; padding: 0;">
            <div id="result-container" style="padding: 15px;">
                <p style="text-align: center; color: #64748b; font-style: italic; padding: 20px 0;">
                    Veuillez cliquer sur "Lancer la recherche" pour afficher les courriers correspondants.
                </p>
            </div>
        </div>
    </div>

<script>
// Variable globale pour stocker les résultats de recherche
let searchResultsData = [];
let searchResultsColumns = [
    { header: 'Flux', key: 'flux' },
    { header: 'N° Ordre', key: 'num_ordre' },
    { header: 'Année', key: 'annee' },
    { header: 'Traité par', key: 'traite_par' },
    { header: 'Date', key: 'date' },
    { header: 'Format', key: 'type_courrier' },
    { header: 'Contact', key: 'expediteur' },
    { header: 'Sujet', key: 'sujet_courrier' },
    { header: 'Observations', key: 'num_recommande' },
    { header: 'Catégorie', key: 'categorie_courrier' },
    { header: 'Docs joints', key: 'nb_documents' }
];


function showExportButtons(count) {
    const btns = document.getElementById('export-buttons');
    const btnsTable = document.getElementById('export-buttons-table');
    const counter = document.getElementById('export-count');
    if (btns) {
        btns.style.visibility = 'visible';
        btns.style.display = 'flex';
    }
    if (btnsTable) btnsTable.style.display = 'flex';
    if (counter) counter.textContent = count + ' résultat' + (count > 1 ? 's' : '');
}

function hideExportButtons() {
    const btns = document.getElementById('export-buttons');
    const btnsTable = document.getElementById('export-buttons-table');
    if (btns) btns.style.visibility = 'hidden';
    if (btnsTable) btnsTable.style.display = 'none';
}

// ==============================
// EXPORT PDF (jsPDF + AutoTable)
// ==============================
function exportPDF() {
    if (!searchResultsData || searchResultsData.length === 0) {
        alert('Aucun résultat à exporter.');
        return;
    }
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });

    const now = new Date();
    const dateStr = now.toLocaleDateString('fr-FR') + ' ' + now.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });

    // En-tête
    doc.setFontSize(16);
    doc.setTextColor(37, 99, 235);
    doc.setFont('helvetica', 'bold');
    doc.text('OpenGestiCourrier - Résultats de recherche', 14, 16);

    doc.setFontSize(9);
    doc.setTextColor(100, 116, 139);
    doc.setFont('helvetica', 'normal');
    doc.text('Exporté le : ' + dateStr + '  |  ' + searchResultsData.length + ' courrier(s)', 14, 22);

    doc.setDrawColor(37, 99, 235);
    doc.setLineWidth(0.5);
    doc.line(14, 25, 283, 25);

    const columns = searchResultsColumns.map(c => ({ header: c.header, dataKey: c.key }));
    const rows = searchResultsData.map(item => {
        const row = {};
        searchResultsColumns.forEach(c => {
            row[c.key] = item[c.key] || '-';
        });
        return row;
    });

    doc.autoTable({
        columns: columns,
        body: rows,
        startY: 28,
        styles: { fontSize: 8, cellPadding: 3, overflow: 'linebreak' },
        headStyles: { fillColor: [37, 99, 235], textColor: 255, fontStyle: 'bold', fontSize: 8 },
        alternateRowStyles: { fillColor: [248, 250, 252] },
        rowPageBreak: 'auto',
        margin: { top: 28, left: 14, right: 14 },
        didDrawPage: function(data) {
            // Numérotation des pages
            doc.setFontSize(8);
            doc.setTextColor(150);
            doc.text('Page ' + doc.internal.getNumberOfPages(), doc.internal.pageSize.width - 20, doc.internal.pageSize.height - 8);
        }
    });

    const filename = 'recherche_courriers_' + now.toISOString().slice(0, 10) + '.pdf';
    doc.save(filename);
}

// ================================
// EXPORT EXCEL (SheetJS)
// ================================
function exportExcel() {
    if (!searchResultsData || searchResultsData.length === 0) {
        alert('Aucun résultat à exporter.');
        return;
    }
    const now = new Date();
    const dateStr = now.toLocaleDateString('fr-FR');

    const headers = searchResultsColumns.map(c => c.header);
    const rows = searchResultsData.map(item => searchResultsColumns.map(c => item[c.key] || ''));

    // Ligne de titre et date
    const wsData = [
        ['OpenGestiCourrier - Résultats de recherche'],
        ['Exporté le : ' + dateStr + '  |  ' + searchResultsData.length + ' courrier(s)'],
        [],
        headers,
        ...rows
    ];

    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.aoa_to_sheet(wsData);

    // Largeurs colonnes
    ws['!cols'] = searchResultsColumns.map((c, i) => {
        if (c.key === 'sujet_courrier') return { wch: 45 };
        if (c.key === 'nb_documents') return { wch: 12 };
        return { wch: 20 };
    });


    // Style titre (merge)
    ws['!merges'] = [{ s: { r: 0, c: 0 }, e: { r: 0, c: headers.length - 1 } }];

    XLSX.utils.book_append_sheet(wb, ws, 'Résultats');
    const filename = 'recherche_courriers_' + now.toISOString().slice(0, 10) + '.xlsx';
    XLSX.writeFile(wb, filename);
}

// ================================
// EXPORT WORD (HTML → DOCX via PHP)
// ================================
function exportWord() {
    if (!searchResultsData || searchResultsData.length === 0) {
        alert('Aucun résultat à exporter.');
        return;
    }
    const now = new Date();
    const dateStr = now.toLocaleDateString('fr-FR') + ' ' + now.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });

    let html = `<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">
<head><meta charset="UTF-8">
<style>
body { font-family: Calibri, Arial, sans-serif; font-size: 11pt; }
h1 { color: #1d4ed8; font-size: 16pt; margin-bottom: 4px; }
p.sub { color: #64748b; font-size: 9pt; margin-top: 0; }
table { border-collapse: collapse; width: 100%; margin-top: 16px; }
th { background-color: #2563eb; color: white; padding: 6px 8px; font-size: 10pt; border: 1px solid #1d4ed8; text-align: left; }
td { padding: 5px 8px; font-size: 9.5pt; border: 1px solid #cbd5e1; }
tr:nth-child(even) td { background-color: #f8fafc; }
</style></head><body>
<h1>OpenGestiCourrier - Résultats de recherche</h1>
<p class="sub">Exporté le : ${dateStr} &nbsp;|&nbsp; ${searchResultsData.length} courrier(s)</p>
<table><thead><tr>`;

    searchResultsColumns.forEach(c => { html += `<th>${c.header}</th>`; });
    html += '</tr></thead><tbody>';

    searchResultsData.forEach(item => {
        html += '<tr>';
        searchResultsColumns.forEach(c => {
            html += `<td>${item[c.key] || '-'}</td>`;
        });
        html += '</tr>';
    });

    html += '</tbody></table></body></html>';

    // Télécharger directement comme .doc (HTML-in-Word)
    const filename = 'recherche_courriers_' + now.toISOString().slice(0, 10) + '.doc';
    const blob = new Blob([html], { type: 'application/msword' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}

document.getElementById('search-form').addEventListener('submit', function(event) {
    event.preventDefault();

    const formData = new FormData(this);

    fetch('search_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(responseText => {
        try {
            const data = JSON.parse(responseText);
            const resultContainer = document.getElementById('result-container');
            resultContainer.innerHTML = '';
            searchResultsData = [];

            if (data.error) {
                resultContainer.innerHTML = `<p style="color:#ef4444; font-weight:bold;">Erreur: ${data.error}</p>`;
                hideExportButtons();
            } else if (data.length === 0) {
                resultContainer.innerHTML = '<p style="text-align:center; color:#64748b; padding:20px 0;">Aucun résultat trouvé pour ces critères.</p>';
                hideExportButtons();
            } else {
                searchResultsData = data;
                showExportButtons(data.length);
                const table = document.createElement('table');
                table.style.width = '100%';
                table.style.fontSize = '13px';

                const headerRow = document.createElement('tr');
                const headers = [
                    { text: 'Flux', key: 'flux' },
                    { text: 'N° Ordre', key: 'num_ordre' },
                    { text: 'Année', key: 'annee' },
                    { text: 'Traité par', key: 'traite_par' },
                    { text: 'Date', key: 'date' },
                    { text: 'Format', key: 'type_courrier' },
                    { text: 'Contact', key: 'expediteur' },
                    { text: 'Sujet', key: 'sujet_courrier' },
                    { text: 'Observations', key: 'num_recommande' },
                    { text: 'Catégorie', key: 'categorie_courrier' },
                    { text: 'Docs joints', key: 'nb_documents' },
                    { text: 'Documents', key: 'document_paths' },
                    { text: 'Courrier associé', key: 'courrier_associe' }
                ];

                headers.forEach(header => {
                    const th = document.createElement('th');
                    th.innerHTML = `${header.text} <span class="sort-symbol">⇅</span>`;
                    if (header.key) {
                        th.dataset.key = header.key;
                        th.style.cursor = 'pointer';
                        th.addEventListener('click', () => sortTable(header.key));
                    }
                    headerRow.appendChild(th);
                });
                table.appendChild(headerRow);

                let sortedData = [...data];
                let sortOrder = {};

                function sortTable(key) {
                    sortOrder[key] = !sortOrder[key];
                    const ascending = sortOrder[key];

                    headerRow.querySelectorAll('th').forEach(th => {
                        th.classList.remove('ascending', 'descending');
                        const sym = th.querySelector('.sort-symbol');
                        if (sym) sym.textContent = '⇅';
                    });

                    const sortedHeader = headerRow.querySelector(`th[data-key="${key}"]`);
                    if (sortedHeader) {
                        const sym = sortedHeader.querySelector('.sort-symbol');
                        if (ascending) {
                            sortedHeader.classList.add('ascending');
                            if (sym) sym.textContent = '⬆';
                        } else {
                            sortedHeader.classList.add('descending');
                            if (sym) sym.textContent = '⬇';
                        }
                    }

                    sortedData.sort((a, b) => {
                        if (a[key] < b[key]) return ascending ? -1 : 1;
                        if (a[key] > b[key]) return ascending ? 1 : -1;
                        return 0;
                    });

                    renderTable(sortedData);
                }

                function renderTable(data) {
                    const rows = table.querySelectorAll('tr:not(:first-child)');
                    rows.forEach(row => row.remove());

                    data.forEach(item => {
                        const row = document.createElement('tr');
                        const columnsOrder = ['flux', 'num_ordre', 'annee', 'traite_par', 'date', 'type_courrier', 'expediteur', 'sujet_courrier', 'num_recommande', 'categorie_courrier', 'nb_documents', 'document_paths', 'courrier_associe'];
                        
                        columnsOrder.forEach(column => {
                            const td = document.createElement('td');

                            if (column === 'flux') {
                                if (item.flux === 'ARRIVE') {
                                    td.innerHTML = '<span style="background:#fef3c7; color:#b45309; padding:3px 8px; border-radius:12px; font-weight:700; font-size:0.8rem;">📥 ARRIVÉ</span>';
                                } else {
                                    td.innerHTML = '<span style="background:#d1fae5; color:#047857; padding:3px 8px; border-radius:12px; font-weight:700; font-size:0.8rem;">📤 DÉPART</span>';
                                }
                            } else if (column === 'nb_documents') {
                                const n = parseInt(item.nb_documents) || 0;
                                const badge = n > 0
                                    ? `<span style="background:#eff6ff; color:#2563eb; padding:2px 10px; border-radius:10px; font-weight:700; font-size:0.85rem;">${n} 📎</span>`
                                    : `<span style="color:#94a3b8; font-size:0.85rem;">-</span>`;
                                td.innerHTML = badge;
                                td.style.textAlign = 'center';
                            } else if (column === 'document_paths') {
                                const documentPaths = [
                                    item.document_path,
                                    item.document_path2,
                                    item.document_path3,
                                    item.document_path4,
                                    item.document_path5
                                ];

                                
                                const usedPaths = [];
                                documentPaths.forEach((path) => {
                                    if (path && path.trim() !== '' && !usedPaths.includes(path)) {
                                        usedPaths.push(path);
                                        const fileName = path.split('/').pop();
                                        const relativePath = path.includes('uploads/') ? ('uploads/' + fileName) : path.replace(chemin, repertoire).replace(/^\//, '');
                                        const link = document.createElement('a');
                                        link.href = relativePath;
                                        link.textContent = `📄 Doc ${usedPaths.length}`;
                                        link.style.cursor = 'pointer';
                                        link.style.color = '#2563eb';
                                        link.style.fontWeight = '600';
                                        link.style.textDecoration = 'underline';
                                        link.addEventListener('click', function(e) {
                                            e.preventDefault();
                                            window.open(relativePath, '_blank', 'width=800,height=900');
                                        });
                                        td.appendChild(link);
                                        td.appendChild(document.createElement('br'));
                                    }
                                });
                                if (usedPaths.length === 0) td.textContent = '-';
                            } else if (column === 'courrier_associe') {
                                if (item.flux === 'ARRIVE' && item.courrier_depart_num_ordre) {
                                    td.textContent = `Départ N°${item.courrier_depart_num_ordre}`;
                                    td.style.color = '#059669';
                                    td.style.fontWeight = 'bold';
                                    
                                    const courrierDepartAnnee = item.courrier_depart_annee || item.annee;
                                    fetch(`get_courrier_documents.php?num_ordre=${item.courrier_depart_num_ordre}&annee=${courrierDepartAnnee}`)
                                        .then(response => response.json())
                                        .then(departDocs => {
                                            if (departDocs && departDocs.length > 0) {
                                                td.appendChild(document.createElement('br'));
                                                departDocs.forEach((doc, idx) => {
                                                    const departRelativePath = doc.replace(chemin, repertoire);
                                                    const departLink = document.createElement('a');
                                                    departLink.href = departRelativePath;
                                                    departLink.textContent = `Doc ${idx + 1}`;
                                                    departLink.style.cursor = 'pointer';
                                                    departLink.style.color = '#059669';
                                                    departLink.style.textDecoration = 'underline';
                                                    departLink.style.fontSize = '0.9em';
                                                    departLink.addEventListener('click', function(e) {
                                                        e.preventDefault();
                                                        window.open(departRelativePath, '_blank', 'width=800,height=900');
                                                    });
                                                    td.appendChild(departLink);
                                                    td.appendChild(document.createTextNode(' '));
                                                });
                                            }
                                        })
                                        .catch(error => console.error('Erreur chargement docs départ:', error));
                                } else if (item.flux === 'DEPART' && item.courrier_arrive_num_ordre) {
                                    td.textContent = `Arrivé N°${item.courrier_arrive_num_ordre}`;
                                    td.style.color = '#d97706';
                                    td.style.fontWeight = 'bold';
                                    
                                    const courrierArriveAnnee = item.courrier_arrive_annee || item.annee;
                                    fetch(`get_courrier_documents_arrive.php?num_ordre=${item.courrier_arrive_num_ordre}&annee=${courrierArriveAnnee}`)
                                        .then(response => response.json())
                                        .then(arriveDocs => {
                                            if (arriveDocs && arriveDocs.length > 0) {
                                                td.appendChild(document.createElement('br'));
                                                arriveDocs.forEach((doc, idx) => {
                                                    const arriveRelativePath = doc.replace(chemin, repertoire);
                                                    const arriveLink = document.createElement('a');
                                                    arriveLink.href = arriveRelativePath;
                                                    arriveLink.textContent = `Doc ${idx + 1}`;
                                                    arriveLink.style.cursor = 'pointer';
                                                    arriveLink.style.color = '#d97706';
                                                    arriveLink.style.textDecoration = 'underline';
                                                    arriveLink.style.fontSize = '0.9em';
                                                    arriveLink.addEventListener('click', function(e) {
                                                        e.preventDefault();
                                                        window.open(arriveRelativePath, '_blank', 'width=800,height=900');
                                                    });
                                                    td.appendChild(arriveLink);
                                                    td.appendChild(document.createTextNode(' '));
                                                });
                                            }
                                        })
                                        .catch(error => console.error('Erreur chargement docs arrivé:', error));
                                } else {
                                    td.textContent = '-';
                                }
                            } else {
                                td.textContent = item[column] || '-';
                            }

                            row.appendChild(td);
                        });

                        table.appendChild(row);
                    });
                }

                renderTable(data);
                resultContainer.appendChild(table);
            }
        } catch (error) {
            console.error('Erreur de syntaxe JSON:', error);
            document.getElementById('result-container').innerHTML = '<p style="color:#ef4444;">Une erreur est survenue lors de l\'affichage des résultats.</p>';
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        document.getElementById('result-container').innerHTML = `<p style="color:#ef4444;">Une erreur est survenue : ${error}</p>`;
    });
});

// Autocomplete pour le champ expéditeur
$.getJSON('get_expediteurs.php', function(data) {
    $("#expediteur").autocomplete({
        source: data,
        select: function(event, ui) {
            $('#expediteur').val(ui.item.label);
            return false;
        }
    });
});

// Autocomplete pour le champ sujet
$.getJSON('get_sujets.php', function(data) {
    $("#sujet").autocomplete({
        source: data,
        select: function(event, ui) {
            $('#sujet').val(ui.item.label);
            return false;
        }
    });
});
</script>

    <?php include 'partials/menu_actif.html';?>
</body>
</html>