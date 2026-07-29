<?php include 'admin/auth_check.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des courriers communaux</title>
    <link rel="icon" type="image/vnd.icon" href="conques.ico">
    <link rel="stylesheet" href="css/style_general.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <script src="/partials/statistiques.js"></script>
</head>
<body>
    <?php include 'partials/header.html'; ?>

    <div class="container">
        <div class="main-content">
                        <h1 style="color:white">Statistiques des courriers</h1>

            <!-- Sélecteur de mois -->
            <div class="month-selector">
                <select id="monthSelector">
                    <?php
                    // Générer les options pour les 12 derniers mois
                    for ($i = 0; $i < 12; $i++) {
                        $month = date('Y-m', strtotime("-$i months"));
                        $monthName = ucfirst(strftime('%B %Y', strtotime("-$i months")));
                        $selected = ($i === 1) ? 'selected' : '';
                        echo "<option value='$month' $selected>$monthName</option>";
                    }
                    ?>
                </select>
                <button onclick="updateCharts()">Mettre à jour</button>
            </div>

            <!-- Courriers - Mois en cours et Mois sélectionné -->
            <div class="chart-row">
                <div class="chart-container">
                    <h2 class="chart-title">Courriers - Mois en cours</h2>
                    <canvas id="courriersCurrentMonthChart"></canvas>
                    <div id="courriersCurrentMonthTable" class="data-table"></div>
                </div>
                <div class="chart-container">
                    <h2 class="chart-title" id="selectedMonthTitle">Courriers - Mois sélectionné</h2>
                    <canvas id="courriersSelectedMonthChart"></canvas>
                    <div id="courriersSelectedMonthTable" class="data-table"></div>
                </div>
            </div>

            <!-- Catégories des courriers entrants -->
            <div class="chart-row">
                <div class="chart-container">
                    <h2 class="chart-title">Catégories des courriers entrants - Mois en cours</h2>
                    <canvas id="categoriesArriveCurrentMonthChart"></canvas>
                    <div id="categoriesArriveCurrentMonthTable" class="data-table"></div>
                </div>
                <div class="chart-container">
                    <h2 class="chart-title" id="categoriesArriveSelectedMonthTitle">Catégories des courriers entrants - Mois sélectionné</h2>
                    <canvas id="categoriesArriveSelectedMonthChart"></canvas>
                    <div id="categoriesArriveSelectedMonthTable" class="data-table"></div>
                </div>
            </div>

            <!-- Catégories des courriers sortants -->
            <div class="chart-row">
                <div class="chart-container">
                    <h2 class="chart-title">Catégories des courriers sortants - Mois en cours</h2>
                    <canvas id="categoriesDepartCurrentMonthChart"></canvas>
                    <div id="categoriesDepartCurrentMonthTable" class="data-table"></div>
                </div>
                <div class="chart-container">
                    <h2 class="chart-title" id="categoriesDepartSelectedMonthTitle">Catégories des courriers sortants - Mois sélectionné</h2>
                    <canvas id="categoriesDepartSelectedMonthChart"></canvas>
                    <div id="categoriesDepartSelectedMonthTable" class="data-table"></div>
                </div>
            </div>
        </div>
    </div>
	<?php include 'partials/stats.html'; ?>
    <?php include 'partials/menu_actif.html'; ?>
</body>
</html>
