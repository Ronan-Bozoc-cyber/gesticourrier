<?php include 'admin/auth_check.php'; ?>
<?php include 'partials/parametres.php'; ?>
<?php require_once('partials/connexion.php');

// R�cup�rer le prochain num�ro d'ordre pour l'ann�e en cours
$date = $_GET['date'] ?? date('Y-m-d');
$year = date('Y', strtotime($date));

$query = "SELECT MAX(num_ordre) AS max_num_ordre FROM courriers_arrive WHERE annee = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $year);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$nextNumOrdre = ($row['max_num_ordre'] ?? 0) + 1;
$stmt->close();
/* DB connection intentionally left open for Singleton */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title> 📥 Courriers reçus</title>
    <link rel="stylesheet" href="css/style_general.css">
    <link rel="stylesheet" href="css/arrive.css">
    <!-- Inclure jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- Inclure jQuery UI -->
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <?php include 'partials/modal.html'; ?>
</head>
<body>
    <?php include 'partials/header.html'; ?>

    <!-- Modale pour les erreurs de taille de fichier -->
    <div id="fileSizeErrorModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeFileSizeErrorModal()">&times;</span>
            <h2 style="color: red;">Erreur de taille de fichier</h2>
            <p id="fileSizeErrorMessage"></p>
            <button onclick="closeFileSizeErrorModal()" style="background-color: #ff4444; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">OK</button>
        </div>
    </div>
    <!-- Modale pour confirmer l'enregistrement réussi -->
    <div id="successModal" class="modal">
        <div class="modal-content">
            <h2>✅ Enregistrement réussi !</h2>
            <p>Le courrier a été enregistré avec succès.</p>
        </div>
    </div>
    <!-- Modale pour rechercher un courrier par numéro d'ordre -->
    <div id="searchModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeSearchModal()">&times;</span>
            <h2 style="color: orange;">🔍 Rechercher un courrier entrant</h2>
            <div class="form-control">
                <label for="search_num_ordre">Numéro d'ordre :</label>
                <input type="number" id="search_num_ordre" placeholder="Saisir le numéro d'ordre" required>
            </div>
            <div class="form-control">
                <label for="search_annee">Année :</label>
                <select id="search_annee">
                    <!-- Les options seront ajoutées dynamiquement par JavaScript -->
                </select>
            </div>
            <button onclick="loadCourrierByNumOrdre()" style="background-color: orange; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">Rechercher</button>
        </div>
    </div>
    <!-- Modale pour afficher les fichiers -->
    <div id="fileModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeFileModal()">&times;</span>
            <div id="fileContent"></div>
        </div>
    </div>
    <!-- Modale pour afficher la progression -->
    <div id="loadingModal" class="modal">
        <div class="modal-content">
            <h2>Enregistrement en cours...</h2>
            <div class="progress-container">
                <div id="progressBar" class="progress-bar">0%</div>
            </div>
            <p>Veuillez patienter...</p>
        </div>
    </div>
    <div class="container">
        <div class="form-container" id="form-arrive">
            <h2 style="color:orange">📥 Information générale d'identification du courrier entrant</h2>
            <form id="courrier-form" action="save_courrier.php" method="post" enctype="multipart/form-data">
                <div class="bordures" style="border: 4px solid #ffa500;padding: 10px;margin-top: 10px;margin-bottom: 10px;border-radius:10px">
                    <div class="form-control">
                        <label for="flux">Flux:</label>
                        <input type="text" id="flux" name="flux" value="ARRIVE" readonly>
                    </div>
                    <div class="form-control">
                        <input type="hidden" id="current_user" value="<?php echo $_SESSION['user_id']; ?>">
                        <label for="traite_par">Traité par :</label>
                        <select id="traite_par" name="traite_par">
                            <!-- Les options seront ajoutées dynamiquement par JavaScript -->
                        </select>
                    </div>
                    <div class="form-control">
                        <label for="num_ordre">Numéro d'ordre :</label>
                        <input type="number" name="num_ordre" id="num_ordre" value="<?php echo $nextNumOrdre; ?>" required>
                    </div>
                    <div class="form-control">
                        <label for="date">Date:</label>
                        <input type="date" name="date" id="date" value="<?php echo $date; ?>" onchange="updateNumOrdre()" required>
                    </div>
                    <div class="form-control">
                        <label for="type_courrier">Type de courrier :</label>
                        <select name="type_courrier" id="type_courrier">
                            <option value="papier">Papier</option>
                            <option value="email">E-mail</option>
                            <option value="demat">Demat</option>
                        </select>
                    </div>
                </div>
                <h2 style="color:orange">🕵️‍♂️ Identification du courrier entrant</h2>
                <div class="bordures" style="border: 4px solid #ffa500;padding: 10px;margin-top: 10px;margin-bottom: 10px;border-radius:10px">
                    <div class="form-control">
                        <label for="expediteur">📌 Expéditeur :</label>
                        <input type="text" id="expediteur" name="expediteur" required>
                        <input type="hidden" id="expediteur_id" name="expediteur_id">
                        <input type="text" name="new_expediteur" id="new_expediteur" placeholder="Nouveau expéditeur">
                    </div>
                    <div class="form-control">
                        <label for="adresse">📌 Adresse :</label>
                        <textarea name="adresse" id="adresse" rows="3"></textarea>
                    </div>
                    <div class="form-control">
                        <label for="num_recommande">📌 Observations :</label>
                        <input type="text" name="num_recommande" id="num_recommande">
                    </div>
                    <div class="form-control">
                        <label for="sujet_courrier">📌 Sujet du courrier (les caractères suivants ne sont pas autorisés: /\.):</label>
                        <input type="text" name="sujet_courrier" id="sujet_courrier" required>
                    </div>
                    <div class="form-control">
                        <label for="categorie_courrier">📌 Catégorie de courrier :</label>
                        <select name="categorie_courrier" id="categorie_courrier">
                            <option value="">Chargement des catégories...</option>
                        </select>
                    </div>
                    <div class="form-control">
                        <label for="courrier_arrive">📌 Courrier départ associé :</label>
                        <input type="text" id="courrier_depart" name="courrier_depart" placeholder="Sélectionnez un courrier parti">
                        <input type="hidden" id="courrier_depart_id" name="courrier_depart_id">
                    </div>
                </div>
                <h2 style="color:orange">🧾 Association des fichiers (5 maximum & <2Mo)</h2>
                <div class="bordures" style="border: 4px solid #ffa500;padding: 10px;margin-top: 10px;margin-bottom: 10px;border-radius:10px">
                    <div class="form-control">
                        <label for="document1">🧷 Document 1 :</label>
                        <input type="file" name="document1" id="document1" accept=".pdf,.jpg,.jpeg,.png">
                        <div id="fileInfo1" class="file-info"></div>
                    </div>
                    <div class="form-control">
                        <label for="document2">🧷 Document 2 :</label>
                        <input type="file" name="document2" id="document2" accept=".pdf,.jpg,.jpeg,.png">
                        <div id="fileInfo2" class="file-info"></div>
                    </div>
                    <div class="form-control">
                        <label for="document3">🧷 Document 3 :</label>
                        <input type="file" name="document3" id="document3" accept=".pdf,.jpg,.jpeg,.png">
                        <div id="fileInfo3" class="file-info"></div>
                    </div>
                    <div class="form-control">
                        <label for="document4">🧷 Document 4 :</label>
                        <input type="file" name="document4" id="document4" accept=".pdf,.jpg,.jpeg,.png">
                        <div id="fileInfo4" class="file-info"></div>
                    </div>
                    <div class="form-control">
                        <label for="document5">🧷 Document 5 :</label>
                        <input type="file" name="document5" id="document5" accept=".pdf,.jpg,.jpeg,.png">
                        <div id="fileInfo5" class="file-info"></div>
                    </div>
                </div>
                <div class="form-control">
                    <button type="submit" class="btn" style="background-color:orange">Enregistrer Courrier</button>
                </div>
            </form>
        </div>
    </div>
    <div class="footer-entrant">
        <p>Liste des courriers entrants <button onclick="openSearchModal()">✏️ Modifier un courrier entrant</button></p>
    </div>
    <div class="table-container2">
        <table id="courriers-arrive-table" style="font-size: 12px;">
            <thead>
                <tr>
                    <th>Numéro d'Ordre</th>
                    <th>Date</th>
                    <th>Type de Courrier</th>
                    <th>Expéditeur</th>
                    <th>Sujet</th>
                    <th>Observations</th>
                    <th>Catégorie</th>
                    <th>Documents</th>
                    <th style="color:green">N° Ordre <br>Courrier Départ</th>
                    <th style="color:green">Courrier Départ</th>
                </tr>
            </thead>
            <tbody>
                <!-- Les enregistrements seront ajoutés ici via JS -->
            </tbody>
        </table>
    </div>
    <?php include 'partials/arrive_script.html'; ?>
    <?php include 'partials/menu_actif.html'; ?>
    <?php include 'partials/autocomplete.html'; ?>
   
    <!-- Script pour gérer les fichiers et la modale -->
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
    // Fonction pour fermer la modale d'erreur de taille de fichier
    function closeFileSizeErrorModal() {
        document.getElementById('fileSizeErrorModal').style.display = 'none';
    }
    // Fonction pour ouvrir la modale de recherche
    function openSearchModal() {
        document.getElementById('searchModal').style.display = 'block';
        fillYearsDropdown();
    }
    // Fonction pour fermer la modale de recherche
    function closeSearchModal() {
        document.getElementById('searchModal').style.display = 'none';
    }
    // Fonction pour fermer la modale de fichier
    function closeFileModal() {
        document.getElementById('fileModal').style.display = 'none';
    }
    // Fonction pour valider la taille des fichiers avant soumission
    function validateFileSizes() {
        const fileInputs = document.querySelectorAll('input[type="file"]');
        let hasError = false;
        let errorMessage = "Les fichiers suivants sont trop volumineux (max 2 Mo) :<br><br>";
        fileInputs.forEach(function(input) {
            const file = input.files[0];
            if (file) {
                const sizeInMo = (file.size / (1024 * 1024)).toFixed(2);
                if (sizeInMo > 2) {
                    hasError = true;
                    errorMessage += `- ${file.name} (${sizeInMo} Mo)<br>`;
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
    // Fonction pour remplir le menu déroulant des années référencées
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
            .catch(error => {
                console.error('Erreur lors du chargement des années:', error);
            });
    }
    // Fonction pour mettre à jour le numéro d'ordre en fonction de la date
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
    // Fonction pour charger les données du courrier par numéro d'ordre et année
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
                    // Remplit le formulaire avec les données du courrier
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

                    // Affiche les chemins des fichiers existants avec des liens cliquables et une option de suppression
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
            .finally(() => {
                closeSearchModal();
            });
    }
    // Fonction pour ouvrir une modale avec le fichier
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
            content.style.color = 'blue';
            content.target = '_blank';
        }
        fileContent.innerHTML = '';
        fileContent.appendChild(content);
    }
    // Fonction pour supprimer un fichier
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
    // Script pour afficher le nom et la taille des fichiers sélectionnés
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
                            ${sizeInMo > 2 ? ' (Trop volumineux !)' : ''}
                        </span>
                    </div>
                `;
            }
        });
    });
    // Gestion de la soumission du formulaire via AJAX
    document.getElementById('courrier-form').addEventListener('submit', function(e) {
        e.preventDefault(); // Empêche la soumission classique
        if (!validateFileSizes()) {
            return; // Si un fichier est trop volumineux, on ne soumet pas
        }
        // Affiche la modale de chargement
        document.getElementById('loadingModal').style.display = 'block';
        const progressBar = document.getElementById('progressBar');
        let progress = 0;
        const interval = setInterval(() => {
            progress += 5;
            progressBar.style.width = progress + '%';
            progressBar.textContent = progress + '%';
            if (progress >= 95) {
                clearInterval(interval);
            }
        }, 200);
        // Récupère les données du formulaire
        const formData = new FormData(this);
        // Envoie les données via AJAX
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
                    // Affiche la modale de succès
                    document.getElementById('successModal').style.display = 'block';
                    // Ferme la modale après 3 secondes, réinitialise le formulaire et recharge la page
                    setTimeout(function() {
                        document.getElementById('successModal').style.display = 'none';
                        location.reload(); // Rafraîchit la page
                    }, 3000);
                } else {
                    alert("Erreur lors de l'enregistrement : " + data);
                }
            }, 500);
        })
        .catch(error => {
            clearInterval(interval);
            document.getElementById('loadingModal').style.display = 'none';
            console.error('Erreur:', error);
            alert("Une erreur est survenue lors de l'enregistrement.");
        });
    });
    </script>
</body>
</html>
