import os

base_dir = '/home/ronan/Antigravity-x64/Mes projets/GED'

arrive_php = os.path.join(base_dir, 'arrive.php')
arrive_view = os.path.join(base_dir, 'views', 'arrive.view.php')

with open(arrive_php, 'r', encoding='utf-8') as f:
    lines = f.readlines()

# The HTML starts at line 19 (index 18)
html_content = "".join(lines[18:])

with open(arrive_view, 'w', encoding='utf-8') as f:
    f.write(html_content)

controller_content = """<?php
include 'admin/auth_check.php';
include 'partials/parametres.php';
require_once 'models/CourrierModel.php';

// Récupérer le prochain numéro d'ordre pour l'année en cours
$date = $_GET['date'] ?? date('Y-m-d');
$year = date('Y', strtotime($date));

$nextNumOrdre = CourrierModel::getNextNumOrdreArrive($year);

// Appeler la vue
require_once 'views/arrive.view.php';
"""

with open(arrive_php, 'w', encoding='utf-8') as f:
    f.write(controller_content)

print("arrive.php refactored successfully.")
