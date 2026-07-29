<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('add-expediteur-btn').addEventListener('click', function() {
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

    document.getElementById('add-expediteur-form').addEventListener('submit', function(event) {
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
                alert('Expéditeur ajouté avec succès !');
                location.reload();  // Recharger la page pour afficher la nouvelle liste
            } else {
                alert('Erreur lors de l\'ajout de l\'expéditeur : ' + data.error);
            }
        })
        .catch(error => console.error('Erreur:', error));
    });

    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function() {
            const modal = document.getElementById('editModal');
            modal.style.display = 'block';
            
            document.getElementById('edit-id').value = this.dataset.id;
            document.getElementById('edit-name').value = this.dataset.name;
            document.getElementById('edit-adresse').value = this.dataset.adresse;
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

    document.getElementById('edit-expediteur-form').addEventListener('submit', function(event) {
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
                alert('Expéditeur mis à jour avec succès !');
                document.getElementById('editModal').style.display = 'none';
                location.reload();  // Recharger la page pour afficher la nouvelle liste
            } else {
                alert('Erreur lors de la mise à jour de l\'expéditeur : ' + data.error);
            }
        })
        .catch(error => console.error('Erreur:', error));
    });

   

    // Fonction de recherche des expéditeurs
    document.getElementById('search-expediteur').addEventListener('input', function() {
        const searchValue = this.value.toLowerCase();
        const rows = document.querySelectorAll('#expediteur-table tbody tr');

        rows.forEach(row => {
            const name = row.querySelector('td:first-child').textContent.toLowerCase();
            if (name.includes(searchValue)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
});
</script>
