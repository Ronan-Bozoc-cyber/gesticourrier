<script>
document.addEventListener('DOMContentLoaded', function() {
    const addBtn = document.getElementById('add-expediteur-btn');
    if (addBtn) {
        addBtn.addEventListener('click', function() {
            const addModal = document.getElementById('addModal');
            if (addModal) {
                addModal.style.display = 'block';
            } else {
                const addName = document.getElementById('add-name');
                if (addName) addName.focus();
            }
        });
    }

    const closeAdd = document.querySelector('.close-add');
    if (closeAdd) {
        closeAdd.addEventListener('click', function() {
            const addModal = document.getElementById('addModal');
            if (addModal) addModal.style.display = 'none';
        });
    }

    const addForm = document.getElementById('add-expediteur-form');
    if (addForm) {
        addForm.addEventListener('submit', function(event) {
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
                    alert('Contact ajouté avec succès !');
                    location.reload();
                } else {
                    alert('Erreur lors de l\'ajout du contact : ' + data.error);
                }
            })
            .catch(error => console.error('Erreur:', error));
        });
    }

    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function() {
            const modal = document.getElementById('editModal');
            if (modal) modal.style.display = 'block';
            
            const editId = document.getElementById('edit-id');
            const editName = document.getElementById('edit-name');
            const editAdresse = document.getElementById('edit-adresse');

            if (editId) editId.value = this.dataset.id;
            if (editName) editName.value = this.dataset.name;
            if (editAdresse) editAdresse.value = this.dataset.adresse;
        });
    });

    const closeEdit = document.querySelector('.close');
    if (closeEdit) {
        closeEdit.addEventListener('click', function() {
            const modal = document.getElementById('editModal');
            if (modal) modal.style.display = 'none';
        });
    }

    window.addEventListener('click', function(event) {
        const modal = document.getElementById('editModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    });

    const editForm = document.getElementById('edit-expediteur-form');
    if (editForm) {
        editForm.addEventListener('submit', function(event) {
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
                    alert('Contact mis à jour avec succès !');
                    const modal = document.getElementById('editModal');
                    if (modal) modal.style.display = 'none';
                    location.reload();
                } else {
                    alert('Erreur lors de la mise à jour : ' + data.error);
                }
            })
            .catch(error => console.error('Erreur:', error));
        });
    }

    const searchInput = document.getElementById('search-expediteur');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
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
    }
});
</script>
