<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration</title>
    <link rel="stylesheet" href="../css/style_general.css">
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            padding-top: 60px;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <?php include '../partials/header.html'; ?>

    <div class="main-container">
        <div class="content-container">
            <h1>Administration des Utilisateurs</h1>

            <!-- Bouton pour ajouter un utilisateur -->
            <button id="add-user-button">Ajouter un utilisateur</button>

            <!-- Liste des utilisateurs -->
            <div class="user-list">
                <h2>Liste des Utilisateurs</h2>
                <table id="user-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom d'utilisateur</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Les lignes des utilisateurs seront générées ici -->
                    </tbody>
                </table>
            </div>

            <!-- Modal pour modification -->
            <div id="editModal" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h2>Modifier l'Utilisateur</h2>
                    <form id="edit-user-form">
                        <input type="hidden" id="edit-id" name="id">
                        
                        <label for="edit-username">Nom d'utilisateur</label>
                        <input type="text" id="edit-username" name="username" required>

                        <label for="edit-email">Email</label>
                        <input type="email" id="edit-email" name="email" required>

                        <label for="edit-role">Rôle</label>
                        <select id="edit-role" name="role" required>
                            <option value="user">Utilisateur</option>
                            <option value="admin">Administrateur</option>
                        </select>

                        <label for="edit-password">Nouveau mot de passe (laissez vide pour ne pas modifier)</label>
                        <input type="password" id="edit-password" name="password">

                        <button type="submit">Enregistrer les modifications</button>
                    </form>
                </div>
            </div>

            <!-- Modal pour ajout -->
            <div id="addModal" class="modal">
                <div class="modal-content">
                    <span class="close-add">&times;</span>
                    <h2>Ajouter un Utilisateur</h2>
                    <form id="add-user-form">
                        <label for="add-username">Nom d'utilisateur</label>
                        <input type="text" id="add-username" name="username" required>

                        <label for="add-email">Email</label>
                        <input type="email" id="add-email" name="email" required>

                        <label for="add-role">Rôle</label>
                        <select id="add-role" name="role" required>
                            <option value="user">Utilisateur</option>
                            <option value="admin">Administrateur</option>
                        </select>

                        <label for="add-password">Mot de passe</label>
                        <input type="password" id="add-password" name="password" required>

                        <button type="submit">Ajouter l'utilisateur</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
    fetchUsers();

    // Ajouter un utilisateur
    document.getElementById('add-user-button').addEventListener('click', function() {
        document.getElementById('addModal').style.display = 'block';
    });

    document.querySelector('.close-add').addEventListener('click', function() {
        document.getElementById('addModal').style.display = 'none';
    });

    document.getElementById('add-user-form').addEventListener('submit', function(event) {
        event.preventDefault();
        const formData = new FormData(this);

        fetch('admin_add_user_handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Utilisateur ajouté avec succès !');
                document.getElementById('addModal').style.display = 'none';
                fetchUsers();
            } else {
                alert('Erreur lors de l\'ajout de l\'utilisateur : ' + data.error);
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Une erreur est survenue : ' + error);
        });
    });
});

function fetchUsers() {
    fetch('get_users.php')
    .then(response => response.json())
    .then(data => {
        const tableBody = document.querySelector('#user-table tbody');
        tableBody.innerHTML = '';

        data.forEach(user => {
            const row = document.createElement('tr');

            const idCell = document.createElement('td');
            idCell.textContent = user.id;
            row.appendChild(idCell);

            const usernameCell = document.createElement('td');
            usernameCell.textContent = user.username;
            row.appendChild(usernameCell);

            const emailCell = document.createElement('td');
            emailCell.textContent = user.email;
            row.appendChild(emailCell);

            const roleCell = document.createElement('td');
            roleCell.textContent = user.role;
            row.appendChild(roleCell);

            const actionsCell = document.createElement('td');
            const editButton = document.createElement('button');
            editButton.textContent = 'Modifier';
            editButton.addEventListener('click', () => openEditModal(user));
            actionsCell.appendChild(editButton);

            const deleteButton = document.createElement('button');
            deleteButton.textContent = 'Supprimer';
            deleteButton.addEventListener('click', () => deleteUser(user.id));
            actionsCell.appendChild(deleteButton);

            row.appendChild(actionsCell);

            tableBody.appendChild(row);
        });
    })
    .catch(error => console.error('Erreur:', error));
}

function openEditModal(user) {
    const modal = document.getElementById('editModal');
    modal.style.display = "block";

    document.getElementById('edit-id').value = user.id;
    document.getElementById('edit-username').value = user.username;
    document.getElementById('edit-email').value = user.email;
    document.getElementById('edit-role').value = user.role;
}

function deleteUser(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) {
        fetch('delete_user.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Utilisateur supprimé avec succès !');
                fetchUsers();
            } else {
                alert('Erreur lors de la suppression de l\'utilisateur : ' + data.error);
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Une erreur est survenue : ' + error);
        });
    }
}

document.querySelector('.close').addEventListener('click', () => {
    document.getElementById('editModal').style.display = 'none';
});

window.addEventListener('click', event => {
    const modal = document.getElementById('editModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
});

document.getElementById('edit-user-form').addEventListener('submit', function(event) {
    event.preventDefault();
    const formData = new FormData(this);

    fetch('update_user.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Utilisateur mis à jour avec succès !');
            document.getElementById('editModal').style.display = 'none';
            fetchUsers();
        } else {
            alert('Erreur lors de la mise à jour de l\'utilisateur : ' + data.error);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Une erreur est survenue : ' + error);
    });
});

    </script>
    <?php include 'partials/arrive_script.html'; ?>
</body>
</html>
