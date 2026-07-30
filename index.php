<?php include 'admin/auth_check.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - OpenGestiCourrier</title>
    <link rel="icon" type="image/vnd.icon" href="conques.ico">
    <link rel="stylesheet" href="css/style_general.css">
    <link rel="stylesheet" href="css/arrive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <style>
        .dashboard-kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 18px;
            margin-bottom: 24px;
        }

        .kpi-card-link {
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .kpi-card {
            background: #ffffff;
            border-radius: 14px;
            padding: 20px 24px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.25s ease;
            cursor: pointer;
        }

        .kpi-card-link:hover .kpi-card {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
        }

        .kpi-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }

        .chart-row-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
            gap: 22px;
            margin-bottom: 24px;
        }

        @media (max-width: 950px) {
            .chart-row-grid {
                grid-template-columns: 1fr;
            }
        }

        .chart-card {
            background: #ffffff;
            border-radius: 14px;
            padding: 24px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            transition: all 0.25s ease;
        }

        .chart-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1.5px solid #f1f5f9;
        }

        .chart-card-header h2 {
            font-size: 1.1rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
    </style>
</head>
<body>
    <?php include 'partials/header.html'; ?>

    <div class="main-container">
        <!-- 4 Cartes Raccourcis Cliquables (Placées AU-DESSUS du titre Tableau de bord) -->
        <div class="dashboard-kpi-grid" style="margin-top: 10px;">
            <a href="<?php echo $urllogiciel; ?>arrive.php" class="kpi-card-link" title="Accéder aux courriers entrants">
                <div class="kpi-card" style="border-top: 4px solid #f59e0b;">
                    <div>
                        <div style="font-size: 0.85rem; font-weight: 600; color: #64748b; text-transform: uppercase;">Courriers Entrants</div>
                        <div style="font-size: 1.3rem; font-weight: 800; color: #d97706; margin-top: 4px;">📥 Arrivé</div>
                    </div>
                    <div class="kpi-icon" style="background: #fef3c7; color: #d97706;">
                        <i class="fas fa-inbox"></i>
                    </div>
                </div>
            </a>

            <a href="<?php echo $urllogiciel; ?>depart.php" class="kpi-card-link" title="Accéder aux courriers sortants">
                <div class="kpi-card" style="border-top: 4px solid #10b981;">
                    <div>
                        <div style="font-size: 0.85rem; font-weight: 600; color: #64748b; text-transform: uppercase;">Courriers Sortants</div>
                        <div style="font-size: 1.3rem; font-weight: 800; color: #059669; margin-top: 4px;">📤 Départ</div>
                    </div>
                    <div class="kpi-icon" style="background: #d1fae5; color: #059669;">
                        <i class="fas fa-paper-plane"></i>
                    </div>
                </div>
            </a>

            <a href="<?php echo $urllogiciel; ?>contact.php" class="kpi-card-link" title="Accéder aux contacts">
                <div class="kpi-card" style="border-top: 4px solid #2563eb;">
                    <div>
                        <div style="font-size: 0.85rem; font-weight: 600; color: #64748b; text-transform: uppercase;">Contacts Référencés</div>
                        <div style="font-size: 1.3rem; font-weight: 800; color: #2563eb; margin-top: 4px;">📇 Contacts</div>
                    </div>
                    <div class="kpi-icon" style="background: #dbeafe; color: #2563eb;">
                        <i class="fas fa-address-book"></i>
                    </div>
                </div>
            </a>

            <a href="<?php echo $urllogiciel; ?>categorie.php" class="kpi-card-link" title="Accéder aux catégories">
                <div class="kpi-card" style="border-top: 4px solid #8b5cf6;">
                    <div>
                        <div style="font-size: 0.85rem; font-weight: 600; color: #64748b; text-transform: uppercase;">Catégories Actives</div>
                        <div style="font-size: 1.3rem; font-weight: 800; color: #7c3aed; margin-top: 4px;">🗂️ Catégories</div>
                    </div>
                    <div class="kpi-icon" style="background: #ede9fe; color: #7c3aed;">
                        <i class="fas fa-tags"></i>
                    </div>
                </div>
            </a>
        </div>

        <!-- Barre d'action et Titre (AU-DESSOUS des 4 cartes raccourcis) -->
        <div class="page-action-bar">
            <div class="page-title-badge" style="color: #2563eb;">
                <i class="fas fa-chart-pie"></i> Tableau de bord & Statistiques
            </div>

            <!-- Sélecteur de mois -->
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <label for="monthSelector" style="font-weight: 600; color: #334155; font-size: 0.9rem;">Période :</label>
                <select id="monthSelector" style="padding: 9px 14px; border-radius: 8px; border: 1.5px solid #cbd5e1; font-size: 0.92rem; background: #f8fafc; font-weight: 600; color: #0f172a;">
                    <?php
                    for ($i = 0; $i < 12; $i++) {
                        $month = date('Y-m', strtotime("-$i months"));
                        $monthName = ucfirst(strftime('%B %Y', strtotime("-$i months")));
                        $selected = ($i === 1) ? 'selected' : '';
                        echo "<option value='$month' $selected>$monthName</option>";
                    }
                    ?>
                </select>
                <button onclick="updateCharts()" class="btn-action-search" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);">
                    <i class="fas fa-sync-alt"></i> Mettre à jour
                </button>
            </div>
        </div>

        <!-- Ligne Graphiques 1 : Volumétrie des Courriers -->
        <div class="chart-row-grid">
            <div class="chart-card" style="border-top: 4px solid #2563eb;">
                <div class="chart-card-header">
                    <h2 style="color: #2563eb;"><i class="fas fa-chart-pie"></i> Courriers - Mois en cours</h2>
                </div>
                <div style="max-width: 320px; margin: 0 auto;">
                    <canvas id="courriersCurrentMonthChart"></canvas>
                </div>
                <div id="courriersCurrentMonthTable"></div>
            </div>

            <div class="chart-card" style="border-top: 4px solid #2563eb;">
                <div class="chart-card-header">
                    <h2 style="color: #2563eb;" id="selectedMonthTitle"><i class="fas fa-chart-pie"></i> Courriers - Mois sélectionné</h2>
                </div>
                <div style="max-width: 320px; margin: 0 auto;">
                    <canvas id="courriersSelectedMonthChart"></canvas>
                </div>
                <div id="courriersSelectedMonthTable"></div>
            </div>
        </div>

        <!-- Ligne Graphiques 2 : Catégories Entrants (Thème Ambré / Jaune) -->
        <div class="chart-row-grid">
            <div class="chart-card" style="border-top: 4px solid #f59e0b;">
                <div class="chart-card-header">
                    <h2 style="color: #d97706;"><i class="fas fa-inbox"></i> Catégories courriers entrants - Mois en cours</h2>
                </div>
                <div style="max-width: 320px; margin: 0 auto;">
                    <canvas id="categoriesArriveCurrentMonthChart"></canvas>
                </div>
                <div id="categoriesArriveCurrentMonthTable"></div>
            </div>

            <div class="chart-card" style="border-top: 4px solid #f59e0b;">
                <div class="chart-card-header">
                    <h2 style="color: #d97706;" id="categoriesArriveSelectedMonthTitle"><i class="fas fa-inbox"></i> Catégories courriers entrants - Mois sélectionné</h2>
                </div>
                <div style="max-width: 320px; margin: 0 auto;">
                    <canvas id="categoriesArriveSelectedMonthChart"></canvas>
                </div>
                <div id="categoriesArriveSelectedMonthTable"></div>
            </div>
        </div>

        <!-- Ligne Graphiques 3 : Catégories Sortants (Thème Vert) -->
        <div class="chart-row-grid">
            <div class="chart-card" style="border-top: 4px solid #10b981;">
                <div class="chart-card-header">
                    <h2 style="color: #059669;"><i class="fas fa-paper-plane"></i> Catégories courriers sortants - Mois en cours</h2>
                </div>
                <div style="max-width: 320px; margin: 0 auto;">
                    <canvas id="categoriesDepartCurrentMonthChart"></canvas>
                </div>
                <div id="categoriesDepartCurrentMonthTable"></div>
            </div>

            <div class="chart-card" style="border-top: 4px solid #10b981;">
                <div class="chart-card-header">
                    <h2 style="color: #059669;" id="categoriesDepartSelectedMonthTitle"><i class="fas fa-paper-plane"></i> Catégories courriers sortants - Mois sélectionné</h2>
                </div>
                <div style="max-width: 320px; margin: 0 auto;">
                    <canvas id="categoriesDepartSelectedMonthChart"></canvas>
                </div>
                <div id="categoriesDepartSelectedMonthTable"></div>
            </div>
        </div>
    </div>

    <?php include 'partials/stats.html'; ?>
    <?php include 'partials/menu_actif.html'; ?>
</body>
</html>
