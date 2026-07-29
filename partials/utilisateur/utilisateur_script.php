<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('add-user-btn').addEventListener('click', function() {
        document.getElementById('addModal').style.display = 'block';
    });

    document.querySelector('.close-add').addEventListener('click', function() {
        document.getElementById('addModal').style.display = 'none';
    });

    window.addEventListener('click', function(event) {
        if (event.target == document.getElementById('addModal')) {
            document.getElementById('addModal').style.display = 'none';
        }
    });

    document.getElementById('add-user-form').addEventListener('submit', function(event) {
        event.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'add');

        fetch('', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Utilisateur ajouté avec succès !');
                location.reload();  // Recharger la page pour afficher la nouvelle liste
            } else {
                alert('Erreur lors de l\'ajout de l\'utilisateur : ' + data.error);
            }
        })
        .catch(error => console.error('Erreur:', error));
    });

    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function() {
            const modal = document.getElementById('editModal');
            modal.style.display = 'block';
            
            document.getElementById('edit-id').value = this.dataset.id;
            document.getElementById('edit-username').value = this.dataset.username;
            document.getElementById('edit-email').value = this.dataset.email;
            document.getElementById('edit-role').value = this.dataset.role;
        });
    });

    document.querySelector('.close').addEventListener('click', function() {
        document.getElementById('editModal').style.display = 'none';
    });

    window.addEventListener('click', function(event) {
        if (event.target == document.getElementById('editModal')) {
            document.getElementById('editModal').style.display = 'none';
        }
    });

    document.getElementById('edit-user-form').addEventListener('submit', function(event) {
        event.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'update');

        fetch('', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Utilisateur mis à jour avec succès !');
                document.getElementById('editModal').style.display = 'none';
                location.reload();  // Recharger la page pour afficher la nouvelle liste
            } else {
                alert('Erreur lors de la mise à jour de l\'utilisateur : ' + data.error);
            }
        })
        .catch(error => console.error('Erreur:', error));
    });

    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.dataset.id;

            if (confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', userId);

                fetch('', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Utilisateur supprimé avec succès !');
                        location.reload();  // Recharger la page pour afficher la nouvelle liste
                    } else {
                        alert('Erreur lors de la suppression de l\'utilisateur : ' + data.error);
                    }
                })
                .catch(error => console.error('Erreur:', error));
            }
        });
    });

    // Fonction de recherche des utilisateurs
    document.getElementById('search-user').addEventListener('input', function() {
        const searchValue = this.value.toLowerCase();
        const rows = document.querySelectorAll('#user-table tbody tr');

        rows.forEach(row => {
            const username = row.querySelector('td:first-child').textContent.toLowerCase();
            if (username.includes(searchValue)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
});
</script>
