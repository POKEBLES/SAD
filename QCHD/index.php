<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QCHD Spatial Analytics Platform | Health Vulnerability & Risk Assessment</title>
    <!-- Load Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Inter Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <!-- Chart.js for Analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <!-- Leaflet for Maps -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            /* New Color Palette */
            --primary-red: #C1495B;
            --dark-navy: #1F2937;
            --medium-blue: #2F4B66;
            --teal: #5C8D89;
            
            /* Risk Level Colors (maintained) */
            --risk-critical: #ef4444;
            --risk-high: #f97316;
            --risk-medium: #f59e0b;
            --risk-low: #10b981;
            
            --bg-light: #f8fafc;
        }
        
        body { 
            font-family: 'Inter', sans-serif; 
            background-color: var(--bg-light);
            margin: 0;
            padding: 0;
        }
        
        .card { 
            transition: all 0.3s ease; 
            border-radius: 0.75rem; 
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06); 
            background: white;
        }
        
        .card:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); 
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary-red) 0%, var(--medium-blue) 100%);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .nav-item {
            position: relative;
            padding: 0.75rem 1.25rem;
            color: rgba(255, 255, 255, 0.9);
            transition: all 0.3s ease;
            cursor: pointer;
            border-radius: 0.5rem;
        }
        
        .nav-item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .nav-item.active {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            font-weight: 600;
        }
        
        .nav-item.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60%;
            height: 3px;
            background: white;
            border-radius: 3px 3px 0 0;
        }
        
        .section-content {
            display: none;
        }
        
        .section-content.active {
            display: block;
            animation: fadeIn 0.3s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .kpi-card {
            border-left: 4px solid;
            padding: 1.5rem;
        }
        
        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            line-height: 1;
        }
        
        #map { height: 500px; width: 100%; border-radius: 0.75rem; }
        
        .progress-bar {
            height: 8px;
            border-radius: 9999px;
            overflow: hidden;
            background: #e5e7eb;
        }
        
        .progress-fill {
            height: 100%;
            transition: width 0.5s ease;
        }
        
        .tooltip-info {
            position: relative;
            cursor: help;
        }
        
        .tooltip-info:hover::after {
            content: attr(data-tooltip);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            padding: 0.5rem 1rem;
            background: rgba(0, 0, 0, 0.9);
            color: white;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            white-space: nowrap;
            z-index: 1000;
        }
    </style>
</head>
<body>

    <!-- Top Navigation Bar -->
    <nav class="navbar sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo & Title -->
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-map-marked-alt text-white text-2xl"></i>
                        <div>
                            <h1 class="text-white text-lg font-bold leading-tight">QCHD Spatial Analytics</h1>
                            <p class="text-white/80 text-xs">Health Vulnerability & Risk Assessment</p>
                        </div>
                    </div>
                </div>
                
                <!-- Navigation Links -->
                <div class="hidden md:flex space-x-2">
                    <div class="nav-item active" onclick="switchSection('dashboard')">
                        <i class="fas fa-chart-line mr-2"></i>Dashboard
                    </div>
                    <div class="nav-item" onclick="switchSection('hvi-analysis')">
                        <i class="fas fa-calculator mr-2"></i>HVI Analysis
                    </div>
                    <div class="nav-item" onclick="switchSection('hotspots')">
                        <i class="fas fa-fire mr-2"></i>Hotspots (DBSCAN)
                    </div>
                    <div class="nav-item" onclick="switchSection('reports')">
                        <i class="fas fa-file-alt mr-2"></i>Reports
                    </div>
                </div>
                
                <!-- User Info -->
                <div class="flex items-center space-x-4">
                    <select id="diseaseSelect" class="px-3 py-2 bg-white/10 border border-white/20 rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-white/50">
                        <option value="dengue" class="text-gray-800">Dengue Fever</option>
                        <option value="tb" class="text-gray-800">Tuberculosis</option>
                        <option value="diarrhea" class="text-gray-800">Diarrhea</option>
                    </select>
                    <div class="hidden md:flex items-center space-x-2 text-white">
                        <i class="fas fa-user-circle text-2xl"></i>
                        <span class="text-sm">Admin</span>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        
        <!-- ============================================= -->
        <!-- SECTION 1: DASHBOARD (DEFAULT VIEW) -->
        <!-- ============================================= -->
        <div id="dashboard-section" class="section-content active">
            
            <!-- Page Header -->
            <div class="mb-6">
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Disease Surveillance Dashboard</h2>
                <p class="text-gray-600">Monitor disease patterns, spatial risk, and health vulnerability across all 142 barangays in Quezon City.</p>
            </div>
            
            <!-- KPI Cards Row -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- HVI Score -->
                <div class="card kpi-card" style="border-left-color: var(--teal);">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <p class="text-gray-500 text-sm font-medium uppercase tracking-wide">Health Vulnerability Index</p>
                            <div class="flex items-baseline mt-2">
                                <span class="stat-number" style="color: var(--teal);" id="hviScore">78.4</span>
                                <span class="ml-2 text-sm text-gray-500">/100</span>
                            </div>
                        </div>
                        <div class="p-3 rounded-lg" style="background-color: rgba(92, 141, 137, 0.1);">
                            <i class="fas fa-shield-virus text-2xl" style="color: var(--teal);"></i>
                        </div>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="background-color: var(--teal);" id="hviProgress"></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2"><i class="fas fa-info-circle mr-1"></i>Calculated via GWR model</p>
                </div>
                
                <!-- DBSCAN Hotspot Clusters -->
                <div class="card kpi-card" style="border-left-color: var(--primary-red);">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <p class="text-gray-500 text-sm font-medium uppercase tracking-wide">Active Hotspots</p>
                            <div class="flex items-baseline mt-2">
                                <span class="stat-number" style="color: var(--primary-red);" id="hotspotsCount">7</span>
                                <span class="ml-2 text-sm text-gray-500">clusters</span>
                            </div>
                        </div>
                        <div class="p-3 rounded-lg" style="background-color: rgba(193, 73, 91, 0.1);">
                            <i class="fas fa-fire text-2xl" style="color: var(--primary-red);"></i>
                        </div>
                    </div>
                    <span class="badge text-white" style="background-color: var(--primary-red);">
                        <i class="fas fa-exclamation-triangle mr-1"></i>DBSCAN Detection
                    </span>
                    <p class="text-xs text-gray-500 mt-2"><i class="fas fa-chart-area mr-1"></i>Density-based clustering</p>
                </div>
                
                <!-- Total Cases -->
                <div class="card kpi-card" style="border-left-color: var(--medium-blue);">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <p class="text-gray-500 text-sm font-medium uppercase tracking-wide">Total Cases (7 Days)</p>
                            <div class="flex items-baseline mt-2">
                                <span class="stat-number" style="color: var(--medium-blue);" id="totalCases">179</span>
                            </div>
                        </div>
                        <div class="p-3 rounded-lg" style="background-color: rgba(47, 75, 102, 0.1);">
                            <i class="fas fa-users text-2xl" style="color: var(--medium-blue);"></i>
                        </div>
                    </div>
                    <div class="flex items-center text-sm">
                        <span class="text-green-600 font-semibold"><i class="fas fa-arrow-down mr-1"></i>-12.5%</span>
                        <span class="text-gray-500 ml-2">vs. last week</span>
                    </div>
                </div>
                
                <!-- Primary Risk Driver -->
                <div class="card kpi-card" style="border-left-color: var(--dark-navy);">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <p class="text-gray-500 text-sm font-medium uppercase tracking-wide">Primary Risk Driver</p>
                            <div class="mt-2">
                                <span class="text-xl font-bold" style="color: var(--dark-navy);" id="riskDriver">Population Density</span>
                            </div>
                        </div>
                        <div class="p-3 rounded-lg" style="background-color: rgba(31, 41, 55, 0.1);">
                            <i class="fas fa-chart-bar text-2xl" style="color: var(--dark-navy);"></i>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500"><i class="fas fa-microscope mr-1"></i>Spatial regression coefficient: 0.67</p>
                </div>
            </div>
            
            <!-- 2-Column Layout: Map & Chart -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Interactive Map -->
                <div class="card p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-bold text-gray-800">
                            <i class="fas fa-map-marked-alt mr-2" style="color: var(--teal);"></i>Spatial Visualization
                        </h3>
                        <div class="flex space-x-2">
                            <button class="px-3 py-1 text-xs text-white rounded-lg hover:opacity-90 transition" style="background-color: var(--teal);" onclick="toggleMapLayer('hvi')">
                                <i class="fas fa-layer-group mr-1"></i>HVI Layer
                            </button>
                            <button class="px-3 py-1 text-xs text-white rounded-lg hover:opacity-90 transition" style="background-color: var(--primary-red);" onclick="toggleMapLayer('hotspots')">
                                <i class="fas fa-circle-dot mr-1"></i>Hotspots
                            </button>
                        </div>
                    </div>
                    <div id="map" class="rounded-lg shadow-inner"></div>
                </div>
                
                <!-- Trend Chart -->
                <div class="card p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">
                        <i class="fas fa-chart-line mr-2" style="color: var(--medium-blue);"></i>7-Day Case Trend
                    </h3>
                    <div style="height: 320px;">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Data Table -->
            <div class="card p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-gray-800">
                        <i class="fas fa-table text-gray-600 mr-2"></i>Top 10 High-Risk Barangays
                    </h3>
                    <input type="text" placeholder="Search barangays..." class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:border-transparent" style="focus:ring-color: var(--teal);" id="barangaySearch">
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full" id="barangayTable">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Barangay</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">HVI Score</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cases (7D)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Risk Level</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="tableBody">
                            <!-- Dynamic content populated by JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ============================================= -->
        <!-- SECTION 2: HVI ANALYSIS (GWR MODEL) -->
        <!-- ============================================= -->
        <div id="hvi-analysis-section" class="section-content">
            <div class="mb-6">
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Health Vulnerability Index (HVI) Analysis</h2>
                <p class="text-gray-600">Geographically Weighted Regression (GWR) model results showing spatial drivers of disease vulnerability across 142 barangays.</p>
            </div>

            <!-- GWR Coefficients -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="card p-6">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Population Density</h3>
                    <div class="flex items-baseline">
                        <span class="text-4xl font-bold" style="color: var(--medium-blue);">0.67</span>
                        <span class="ml-2 text-sm text-gray-500">coefficient</span>
                    </div>
                    <div class="mt-3">
                        <div class="progress-bar">
                            <div class="progress-fill" style="background-color: var(--medium-blue); width: 67%;"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Strongest predictor of disease risk</p>
                    </div>
                </div>

                <div class="card p-6">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Water & Sanitation</h3>
                    <div class="flex items-baseline">
                        <span class="text-4xl font-bold" style="color: var(--teal);">0.54</span>
                        <span class="ml-2 text-sm text-gray-500">coefficient</span>
                    </div>
                    <div class="mt-3">
                        <div class="progress-bar">
                            <div class="progress-fill" style="background-color: var(--teal); width: 54%;"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Infrastructure access impact</p>
                    </div>
                </div>

                <div class="card p-6">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Poverty Index</h3>
                    <div class="flex items-baseline">
                        <span class="text-4xl font-bold" style="color: var(--primary-red);">0.48</span>
                        <span class="ml-2 text-sm text-gray-500">coefficient</span>
                    </div>
                    <div class="mt-3">
                        <div class="progress-bar">
                            <div class="progress-fill" style="background-color: var(--primary-red); width: 48%;"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Socioeconomic vulnerability</p>
                    </div>
                </div>
            </div>

            <!-- HVI Map & Barangay Rankings -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="card p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">
                        <i class="fas fa-map mr-2" style="color: var(--teal);"></i>HVI Spatial Distribution
                    </h3>
                    <div id="hviMap" style="height: 400px;" class="rounded-lg"></div>
                    <div class="mt-4 flex items-center justify-between text-xs">
                        <span class="flex items-center"><span class="w-4 h-4 bg-red-500 rounded mr-2"></span>High Risk (80-100)</span>
                        <span class="flex items-center"><span class="w-4 h-4 bg-amber-500 rounded mr-2"></span>Moderate (50-79)</span>
                        <span class="flex items-center"><span class="w-4 h-4 bg-green-500 rounded mr-2"></span>Low (<50)</span>
                    </div>
                </div>

                <div class="card p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">
                        <i class="fas fa-ranking-star mr-2" style="color: var(--dark-navy);"></i>Top 10 Most Vulnerable Barangays
                    </h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                            <div>
                                <p class="font-semibold text-gray-800">1. Payatas</p>
                                <p class="text-xs text-gray-500">District 2</p>
                            </div>
                            <span class="badge bg-red-600 text-white text-lg px-4 py-2">92.3</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                            <div>
                                <p class="font-semibold text-gray-800">2. Bagong Silangan</p>
                                <p class="text-xs text-gray-500">District 2</p>
                            </div>
                            <span class="badge bg-red-600 text-white text-lg px-4 py-2">89.7</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                            <div>
                                <p class="font-semibold text-gray-800">3. Commonwealth</p>
                                <p class="text-xs text-gray-500">District 1</p>
                            </div>
                            <span class="badge bg-red-500 text-white text-lg px-4 py-2">85.2</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-amber-50 rounded-lg">
                            <div>
                                <p class="font-semibold text-gray-800">4. Batasan Hills</p>
                                <p class="text-xs text-gray-500">District 2</p>
                            </div>
                            <span class="badge bg-amber-600 text-white text-lg px-4 py-2">78.9</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-amber-50 rounded-lg">
                            <div>
                                <p class="font-semibold text-gray-800">5. Fairview</p>
                                <p class="text-xs text-gray-500">District 5</p>
                            </div>
                            <span class="badge bg-amber-500 text-white text-lg px-4 py-2">75.4</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================= -->
        <!-- SECTION 3: HOTSPOTS (DBSCAN CLUSTERING) -->
        <!-- ============================================= -->
        <div id="hotspots-section" class="section-content">
            <div class="mb-6">
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Disease Hotspot Detection (DBSCAN)</h2>
                <p class="text-gray-600">Density-based spatial clustering identifies continuous and emerging high-risk zones across Quezon City.</p>
            </div>

            <!-- Cluster Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="card p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 uppercase font-medium">Total Clusters</p>
                            <p class="text-3xl font-bold text-red-600 mt-1">7</p>
                        </div>
                        <i class="fas fa-layer-group text-red-200 text-4xl"></i>
                    </div>
                </div>
                <div class="card p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 uppercase font-medium">Continuous</p>
                            <p class="text-3xl font-bold text-orange-600 mt-1">4</p>
                        </div>
                        <i class="fas fa-fire-flame-curved text-orange-200 text-4xl"></i>
                    </div>
                </div>
                <div class="card p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 uppercase font-medium">Emerging</p>
                            <p class="text-3xl font-bold text-amber-600 mt-1">3</p>
                        </div>
                        <i class="fas fa-triangle-exclamation text-amber-200 text-4xl"></i>
                    </div>
                </div>
                <div class="card p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 uppercase font-medium">Affected Brgy.</p>
                            <p class="text-3xl font-bold text-blue-600 mt-1">23</p>
                        </div>
                        <i class="fas fa-map-pin text-blue-200 text-4xl"></i>
                    </div>
                </div>
            </div>

            <!-- Cluster Details & Map -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="card p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">
                        <i class="fas fa-table-cells mr-2" style="color: var(--primary-red);"></i>Cluster Details
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cluster ID</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Barangays</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cases</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-mono text-sm font-semibold">C-001</td>
                                    <td class="px-4 py-3 text-sm">Payatas, Batasan Hills</td>
                                    <td class="px-4 py-3 text-sm font-bold text-red-600">58</td>
                                    <td class="px-4 py-3"><span class="badge bg-red-600 text-white">Continuous</span></td>
                                </tr>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-mono text-sm font-semibold">C-002</td>
                                    <td class="px-4 py-3 text-sm">Bagong Silangan</td>
                                    <td class="px-4 py-3 text-sm font-bold text-red-600">42</td>
                                    <td class="px-4 py-3"><span class="badge bg-red-600 text-white">Continuous</span></td>
                                </tr>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-mono text-sm font-semibold">C-003</td>
                                    <td class="px-4 py-3 text-sm">Commonwealth, Fairview</td>
                                    <td class="px-4 py-3 text-sm font-bold text-orange-600">35</td>
                                    <td class="px-4 py-3"><span class="badge bg-orange-500 text-white">Continuous</span></td>
                                </tr>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-mono text-sm font-semibold">C-004</td>
                                    <td class="px-4 py-3 text-sm">Novaliches, San Bartolome</td>
                                    <td class="px-4 py-3 text-sm font-bold text-amber-600">28</td>
                                    <td class="px-4 py-3"><span class="badge bg-amber-500 text-white">Emerging</span></td>
                                </tr>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-mono text-sm font-semibold">C-005</td>
                                    <td class="px-4 py-3 text-sm">Tandang Sora</td>
                                    <td class="px-4 py-3 text-sm font-bold text-amber-600">22</td>
                                    <td class="px-4 py-3"><span class="badge bg-amber-500 text-white">Emerging</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">
                        <i class="fas fa-map-location-dot mr-2" style="color: var(--primary-red);"></i>Cluster Spatial Distribution
                    </h3>
                    <div id="hotspotsMap" style="height: 400px;" class="rounded-lg"></div>
                </div>
            </div>
        </div>

        <!-- ============================================= -->
        <!-- SECTION 4: REPORTS -->
        <!-- ============================================= -->
        <div id="reports-section" class="section-content">
            <div class="mb-6">
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Predictive Reports & Analytics</h2>
                <p class="text-gray-600">Download comprehensive reports on disease surveillance, spatial risk assessment, and predictive modeling.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Report Cards -->
                <div class="card p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">Weekly Surveillance Report</h3>
                            <p class="text-sm text-gray-500 mt-1">Disease case trends and geographic distribution</p>
                        </div>
                        <i class="fas fa-file-pdf text-red-400 text-3xl"></i>
                    </div>
                    <div class="flex items-center justify-between mt-4">
                        <span class="text-xs text-gray-500">Last updated: 2 hours ago</span>
                        <button class="px-4 py-2 text-white rounded-lg transition" style="background-color: var(--teal);" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                            <i class="fas fa-download mr-2"></i>Download PDF
                        </button>
                    </div>
                </div>

                <div class="card p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">HVI Assessment Report</h3>
                            <p class="text-sm text-gray-500 mt-1">Comprehensive vulnerability analysis for all 142 barangays</p>
                        </div>
                        <i class="fas fa-file-chart-line text-blue-400 text-3xl"></i>
                    </div>
                    <div class="flex items-center justify-between mt-4">
                        <span class="text-xs text-gray-500">Last updated: Yesterday</span>
                        <button class="px-4 py-2 text-white rounded-lg transition" style="background-color: var(--teal);" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                            <i class="fas fa-download mr-2"></i>Download PDF
                        </button>
                    </div>
                </div>

                <div class="card p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">DBSCAN Hotspot Analysis</h3>
                            <p class="text-sm text-gray-500 mt-1">Cluster identification and temporal tracking</p>
                        </div>
                        <i class="fas fa-file-code text-purple-400 text-3xl"></i>
                    </div>
                    <div class="flex items-center justify-between mt-4">
                        <span class="text-xs text-gray-500">Last updated: 6 hours ago</span>
                        <button class="px-4 py-2 text-white rounded-lg transition" style="background-color: var(--teal);" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                            <i class="fas fa-download mr-2"></i>Download CSV
                        </button>
                    </div>
                </div>

                <div class="card p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">GWR Spatial Regression Results</h3>
                            <p class="text-sm text-gray-500 mt-1">Coefficient maps and statistical summary</p>
                        </div>
                        <i class="fas fa-file-excel text-green-400 text-3xl"></i>
                    </div>
                    <div class="flex items-center justify-between mt-4">
                        <span class="text-xs text-gray-500">Last updated: Yesterday</span>
                        <button class="px-4 py-2 text-white rounded-lg transition" style="background-color: var(--teal);" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                            <i class="fas fa-download mr-2"></i>Download Excel
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <!-- JavaScript Logic -->
    <script>
        // Mock Disease Data
        const diseaseData = {
            dengue: {
                name: "Dengue Fever",
                hvi: 78.4,
                hotspots: 7,
                riskDriver: "Poor Sanitation & Stagnant Water",
                totalCases: 179,
                cases: [
                    { date: '2025-01-06', count: 18 },
                    { date: '2025-01-07', count: 21 },
                    { date: '2025-01-08', count: 25 },
                    { date: '2025-01-09', count: 22 },
                    { date: '2025-01-10', count: 30 },
                    { date: '2025-01-11', count: 28 },
                    { date: '2025-01-12', count: 35 }
                ],
                color: '#ef4444',
                barangays: [
                    { name: 'Payatas', hvi: 92.3, cases: 35, risk: 'Critical' },
                    { name: 'Bagong Silangan', hvi: 89.7, cases: 28, risk: 'Critical' },
                    { name: 'Commonwealth', hvi: 85.2, cases: 22, risk: 'High' },
                    { name: 'Batasan Hills', hvi: 78.9, cases: 18, risk: 'High' },
                    { name: 'Fairview', hvi: 75.4, cases: 15, risk: 'High' },
                    { name: 'Novaliches', hvi: 68.3, cases: 12, risk: 'Medium' },
                    { name: 'Project 4', hvi: 65.8, cases: 11, risk: 'Medium' },
                    { name: 'Tandang Sora', hvi: 62.1, cases: 9, risk: 'Medium' },
                    { name: 'Culiat', hvi: 58.7, cases: 7, risk: 'Medium' },
                    { name: 'San Bartolome', hvi: 55.2, cases: 6, risk: 'Low' }
                ]
            },
            tb: {
                name: "Tuberculosis",
                hvi: 62.8,
                hotspots: 5,
                riskDriver: "Population Density",
                totalCases: 79,
                cases: [
                    { date: '2025-01-06', count: 8 },
                    { date: '2025-01-07', count: 10 },
                    { date: '2025-01-08', count: 9 },
                    { date: '2025-01-09', count: 12 },
                    { date: '2025-01-10', count: 11 },
                    { date: '2025-01-11', count: 15 },
                    { date: '2025-01-12', count: 14 }
                ],
                color: '#3b82f6',
                barangays: [
                    { name: 'Tatalon', hvi: 88.5, cases: 18, risk: 'Critical' },
                    { name: 'Gulod', hvi: 82.3, cases: 14, risk: 'High' },
                    { name: 'Payatas', hvi: 79.1, cases: 12, risk: 'High' },
                    { name: 'Tandang Sora', hvi: 74.8, cases: 9, risk: 'High' },
                    { name: 'Bagong Silangan', hvi: 68.4, cases: 7, risk: 'Medium' },
                    { name: 'Batasan Hills', hvi: 62.9, cases: 5, risk: 'Medium' },
                    { name: 'Commonwealth', hvi: 58.6, cases: 4, risk: 'Medium' },
                    { name: 'Fairview', hvi: 54.2, cases: 3, risk: 'Low' },
                    { name: 'Novaliches', hvi: 51.8, cases: 2, risk: 'Low' },
                    { name: 'Culiat', hvi: 48.3, cases: 2, risk: 'Low' }
                ]
            },
            diarrhea: {
                name: "Diarrhea",
                hvi: 55.3,
                hotspots: 4,
                riskDriver: "Water & Sanitation Access",
                totalCases: 112,
                cases: [
                    { date: '2025-01-06', count: 12 },
                    { date: '2025-01-07', count: 15 },
                    { date: '2025-01-08', count: 18 },
                    { date: '2025-01-09', count: 14 },
                    { date: '2025-01-10', count: 19 },
                    { date: '2025-01-11', count: 16 },
                    { date: '2025-01-12', count: 18 }
                ],
                color: '#10b981',
                barangays: [
                    { name: 'Payatas', hvi: 84.6, cases: 22, risk: 'Critical' },
                    { name: 'Bagong Silangan', hvi: 78.2, cases: 18, risk: 'High' },
                    { name: 'Commonwealth', hvi: 72.9, cases: 14, risk: 'High' },
                    { name: 'Batasan Hills', hvi: 68.5, cases: 11, risk: 'Medium' },
                    { name: 'Fairview', hvi: 64.1, cases: 9, risk: 'Medium' },
                    { name: 'Novaliches', hvi: 59.7, cases: 8, risk: 'Medium' },
                    { name: 'Project 4', hvi: 55.3, cases: 7, risk: 'Low' },
                    { name: 'Tandang Sora', hvi: 52.8, cases: 6, risk: 'Low' },
                    { name: 'Culiat', hvi: 49.4, cases: 5, risk: 'Low' },
                    { name: 'San Bartolome', hvi: 46.1, cases: 4, risk: 'Low' }
                ]
            }
        };

        // Global variables
        let trendChart = null;
        let mainMap = null;
        let hviMap = null;
        let hotspotsMap = null;

        // Section Navigation
        function switchSection(sectionId) {
            // Hide all sections
            document.querySelectorAll('.section-content').forEach(section => {
                section.classList.remove('active');
            });
            
            // Remove active from all nav items
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active');
            });
            
            // Show selected section
            const targetSection = document.getElementById(`${sectionId}-section`);
            if (targetSection) {
                targetSection.classList.add('active');
                
                // Add active to clicked nav item
                event.target.classList.add('active');
                
                // Initialize maps when section becomes visible
                if (sectionId === 'dashboard' && !mainMap) {
                    setTimeout(() => initMainMap(), 100);
                } else if (sectionId === 'hvi-analysis' && !hviMap) {
                    setTimeout(() => initHviMap(), 100);
                } else if (sectionId === 'hotspots' && !hotspotsMap) {
                    setTimeout(() => initHotspotsMap(), 100);
                }
                
                // Invalidate size for existing maps
                if (mainMap) mainMap.invalidateSize();
                if (hviMap) hviMap.invalidateSize();
                if (hotspotsMap) hotspotsMap.invalidateSize();
            }
        }

        // Initialize Main Dashboard Map
        function initMainMap() {
            if (!document.getElementById('map')) return;
            
            mainMap = L.map('map').setView([14.6760, 121.0437], 12);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(mainMap);
            
            // Add sample markers
            const markers = [
                { lat: 14.7200, lng: 121.0800, name: 'Payatas', hvi: 92.3, cases: 35 },
                { lat: 14.7100, lng: 121.1000, name: 'Bagong Silangan', hvi: 89.7, cases: 28 },
                { lat: 14.6900, lng: 121.0500, name: 'Commonwealth', hvi: 85.2, cases: 22 },
                { lat: 14.6850, lng: 121.1100, name: 'Batasan Hills', hvi: 78.9, cases: 18 },
                { lat: 14.7050, lng: 121.0600, name: 'Fairview', hvi: 75.4, cases: 15 }
            ];
            
            markers.forEach(marker => {
                const color = marker.hvi >= 80 ? '#ef4444' : marker.hvi >= 60 ? '#f59e0b' : '#10b981';
                L.circleMarker([marker.lat, marker.lng], {
                    radius: 12,
                    fillColor: color,
                    color: '#fff',
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.7
                }).bindPopup(`
                    <strong>${marker.name}</strong><br>
                    HVI Score: ${marker.hvi}<br>
                    Cases (7D): ${marker.cases}
                `).addTo(mainMap);
            });
        }

        // Initialize HVI Analysis Map
        function initHviMap() {
            if (!document.getElementById('hviMap')) return;
            
            hviMap = L.map('hviMap').setView([14.6760, 121.0437], 12);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(hviMap);
            
            const currentDisease = document.getElementById('diseaseSelect').value;
            const barangays = diseaseData[currentDisease].barangays;
            
            barangays.slice(0, 5).forEach((brgy, index) => {
                const lat = 14.6760 + (Math.random() - 0.5) * 0.1;
                const lng = 121.0437 + (Math.random() - 0.5) * 0.1;
                const color = brgy.hvi >= 80 ? '#ef4444' : brgy.hvi >= 60 ? '#f59e0b' : '#10b981';
                
                L.circleMarker([lat, lng], {
                    radius: 15,
                    fillColor: color,
                    color: '#fff',
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.8
                }).bindPopup(`
                    <strong>${brgy.name}</strong><br>
                    HVI Score: ${brgy.hvi}<br>
                    Risk Level: ${brgy.risk}
                `).addTo(hviMap);
            });
        }

        // Initialize Hotspots Map
        function initHotspotsMap() {
            if (!document.getElementById('hotspotsMap')) return;
            
            hotspotsMap = L.map('hotspotsMap').setView([14.6760, 121.0437], 12);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(hotspotsMap);
            
            // Add cluster circles
            const clusters = [
                { lat: 14.7200, lng: 121.0800, radius: 800, name: 'C-001', cases: 58 },
                { lat: 14.7100, lng: 121.1000, radius: 600, name: 'C-002', cases: 42 },
                { lat: 14.6900, lng: 121.0500, radius: 550, name: 'C-003', cases: 35 },
                { lat: 14.6750, lng: 121.0900, radius: 450, name: 'C-004', cases: 28 },
                { lat: 14.7050, lng: 121.0650, radius: 400, name: 'C-005', cases: 22 }
            ];
            
            clusters.forEach(cluster => {
                L.circle([cluster.lat, cluster.lng], {
                    radius: cluster.radius,
                    fillColor: '#ef4444',
                    color: '#dc2626',
                    weight: 2,
                    opacity: 0.8,
                    fillOpacity: 0.3
                }).bindPopup(`
                    <strong>Cluster ${cluster.name}</strong><br>
                    Total Cases: ${cluster.cases}<br>
                    Status: Continuous
                `).addTo(hotspotsMap);
            });
        }

        // Map Layer Toggle
        function toggleMapLayer(layer) {
            console.log(`Toggle layer: ${layer}`);
            // In production, this would toggle GeoJSON layers
        }

        // Initialize Chart
        function initChart(data, color) {
            const ctx = document.getElementById('trendChart');
            if (!ctx) return;
            
            const labels = data.map(d => new Date(d.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
            const counts = data.map(d => d.count);

            trendChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'New Cases',
                        data: counts,
                        borderColor: color,
                        backgroundColor: `${color}40`,
                        tension: 0.4,
                        fill: true,
                        pointRadius: 4,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: color,
                        pointBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: { display: true, text: 'Cases', font: { size: 12 } },
                            grid: { color: '#e5e7eb' },
                            ticks: { font: { size: 11 } }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 11 } }
                        }
                    },
                    plugins: {
                        legend: { 
                            display: true, 
                            position: 'top',
                            labels: { font: { size: 12 }, padding: 10 }
                        },
                        tooltip: { mode: 'index', intersect: false }
                    }
                }
            });
        }

        // Update Dashboard
        function updateDashboard(diseaseKey) {
            const data = diseaseData[diseaseKey];
            if (!data) return;

            // Update KPI Cards
            document.getElementById('hviScore').textContent = data.hvi.toFixed(1);
            document.getElementById('hviProgress').style.width = `${data.hvi}%`;
            document.getElementById('hotspotsCount').textContent = data.hotspots;
            document.getElementById('totalCases').textContent = data.totalCases.toLocaleString();
            document.getElementById('riskDriver').textContent = data.riskDriver;

            // Update Chart
            if (trendChart) {
                const labels = data.cases.map(d => new Date(d.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
                const counts = data.cases.map(d => d.count);
                
                trendChart.data.labels = labels;
                trendChart.data.datasets[0].data = counts;
                trendChart.data.datasets[0].borderColor = data.color;
                trendChart.data.datasets[0].backgroundColor = `${data.color}40`;
                trendChart.data.datasets[0].pointBorderColor = data.color;
                trendChart.update();
            }

            // Update Table
            const tableBody = document.getElementById('tableBody');
            if (tableBody) {
                tableBody.innerHTML = '';
                data.barangays.forEach((brgy, index) => {
                    const riskBadge = brgy.risk === 'Critical' ? 'bg-red-600 text-white' :
                                      brgy.risk === 'High' ? 'bg-orange-500 text-white' :
                                      brgy.risk === 'Medium' ? 'bg-amber-500 text-white' :
                                      'bg-green-500 text-white';
                    
                    const row = document.createElement('tr');
                    row.className = 'hover:bg-gray-50';
                    row.innerHTML = `
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${brgy.name}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold">${brgy.hvi.toFixed(1)}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${brgy.cases}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="badge ${riskBadge}">${brgy.risk}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <button class="text-green-600 hover:text-green-900">
                                <i class="fas fa-eye mr-1"></i>View
                            </button>
                        </td>
                    `;
                    tableBody.appendChild(row);
                });
            }

            // Update maps if they exist
            if (hviMap) {
                hviMap.eachLayer(layer => {
                    if (layer instanceof L.CircleMarker) {
                        hviMap.removeLayer(layer);
                    }
                });
                
                data.barangays.slice(0, 5).forEach((brgy, index) => {
                    const lat = 14.6760 + (Math.random() - 0.5) * 0.1;
                    const lng = 121.0437 + (Math.random() - 0.5) * 0.1;
                    const color = brgy.hvi >= 80 ? '#ef4444' : brgy.hvi >= 60 ? '#f59e0b' : '#10b981';
                    
                    L.circleMarker([lat, lng], {
                        radius: 15,
                        fillColor: color,
                        color: '#fff',
                        weight: 2,
                        opacity: 1,
                        fillOpacity: 0.8
                    }).bindPopup(`
                        <strong>${brgy.name}</strong><br>
                        HVI Score: ${brgy.hvi}<br>
                        Risk Level: ${brgy.risk}
                    `).addTo(hviMap);
                });
            }
        }

        // Barangay Search
        function setupBarangaySearch() {
            const searchInput = document.getElementById('barangaySearch');
            if (!searchInput) return;
            
            searchInput.addEventListener('input', (e) => {
                const searchTerm = e.target.value.toLowerCase();
                const rows = document.querySelectorAll('#tableBody tr');
                
                rows.forEach(row => {
                    const barangayName = row.querySelector('td:first-child').textContent.toLowerCase();
                    if (barangayName.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }

        // Initialize on load
        document.addEventListener('DOMContentLoaded', () => {
            const diseaseSelect = document.getElementById('diseaseSelect');
            const initialDisease = diseaseSelect ? diseaseSelect.value : 'dengue';

            // Initialize chart
            initChart(diseaseData[initialDisease].cases, diseaseData[initialDisease].color);
            
            // Update dashboard
            updateDashboard(initialDisease);
            
            // Initialize main map after a short delay
            setTimeout(() => initMainMap(), 500);
            
            // Setup disease selector
            if (diseaseSelect) {
                diseaseSelect.addEventListener('change', (e) => {
                    updateDashboard(e.target.value);
                });
            }
            
            // Setup search
            setupBarangaySearch();
        });
    </script>
</body>
</html>
