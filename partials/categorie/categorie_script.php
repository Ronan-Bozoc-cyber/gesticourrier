<script>
document.addEventListener('DOMContentLoaded', function() {
    const addCategoryBtn = document.getElementById('add-category-btn');
    const addModal = document.getElementById('addModal');
    const addCategoryForm = document.getElementById('add-category-form');
    
    if (addCategoryBtn) {
        addCategoryBtn.addEventListener('click', function() {
            if (addModal) {
                addModal.style.display = 'block';
            }
        });
    }
    
    const closeAdd = document.querySelector('.close-add');
    if (closeAdd) {
        closeAdd.addEventListener('click', function() {
            if (addModal) {
                addModal.style.display = 'none';
            }
        });
    }

    window.addEventListener('click', function(event) {
        if (event.target == addModal) {
            addModal.style.display = 'none';
        }
    });

    if (addCategoryForm) {
        addCategoryForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'add');

            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log(data);  // Ajout d'un log pour déboguer
                if (data.success) {
                    alert('Catégorie ajoutée avec succès !');
                    location.reload();  // Recharger la page pour afficher la nouvelle liste
                } else {
                    alert('Erreur lors de l\'ajout de la catégorie : ' + data.error);
                }
            })
            .catch(error => console.error('Erreur:', error));
        });
    }

    const editBtns = document.querySelectorAll('.edit-btn');
    if (editBtns) {
        editBtns.forEach(button => {
            button.addEventListener('click', function() {
                const modal = document.getElementById('editModal');
                if (modal) {
                    modal.style.display = 'block';
                }
                
                const editId = document.getElementById('edit-id');
                const editName = document.getElementById('edit-name');
                const updateAll = document.getElementById('update-all');
                
                if (editId) editId.value = this.dataset.id;
                if (editName) editName.value = this.dataset.name;
                if (updateAll) updateAll.checked = false;  // Réinitialiser la case à cocher
            });
        });
    }

    const closeEdit = document.querySelector('.close');
    if (closeEdit) {
        closeEdit.addEventListener('click', function() {
            const modal = document.getElementById('editModal');
            if (modal) {
                modal.style.display = 'none';
            }
        });
    }

    window.addEventListener('click', function(event) {
        const modal = document.getElementById('editModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    });

    const editCategoryForm = document.getElementById('edit-category-form');
    if (editCategoryForm) {
        editCategoryForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'update');

            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log(data);  // Ajout d'un log pour déboguer
                if (data.success) {
                    alert('Catégorie mise à jour avec succès !');
                    const modal = document.getElementById('editModal');
                    if (modal) {
                        modal.style.display = 'none';
                    }
                    location.reload();  // Recharger la page pour afficher la nouvelle liste
                } else {
                    alert('Erreur lors de la mise à jour de la catégorie : ' + data.error);
                }
            })
            .catch(error => console.error('Erreur:', error));
        });
    }

    const deleteBtns = document.querySelectorAll('.delete-btn');
    if (deleteBtns) {
        deleteBtns.forEach(button => {
            button.addEventListener('click', function() {
                const categoryId = this.dataset.id;

                if (confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?')) {
                    const formData = new FormData();
                    formData.append('action', 'delete');
                    formData.append('id', categoryId);

                    fetch('', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log(data);  // Ajout d'un log pour déboguer
                        if (data.success) {
                            alert('Catégorie supprimée avec succès !');
                            location.reload();  // Recharger la page pour afficher la nouvelle liste
                        } else {
                            alert('Erreur lors de la suppression de la catégorie : ' + data.error);
                        }
                    })
                    .catch(error => console.error('Erreur:', error));
                }
            });
        });
    }

    const searchCategory = document.getElementById('search-category');
    if (searchCategory) {
        searchCategory.addEventListener('input', function() {
            const searchValue = this.value.toLowerCase();
            const rows = document.querySelectorAll('#category-table tbody tr');

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
