<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔎 Recherche multi-critères</title>
    <link rel="stylesheet" href="css/recherche.css">
    <link rel="stylesheet" href="css/style_general.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Inclure jQuery et jQuery UI -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css"/>
    <script>
    // Variables PHP pour JavaScript
    const urllogiciel = "<?php echo $urllogiciel; ?>";
    const repertoire = "<?php echo $repertoire; ?>";
    const chemin = "<?php echo $chemin; ?>";
    
    document.addEventListener("DOMContentLoaded", function() {
        fetch('get_categories.php')
        .then(response => response.json())
        .then(categories => {
            const categorieDiv = document.getElementById('categorieDiv');
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
        <div class="search-container">
            <h1>🔎 Recherche multi-critères</h1>
            <form id="search-form">
                <label for="flux">Flux</label>
                <select id="flux" name="flux">
                    <option value="TOUS">Tous</option>
                    <option value="ARRIVE">Arrivé</option>
                    <option value="DEPART">Départ</option>
                </select>

                <label for="num_ordre">Numéro d'ordre</label>
                <input type="text" style="width: 100%;padding: 10px;margin-bottom: 10px;border: 1px solid #ccc;border-radius: 4px;box-sizing: border-box;" id="num_ordre" name="num_ordre">

                <label for="annee">Année</label>
                <input type="number" style="width: 100%;padding: 10px;margin-bottom: 10px;border: 1px solid #ccc;border-radius: 4px;box-sizing: border-box;" id="annee" name="annee" placeholder="Ex: 2024" min="2000" max="2100">

                <label for="date_debut">Date de début</label>
                <input type="date" style="width: 100%;padding: 10px;margin-bottom: 10px;border: 1px solid #ccc;border-radius: 4px;box-sizing: border-box;" id="date_debut" name="date_debut">

                <label for="date_fin">Date de fin</label>
                <input type="date" style="width: 100%;padding: 10px;margin-bottom: 10px;border: 1px solid #ccc;border-radius: 4px;box-sizing: border-box;" id="date_fin" name="date_fin">

                <label for="type_courrier">Type de courrier</label>
                <select id="type_courrier" name="type_courrier">
                    <option value="">Tous</option>
                    <option value="papier">Papier</option>
                    <option value="email">E-mail</option>
                    <option value="demat">Demat</option>
                </select>

                <label for="expediteur">Contact</label>
                <input type="text" style="width: 100%;padding: 10px;margin-bottom: 10px;border: 1px solid #ccc;border-radius: 4px;box-sizing: border-box;" id="expediteur" name="expediteur">

                <label for="sujet">Sujet</label>
                <input type="text" style="width: 100%;padding: 10px;margin-bottom: 10px;border: 1px solid #ccc;border-radius: 4px;box-sizing: border-box;" id="sujet" name="sujet">

                <label for="categorie">Catégories :</label>
                <div id="categorieDiv"></div>

                <button type="submit">Rechercher</button>
            </form>
        </div>
        
        <div class="result-container" id="result-container">
            <!-- Les résultats de la recherche seront affichés ici -->
        </div>
    </div>

<script>
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

            if (data.error) {
                resultContainer.innerHTML = `<p>Erreur: ${data.error}</p>`;
            } else if (data.length === 0) {
                resultContainer.innerHTML = '<p>Aucun résultat trouvé.</p>';
            } else {
                const table = document.createElement('table');
                table.style.width = '100%';
                table.style.borderCollapse = 'collapse';

                const headerRow = document.createElement('tr');
                const headers = [
                    { text: 'Flux', key: 'flux' },
                    { text: 'Ordre', key: 'num_ordre' },
                    { text: 'Année', key: 'annee' },
                    { text: 'Traité par', key: 'traite_par' },
                    { text: 'Date', key: 'date' },
                    { text: 'Type', key: 'type_courrier' },
                    { text: 'Contact', key: 'expediteur' },
                    { text: 'Sujet', key: 'sujet_courrier' },
                    { text: 'Observations', key: 'num_recommande' },
                    { text: 'Catégorie', key: 'categorie_courrier' },
                    { text: 'Documents', key: 'document_paths' },
                    { text: 'Courrier associé', key: 'courrier_associe' }
                ];

                headers.forEach(header => {
                    const th = document.createElement('th');
                    th.innerHTML = `${header.text} <span class="sort-symbol">⇅</span>`;
                    th.style.border = '1px solid #ccc';
                    th.style.padding = '8px';
                    th.style.background = '#f0f0f0';
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
                        th.querySelector('.sort-symbol').textContent = '⇅';
                    });

                    const sortedHeader = headerRow.querySelector(`th[data-key="${key}"]`);
                    if (ascending) {
                        sortedHeader.classList.add('ascending');
                        sortedHeader.querySelector('.sort-symbol').textContent = '⬆';
                    } else {
                        sortedHeader.classList.add('descending');
                        sortedHeader.querySelector('.sort-symbol').textContent = '⬇';
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

                        const columnsOrder = ['flux', 'num_ordre', 'annee', 'traite_par', 'date', 'type_courrier', 'expediteur', 'sujet_courrier', 'num_recommande', 'categorie_courrier', 'document_paths', 'courrier_associe'];
                        
                        columnsOrder.forEach(column => {
                            const td = document.createElement('td');

                            if (column === 'document_paths') {
                                // Afficher tous les documents joints
                                const documentPaths = [
                                    item.document_path,
                                    item.document_path2,
                                    item.document_path3,
                                    item.document_path4,
                                    item.document_path5
                                ];
                                
                                // Tableau pour éviter les doublons
                                const usedPaths = [];
                                
                                documentPaths.forEach((path, index) => {
                                    if (path && path.trim() !== '' && !usedPaths.includes(path)) {
                                        usedPaths.push(path);
                                        const relativePath = path.replace(chemin, repertoire);
                                        const link = document.createElement('a');
                                        link.href = relativePath;
                                        link.textContent = `Document ${usedPaths.length}`;
                                        link.style.cursor = 'pointer';
                                        link.style.color = 'blue';
                                        link.style.textDecoration = 'underline';
                                        link.addEventListener('click', function(e) {
                                            e.preventDefault();
                                            window.open(relativePath, '_blank', 'width=794,height=900');
                                        });
                                        td.appendChild(link);
                                        td.appendChild(document.createElement('br'));
                                    }
                                });
                            } else if (column === 'courrier_associe') {
                                // Afficher le courrier associé avec ses documents
                                if (item.flux === 'ARRIVE' && item.courrier_depart_num_ordre) {
                                    td.textContent = `Départ N°${item.courrier_depart_num_ordre}`;
                                    td.style.color = 'green';
                                    td.style.fontWeight = 'bold';
                                    
                                    // Charger les documents du courrier départ associé
                                    const courrierDepartAnnee = item.courrier_depart_annee || item.annee;
                                    console.log(`Chargement docs départ - Num: ${item.courrier_depart_num_ordre}, Année: ${courrierDepartAnnee}`);
                                    
                                    fetch(`get_courrier_documents.php?num_ordre=${item.courrier_depart_num_ordre}&annee=${courrierDepartAnnee}`)
                                        .then(response => response.json())
                                        .then(departDocs => {
                                            console.log('Documents départ reçus:', departDocs);
                                            if (departDocs && departDocs.length > 0) {
                                                td.appendChild(document.createElement('br'));
                                                departDocs.forEach((doc, idx) => {
                                                    const departRelativePath = doc.replace(chemin, repertoire);
                                                    const departLink = document.createElement('a');
                                                    departLink.href = departRelativePath;
                                                    departLink.textContent = `Doc ${idx + 1}`;
                                                    departLink.style.cursor = 'pointer';
                                                    departLink.style.color = 'green';
                                                    departLink.style.textDecoration = 'underline';
                                                    departLink.style.fontSize = '0.9em';
                                                    departLink.addEventListener('click', function(e) {
                                                        e.preventDefault();
                                                        window.open(departRelativePath, '_blank', 'width=794,height=900');
                                                    });
                                                    td.appendChild(departLink);
                                                    td.appendChild(document.createTextNode(' '));
                                                });
                                            }
                                        })
                                        .catch(error => console.error('Erreur chargement docs départ:', error));
                                } else if (item.flux === 'DEPART' && item.courrier_arrive_num_ordre) {
                                    td.textContent = `Arrivé N°${item.courrier_arrive_num_ordre}`;
                                    td.style.color = 'orange';
                                    td.style.fontWeight = 'bold';
                                    
                                    // Charger les documents du courrier arrivé associé
                                    const courrierArriveAnnee = item.courrier_arrive_annee || item.annee;
                                    console.log(`Chargement docs arrivé - Num: ${item.courrier_arrive_num_ordre}, Année: ${courrierArriveAnnee}`);
                                    
                                    fetch(`get_courrier_documents_arrive.php?num_ordre=${item.courrier_arrive_num_ordre}&annee=${courrierArriveAnnee}`)
                                        .then(response => response.json())
                                        .then(arriveDocs => {
                                            console.log('Documents arrivé reçus:', arriveDocs);
                                            if (arriveDocs && arriveDocs.length > 0) {
                                                td.appendChild(document.createElement('br'));
                                                arriveDocs.forEach((doc, idx) => {
                                                    const arriveRelativePath = doc.replace(chemin, repertoire);
                                                    const arriveLink = document.createElement('a');
                                                    arriveLink.href = arriveRelativePath;
                                                    arriveLink.textContent = `Doc ${idx + 1}`;
                                                    arriveLink.style.cursor = 'pointer';
                                                    arriveLink.style.color = 'orange';
                                                    arriveLink.style.textDecoration = 'underline';
                                                    arriveLink.style.fontSize = '0.9em';
                                                    arriveLink.addEventListener('click', function(e) {
                                                        e.preventDefault();
                                                        window.open(arriveRelativePath, '_blank', 'width=794,height=900');
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

                            td.style.border = '1px solid #ccc';
                            td.style.padding = '8px';
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
            console.log('Réponse brute:', responseText);
            document.getElementById('result-container').innerHTML = '<p>Une erreur est survenue lors de la recherche. Veuillez vérifier la console pour plus de détails.</p>';
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        document.getElementById('result-container').innerHTML = `<p>Une erreur est survenue lors de la recherche : ${error}</p>`;
    });
});

// Autocomplete pour le champ expéditeur
$.getJSON('get_expediteurs.php', function(data) {
    console.log('Expéditeurs reçus:', data);
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
    console.log('Sujets reçus:', data);
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