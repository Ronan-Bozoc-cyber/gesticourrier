import os

base_dir = '/home/ronan/Antigravity-x64/Mes projets/GED'

recherche_php = os.path.join(base_dir, 'recherche.php')
recherche_view = os.path.join(base_dir, 'views', 'recherche.view.php')

with open(recherche_php, 'r', encoding='utf-8') as f:
    lines = f.readlines()

# The HTML starts at line 3 (index 2)
html_content = "".join(lines[2:])

with open(recherche_view, 'w', encoding='utf-8') as f:
    f.write(html_content)

controller_content = """<?php
include 'admin/auth_check.php';
include 'partials/parametres.php';

// Appeler la vue
require_once 'views/recherche.view.php';
"""

with open(recherche_php, 'w', encoding='utf-8') as f:
    f.write(controller_content)

print("recherche.php refactored successfully.")
