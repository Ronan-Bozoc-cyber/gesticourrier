<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📥 Courriers reçus - OpenGestiCourrier</title>
    <link rel="stylesheet" href="css/style_general.css">
    <link rel="stylesheet" href="css/arrive.css">
    <!-- Inclure jQuery & jQuery UI -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php include 'partials/modal.html'; ?>
</head>
<body>
    <?php include 'partials/header.html'; ?>

    <!-- Modale pour les erreurs de taille de fichier -->
    <div id="fileSizeErrorModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeFileSizeErrorModal()">&times;</span>
            <h2 style="color: #ef4444;"><i class="fas fa-exclamation-triangle"></i> Fichier trop volumineux</h2>
            <p id="fileSizeErrorMessage"></p>
            <button onclick="closeFileSizeErrorModal()" style="background-color: #ef4444; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: bold; width: 100%;">Fermer</button>
        </div>
    </div>

    <!-- Modale pour confirmer l'enregistrement réussi -->
    <div id="successModal" class="modal">
        <div class="modal-content" style="text-align: center;">
            <h2 style="color: #f59e0b;"><i class="fas fa-check-circle"></i> Enregistrement réussi !</h2>
            <p>Le courrier entrant a été enregistré avec succès.</p>
        </div>
    </div>

    <!-- Modale pour rechercher un courrier par numéro d'ordre -->
    <div id="searchModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeSearchModal()">&times;</span>
            <h2 style="color: #d97706;"><i class="fas fa-search"></i> Rechercher un courrier entrant</h2>
            <div class="form-group" style="margin-bottom: 14px;">
                <label for="search_num_ordre">Numéro d'ordre :</label>
                <input type="number" id="search_num_ordre" placeholder="Saisir le numéro d'ordre" required>
            </div>
            <div class="form-group" style="margin-bottom: 20px;">
                <label for="search_annee">Année :</label>
                <select id="search_annee">
                    <!-- Options dynamiques -->
                </select>
            </div>
            <button onclick="loadCourrierByNumOrdre()" class="btn-action-search" style="width: 100%; justify-content: center;"><i class="fas fa-search"></i> Rechercher</button>
        </div>
    </div>

    <!-- Modale pour afficher les fichiers -->
    <div id="fileModal" class="modal">
        <div class="modal-content" style="max-width: 900px;">
            <span class="close-modal" onclick="closeFileModal()">&times;</span>
            <div id="fileContent"></div>
        </div>
    </div>

    <!-- Modale pour afficher la progression -->
    <div id="loadingModal" class="modal">
        <div class="modal-content" style="text-align: center;">
            <h2>Enregistrement en cours...</h2>
            <div class="progress-container">
                <div id="progressBar" class="progress-bar">0%</div>
            </div>
            <p style="color: #64748b; font-size: 0.9rem;">Veuillez patienter pendant l'envoi des documents...</p>
        </div>
    </div>

    <div class="main-container">
        <!-- Barre d'action et Titre -->
        <div class="page-action-bar">
            <div class="page-title-badge">
                <i class="fas fa-inbox"></i> Enregistrement des courriers entrants
            </div>
            <button onclick="openSearchModal()" class="btn-action-search">
                <i class="fas fa-edit"></i> Modifier / Rechercher un courrier
            </button>
        </div>

        <?php if (!$can_edit): ?>
            <div style="background: #fef3c7; color: #b45309; border: 1.5px solid #fde68a; padding: 14px 20px; border-radius: 10px; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                <div>
                    <i class="fas fa-lock" style="font-size: 1.2rem; margin-right: 8px;"></i>
                    <strong>Mode Consultation Uniquement :</strong> La saisie est actuellement détenue par <u><?php echo htmlspecialchars($lockState['lock_username'] ?? 'un autre utilisateur'); ?></u>.
                </div>
                <a href="lock_action.php?action=claim" class="btn-lock-action claim" style="white-space: nowrap;"><i class="fas fa-pen"></i> Prendre la main pour saisir</a>
            </div>
            <style>
                #courrier-form input:not([type="hidden"]), 
                #courrier-form select, 
                #courrier-form textarea,
                .btn-submit-main {
                    pointer-events: none !important;
                    opacity: 0.65;
                }
                .btn-submit-main {
                    display: none !important;
                }
            </style>
        <?php endif; ?>

        <div class="form-container" id="form-arrive">
            <form id="courrier-form" action="save_courrier.php" method="post" enctype="multipart/form-data">
                
                <!-- Carte 1 : Informations Générales -->
                <div class="form-section-card">
                    <div class="form-section-header">
                        <h2><i class="fas fa-info-circle"></i> 1. Informations générales de suivi</h2>
                    </div>
                    <div class="form-grid-4">
                        <div class="form-group">
                            <label for="flux">Flux :</label>
                            <input type="text" id="flux" name="flux" value="ARRIVE" readonly>
                        </div>
                        <div class="form-group">
                            <input type="hidden" id="current_user" value="<?php echo $_SESSION['user_id']; ?>">
                            <label for="traite_par">Traité par :</label>
                            <select id="traite_par" name="traite_par">
                                <!-- Chargement dynamique par JS -->
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="num_ordre">Numéro d'ordre :</label>
                            <input type="number" name="num_ordre" id="num_ordre" value="<?php echo $nextNumOrdre; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="date">Date de réception :</label>
                            <input type="date" name="date" id="date" value="<?php echo $date; ?>" onchange="updateNumOrdre()" required>
                        </div>
                        <div class="form-group form-grid-full">
                            <label for="type_courrier">Format du courrier :</label>
                            <select name="type_courrier" id="type_courrier">
                                <option value="papier">📄 Papier</option>
                                <option value="email">✉️ E-mail</option>
                                <option value="demat">💻 Dématérialisé</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Carte 2 : Identification & Contenu -->
                <div class="form-section-card">
                    <div class="form-section-header">
                        <h2><i class="fas fa-user-tag"></i> 2. Expéditeur & Identification du courrier</h2>
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label for="expediteur">📌 Expéditeur :</label>
                            <input type="text" id="expediteur" name="expediteur" placeholder="Tapez pour rechercher un expéditeur..." required>
                            <input type="hidden" id="expediteur_id" name="expediteur_id">
                            <input type="text" name="new_expediteur" id="new_expediteur" placeholder="+ Ajouter un nouvel expéditeur si inexistant" style="margin-top:6px;">
                        </div>
                        <div class="form-group">
                            <label for="adresse">📌 Adresse de l'expéditeur :</label>
                            <textarea name="adresse" id="adresse" rows="3" placeholder="Adresse complète..."></textarea>
                        </div>
                        <div class="form-group form-grid-full">
                            <label for="sujet_courrier">📌 Objet / Sujet du courrier :</label>
                            <input type="text" name="sujet_courrier" id="sujet_courrier" placeholder="Intitulé concis du courrier..." required>
                        </div>
                        <div class="form-group">
                            <label for="categorie_courrier">📌 Catégorie :</label>
                            <select name="categorie_courrier" id="categorie_courrier">
                                <option value="">Chargement des catégories...</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="courrier_depart">📌 Courrier départ associé :</label>
                            <input type="text" id="courrier_depart" name="courrier_depart" placeholder="Sélectionnez un courrier parti associé...">
                            <input type="hidden" id="courrier_depart_id" name="courrier_depart_id">
                        </div>
                        <div class="form-group form-grid-full">
                            <label for="num_recommande">📌 Observations & Références :</label>
                            <input type="text" name="num_recommande" id="num_recommande" placeholder="N° recommandé, références ou remarques particulières...">
                        </div>
                    </div>
                </div>

                <!-- Carte 3 : Pièces jointes -->
                <div class="form-section-card">
                    <div class="form-section-header">
                        <h2><i class="fas fa-paperclip"></i> 3. Documents joints (5 Fichiers max, < 2 Mo par fichier)</h2>
                    </div>
                    <div class="file-grid">
                        <div class="file-card-item">
                            <label for="document1"><i class="fas fa-file-pdf"></i> Document 1</label>
                            <input type="file" name="document1" id="document1" accept=".pdf,.jpg,.jpeg,.png">
                            <div id="fileInfo1" class="file-info"></div>
                        </div>
                        <div class="file-card-item">
                            <label for="document2"><i class="fas fa-file-pdf"></i> Document 2</label>
                            <input type="file" name="document2" id="document2" accept=".pdf,.jpg,.jpeg,.png">
                            <div id="fileInfo2" class="file-info"></div>
                        </div>
                        <div class="file-card-item">
                            <label for="document3"><i class="fas fa-file-pdf"></i> Document 3</label>
                            <input type="file" name="document3" id="document3" accept=".pdf,.jpg,.jpeg,.png">
                            <div id="fileInfo3" class="file-info"></div>
                        </div>
                        <div class="file-card-item">
                            <label for="document4"><i class="fas fa-file-pdf"></i> Document 4</label>
                            <input type="file" name="document4" id="document4" accept=".pdf,.jpg,.jpeg,.png">
                            <div id="fileInfo4" class="file-info"></div>
                        </div>
                        <div class="file-card-item">
                            <label for="document5"><i class="fas fa-file-pdf"></i> Document 5</label>
                            <input type="file" name="document5" id="document5" accept=".pdf,.jpg,.jpeg,.png">
                            <div id="fileInfo5" class="file-info"></div>
                        </div>
                    </div>

                    <!-- Container d'aperçu en direct du document avec tampon & zoom -->
                    <div id="live-stamp-preview-container" class="live-stamp-preview-container" style="display: none;">
                        <div class="preview-toolbar-bar">
                            <div id="doc-preview-info-badge" style="font-weight: 700; color: #1e40af; font-size: 0.92rem;">
                                <i class="fas fa-file-alt"></i> Aperçu du courrier avec tampon apposé
                            </div>
                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                <!-- Navigation Multi-Pages PDF -->
                                <div id="pdf-page-nav-controls" style="display: none; align-items: center; gap: 6px; background: #ffffff; padding: 3px 8px; border-radius: 6px; border: 1px solid #cbd5e1;">
                                    <button type="button" id="btn-pdf-prev" onclick="changePdfPage(-1)" class="btn-zoom" style="padding: 2px 8px;"><i class="fas fa-chevron-left"></i> Précédent</button>
                                    <span style="font-size: 0.85rem; font-weight: 700; color: #1e40af; padding: 0 4px;">Page <span id="pdf-page-num">1</span> / <span id="pdf-page-count">1</span></span>
                                    <button type="button" id="btn-pdf-next" onclick="changePdfPage(1)" class="btn-zoom" style="padding: 2px 8px;">Suivant <i class="fas fa-chevron-right"></i></button>
                                </div>

                                <span style="font-size: 0.82rem; color: #475569; font-weight: 600; background: #e2e8f0; padding: 4px 10px; border-radius: 6px;"><i class="fas fa-hand-pointer" style="color: #2563eb;"></i> Déplacez & redimensionnez le tampon au coin</span>
                                <button type="button" onclick="changeStampFontSize(2)" class="btn-zoom" title="Agrandir la taille du tampon"><i class="fas fa-text-height"></i> Tampon +</button>
                                <button type="button" onclick="changeStampFontSize(-2)" class="btn-zoom" title="Réduire la taille du tampon"><i class="fas fa-text-height"></i> Tampon -</button>
                                <button type="button" onclick="applyStampStyles()" class="btn-zoom" title="Replacer le tampon à sa position par défaut"><i class="fas fa-map-marker-alt"></i> Position initiale</button>
                                <button type="button" onclick="changePreviewZoom(0.2)" class="btn-zoom"><i class="fas fa-search-plus"></i> Page +</button>
                                <button type="button" onclick="changePreviewZoom(-0.2)" class="btn-zoom"><i class="fas fa-search-minus"></i> Page -</button>
                                <button type="button" onclick="resetPreviewZoom()" class="btn-zoom"><i class="fas fa-sync-alt"></i> 100%</button>
                                <span id="zoom-level-indicator" style="font-weight: 700; color: #1e40af; font-size: 0.88rem; background: #ffffff; padding: 4px 10px; border-radius: 6px; border: 1px solid #cbd5e1;">100%</span>
                            </div>
                        </div>

                        <div class="stamp-doc-viewer-wrapper">
                            <div id="stamp-doc-sheet" class="stamp-doc-sheet">
                                <div id="stamp-doc-canvas" class="stamp-doc-canvas">
                                    <div id="doc-stamp-overlay" class="doc-stamp-overlay"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bouton d'enregistrement principal -->
                <div style="margin-top: 24px;">
                    <button type="submit" class="btn-submit-main">
                        <i class="fas fa-save"></i> Enregistrer le Courrier Entrant
                    </button>
                </div>
            </form>
        </div>

        <!-- Section Liste des courriers entrants -->
        <div class="table-header-bar">
            <h3><i class="fas fa-list-alt"></i> Liste récente des courriers entrants</h3>
            <button onclick="openSearchModal()" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.4); padding: 6px 14px; border-radius: 6px; cursor: pointer; font-size: 0.88rem; font-weight: 600;">
                <i class="fas fa-search"></i> Filtrer / Rechercher
            </button>
        </div>
        <div class="table-container2" style="margin-top: 0; border-radius: 0 0 12px 12px;">
            <table id="courriers-arrive-table" style="font-size: 13px;">
                <thead>
                    <tr>
                        <th>N° Ordre</th>
                        <th>Date</th>
                        <th>Format</th>
                        <th>Expéditeur</th>
                        <th>Sujet</th>
                        <th>Observations</th>
                        <th>Catégorie</th>
                        <th>Documents</th>
                        <th style="color:#10b981;">N° Ordre<br>Départ</th>
                        <th style="color:#10b981;">Courrier Départ</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- JS population -->
                </tbody>
            </table>
        </div>
    </div>

    <?php include 'partials/arrive_script.html'; ?>
    <?php include 'partials/menu_actif.html'; ?>
    <?php include 'partials/autocomplete.html'; ?>

    <script>
    function escapeHTML(str) {
        return str.replace(/[&<>'"]/g, 
            tag => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                "'": '&#39;',
                '"': '&quot;'
            }[tag] || tag)
        );
    }
    function closeFileSizeErrorModal() {
        document.getElementById('fileSizeErrorModal').style.display = 'none';
    }
    function openSearchModal() {
        document.getElementById('searchModal').style.display = 'block';
        fillYearsDropdown();
    }
    function closeSearchModal() {
        document.getElementById('searchModal').style.display = 'none';
    }
    function closeFileModal() {
        document.getElementById('fileModal').style.display = 'none';
    }
    function validateFileSizes() {
        const fileInputs = document.querySelectorAll('input[type="file"]');
        let hasError = false;
        let errorMessage = "Les fichiers suivants dépassent la limite de 2 Mo :<br><br>";
        fileInputs.forEach(function(input) {
            const file = input.files[0];
            if (file) {
                const sizeInMo = (file.size / (1024 * 1024)).toFixed(2);
                if (sizeInMo > 2) {
                    hasError = true;
                    errorMessage += `- <strong>${escapeHTML(file.name)}</strong> (${sizeInMo} Mo)<br>`;
                }
            }
        });
        if (hasError) {
            document.getElementById('fileSizeErrorMessage').innerHTML = errorMessage;
            document.getElementById('fileSizeErrorModal').style.display = 'block';
            return false;
        }
        return true;
    }
    function fillYearsDropdown() {
        fetch('get_years.php')
            .then(response => response.json())
            .then(years => {
                const searchAnneeSelect = document.getElementById('search_annee');
                searchAnneeSelect.innerHTML = '';
                years.forEach(year => {
                    const option = document.createElement('option');
                    option.value = year;
                    option.textContent = year;
                    searchAnneeSelect.appendChild(option);
                });
            })
            .catch(error => console.error('Erreur lors du chargement des années:', error));
    }
    function updateNumOrdre() {
        const date = document.getElementById('date').value;
        const flux = 'ARRIVE';
        fetch(`get_next_num_ordre.php?date=${date}&flux=${flux}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('num_ordre').value = data.nextNumOrdre;
            })
            .catch(error => console.error('Erreur:', error));
    }
    function loadCourrierByNumOrdre() {
        const numOrdre = document.getElementById('search_num_ordre').value;
        const annee = document.getElementById('search_annee').value;
        const urllogiciel = "<?php echo $urllogiciel; ?>";

        if (!numOrdre) {
            alert("Veuillez saisir un numéro d'ordre.");
            return;
        }

        fetch(`obtenir_courrier_arrive.php?num_ordre=${numOrdre}&annee=${annee}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                } else {
                    document.getElementById('num_ordre').value = data.num_ordre;
                    document.getElementById('date').value = data.date;
                    document.getElementById('type_courrier').value = data.type_courrier;
                    document.getElementById('expediteur').value = data.expediteur_name;
                    document.getElementById('expediteur_id').value = data.expediteur_id;
                    document.getElementById('adresse').value = data.expediteur_adresse;
                    document.getElementById('num_recommande').value = data.num_recommande || '';
                    document.getElementById('sujet_courrier').value = data.sujet_courrier;
                    document.getElementById('categorie_courrier').value = data.categorie_courrier;
                    document.getElementById('courrier_depart').value = data.courrier_depart_num_ordre || '';
                    document.getElementById('courrier_depart_id').value = data.courrier_depart_id || '';
                    document.getElementById('traite_par').value = data.traite_par || '';

                    const filePaths = [
                        { elementId: 'fileInfo1', path: data.document_path, inputName: 'existing_document1' },
                        { elementId: 'fileInfo2', path: data.document_path2, inputName: 'existing_document2' },
                        { elementId: 'fileInfo3', path: data.document_path3, inputName: 'existing_document3' },
                        { elementId: 'fileInfo4', path: data.document_path4, inputName: 'existing_document4' },
                        { elementId: 'fileInfo5', path: data.document_path5, inputName: 'existing_document5' }
                    ];

                    filePaths.forEach((file) => {
                        const fileInfoElement = document.getElementById(file.elementId);
                        if (file.path) {
                            const fileName = file.path.split('/').pop();
                            const absolutePath = file.path;
                            const relativePath = file.path.replace(chemin, '');
                            const fullPath = urllogiciel + relativePath;
                            fileInfoElement.innerHTML = `
                                <div class="file-actions">
                                    <a href="#" onclick="openFileModal('${escapeHTML(fullPath)}')">${escapeHTML(fileName)}</a>
                                    <button type="button" onclick="removeFile('${file.elementId}', '${file.inputName}')">Supprimer</button>
                                    <input type="hidden" name="${file.inputName}" value="${absolutePath}">
                                </div>
                            `;
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert("Une erreur est survenue lors de la recherche.");
            })
            .finally(() => closeSearchModal());
    }
    function openFileModal(filePath) {
        document.getElementById('fileModal').style.display = 'block';
        const fileContent = document.getElementById('fileContent');
        const fileExtension = filePath.split('.').pop().toLowerCase();
        let content;
        if (['jpg', 'jpeg', 'png'].includes(fileExtension)) {
            content = document.createElement('img');
            content.src = filePath;
            content.style.maxWidth = '100%';
            content.style.maxHeight = '80vh';
        } else if (fileExtension === 'pdf') {
            content = document.createElement('iframe');
            content.src = filePath;
            content.style.width = '100%';
            content.style.height = '80vh';
            content.style.border = 'none';
        } else {
            content = document.createElement('a');
            content.href = filePath;
            content.textContent = 'Télécharger le fichier';
            content.style.display = 'block';
            content.style.marginTop = '20px';
            content.style.color = '#2563eb';
            content.target = '_blank';
        }
        fileContent.innerHTML = '';
        fileContent.appendChild(content);
    }
    function removeFile(elementId, inputName) {
        if (confirm("Êtes-vous sûr de vouloir supprimer ce fichier ?")) {
            const fileInfoElement = document.getElementById(elementId);
            const hiddenInput = fileInfoElement.querySelector(`input[name="${inputName}"]`);
            fileInfoElement.innerHTML = '';
            if (hiddenInput) {
                hiddenInput.remove();
            }
        }
    }
    document.querySelectorAll('input[type="file"]').forEach(function(input, index) {
        const fileInfoId = 'fileInfo' + (index + 1);
        const fileInfoContainer = document.getElementById(fileInfoId);
        input.addEventListener('change', function(e) {
            fileInfoContainer.innerHTML = '';
            const file = e.target.files[0];
            if (file) {
                const fileName = file.name;
                const sizeInMo = (file.size / (1024 * 1024)).toFixed(2);
                const className = sizeInMo > 2 ? 'file-error' : 'file-ok';
                fileInfoContainer.innerHTML = `
                    <div>
                        <strong>${escapeHTML(fileName)}</strong> (${sizeInMo} Mo)
                        <span class="${className}">
                            ${sizeInMo > 2 ? ' (Trop volumineux !)' : ' ✓ OK'}
                        </span>
                    </div>
                `;
            }
        });
    });
    document.getElementById('courrier-form').addEventListener('submit', function(e) {
        e.preventDefault();
        if (!validateFileSizes()) return;
        
        document.getElementById('loadingModal').style.display = 'block';
        const progressBar = document.getElementById('progressBar');
        let progress = 0;
        const interval = setInterval(() => {
            progress += 5;
            progressBar.style.width = progress + '%';
            progressBar.textContent = progress + '%';
            if (progress >= 95) clearInterval(interval);
        }, 200);

        const formData = new FormData(this);
        fetch('save_courrier.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            clearInterval(interval);
            progressBar.style.width = '100%';
            progressBar.textContent = '100%';
            setTimeout(() => {
                document.getElementById('loadingModal').style.display = 'none';
                if (data.includes("enregistré avec succès")) {
                    document.getElementById('successModal').style.display = 'block';
                    setTimeout(function() {
                        document.getElementById('successModal').style.display = 'none';
                        location.reload();
                    }, 2500);
                } else {
                    alert("Erreur lors de l'enregistrement : " + data);
                }
            }, 400);
        })
        .catch(error => {
            clearInterval(interval);
            document.getElementById('loadingModal').style.display = 'none';
            console.error('Erreur:', error);
            alert("Une erreur est survenue lors de l'enregistrement.");
        });
    });
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <script>
    window.STAMP_CONFIG = <?php echo json_encode($org_settings ?? [], JSON_UNESCAPED_UNICODE); ?>;
    </script>
    <script src="js/stamp_preview_doc.js"></script>
</body>
</html>
