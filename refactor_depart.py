import os

base_dir = '/home/ronan/Antigravity-x64/Mes projets/GED'

depart_php = os.path.join(base_dir, 'depart.php')
depart_view = os.path.join(base_dir, 'views', 'depart.view.php')

with open(depart_php, 'r', encoding='utf-8') as f:
    lines = f.readlines()

# The HTML starts at line 19 (index 18)
html_content = "".join(lines[18:])

with open(depart_view, 'w', encoding='utf-8') as f:
    f.write(html_content)

controller_content = """<?php
include 'admin/auth_check.php';
include 'partials/parametres.php';
require_once 'models/CourrierModel.php';

// Récupérer le prochain numéro d'ordre pour l'année en cours
$date = $_GET['date'] ?? date('Y-m-d');
$year = date('Y', strtotime($date));

$nextNumOrdre = CourrierModel::getNextNumOrdreDepart($year);

// Appeler la vue
require_once 'views/depart.view.php';
"""

with open(depart_php, 'w', encoding='utf-8') as f:
    f.write(controller_content)

print("depart.php refactored successfully.")
