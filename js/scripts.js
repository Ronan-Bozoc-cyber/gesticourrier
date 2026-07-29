document.getElementById('expediteur').addEventListener('change', function () {
    let selectedExpediteur = this.value;
    // Fetch and display address based on the selected expediteur
});

document.getElementById('document').addEventListener('change', function () {
    let file = this.files[0];
    if (file && (file.type === 'application/pdf' || file.type.startsWith('image/'))) {
        // Process the file for watermarking and upload
    } else {
        alert('Seuls les fichiers PDF et les images sont autorisés.');
        this.value = '';
    }
});
