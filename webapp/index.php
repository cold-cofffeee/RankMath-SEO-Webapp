<?php require_once __DIR__ . '/config/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RankMath SEO Webapp - Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script>
        // Auto-detected configuration - works anywhere!
        window.APP_CONFIG = <?php echo json_encode(getConfigForJS()); ?>;
    </script>
</head>
<body>
    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">
                <h2>🚀 RankMath</h2>
                <p>SEO Toolkit</p>
            </div>
            
            <nav class="nav-menu">
                <a href="<?php echo BASE_PATH ?: '/'; ?>" class="nav-item" data-view="dashboard">
                    <span class="icon">📊</span>
                    Dashboard
                </a>
                <a href="<?php echo BASE_PATH; ?>/seo-analysis" class="nav-item" data-view="seo-analysis">
                    <span class="icon">🔍</span>
                    SEO Analysis
                </a>
                <a href="<?php echo BASE_PATH; ?>/competitor" class="nav-item" data-view="competitor">
                    <span class="icon">⚔️</span>
                    Competitor Analysis
                </a>
                <a href="<?php echo BASE_PATH; ?>/analytics" class="nav-item" data-view="analytics">
                    <span class="icon">📈</span>
                    Analytics
                </a>
                <a href="<?php echo BASE_PATH; ?>/content-ai" class="nav-item" data-view="content-ai">
                    <span class="icon">🤖</span>
                    Content AI
                </a>
                <a href="<?php echo BASE_PATH; ?>/local-seo" class="nav-item" data-view="local-seo">
                    <span class="icon">📍</span>
                    Local SEO
                </a>
                <a href="<?php echo BASE_PATH; ?>/image-seo" class="nav-item" data-view="image-seo">
                    <span class="icon">🖼️</span>
                    Image SEO
                </a>
                <a href="<?php echo BASE_PATH; ?>/sitemaps" class="nav-item" data-view="sitemaps">
                    <span class="icon">🗺️</span>
                    Sitemaps
                </a>
                <a href="<?php echo BASE_PATH; ?>/404-monitor" class="nav-item" data-view="404-monitor">
                    <span class="icon">⚠️</span>
                    404 Monitor
                </a>
                <a href="<?php echo BASE_PATH; ?>/redirections" class="nav-item" data-view="redirections">
                    <span class="icon">↪️</span>
                    Redirections
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="top-bar">
                <h1 id="page-title">Dashboard</h1>
                <div class="user-actions">
                    <span class="user-name">Welcome!</span>
                </div>
            </header>

            <div class="content-area" id="content-area">
                <!-- Dashboard View -->
                <div class="view active" id="view-dashboard">
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon">🔍</div>
                            <div class="stat-info">
                                <h3>SEO Score</h3>
                                <p class="stat-value">--</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">📊</div>
                            <div class="stat-info">
                                <h3>Total Keywords</h3>
                                <p class="stat-value">--</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">👁️</div>
                            <div class="stat-info">
                                <h3>Impressions</h3>
                                <p class="stat-value">--</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">🖱️</div>
                            <div class="stat-info">
                                <h3>Clicks</h3>
                                <p class="stat-value">--</p>
                            </div>
                        </div>
                    </div>

                    <div class="dashboard-grid">
                        <div class="card">
                            <h3>Quick Actions</h3>
                            <div class="quick-actions">
                                <button class="btn btn-primary" onclick="switchView('seo-analysis')">Run SEO Analysis</button>
                                <button class="btn btn-secondary" onclick="switchView('competitor')">Analyze Competitor</button>
                                <button class="btn btn-secondary" onclick="switchView('content-ai')">Generate Content</button>
                                <button class="btn btn-secondary" onclick="switchView('404-monitor')">View 404s</button>
                            </div>
                        </div>

                        <div class="card">
                            <h3>Recent Activity</h3>
                            <div id="recent-activity">
                                <p class="text-muted">No recent activity</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SEO Analysis View -->
                <div class="view" id="view-seo-analysis">
                    <div class="card">
                        <h3>Website SEO Analysis</h3>
                        <form id="seo-analysis-form" class="form">
                            <div class="form-group">
                                <label>Enter Website URL</label>
                                <input type="url" id="seo-url" placeholder="https://example.com" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Analyze Website</button>
                        </form>
                        <div id="seo-results" class="results-area"></div>
                    </div>
                </div>

                <!-- Competitor Analysis View -->
                <div class="view" id="view-competitor">
                    <div class="card">
                        <h3>Competitor Analysis</h3>
                        <form id="competitor-form" class="form">
                            <div class="form-group">
                                <label>Competitor URL</label>
                                <input type="url" id="competitor-url" placeholder="https://competitor.com" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Analyze Competitor</button>
                        </form>
                        <div id="competitor-results" class="results-area"></div>
                    </div>
                </div>

                <!-- Analytics View -->
                <div class="view" id="view-analytics">
                    <div class="card">
                        <h3>Analytics Dashboard</h3>
                        <div id="analytics-dashboard">
                            <p class="text-muted">No analytics data available yet. Import from Google Search Console or add data manually.</p>
                        </div>
                    </div>
                </div>

                <!-- Content AI View -->
                <div class="view" id="view-content-ai">
                    <div class="card">
                        <h3>AI Content Generator</h3>
                        <form id="content-ai-form" class="form">
                            <div class="form-group">
                                <label>Target Keyword</label>
                                <input type="text" id="ai-keyword" placeholder="e.g., digital marketing" required>
                            </div>
                            <div class="form-group">
                                <label>Content Type</label>
                                <select id="ai-content-type">
                                    <option value="paragraph">Paragraph</option>
                                    <option value="title">Title</option>
                                    <option value="meta_description">Meta Description</option>
                                    <option value="heading">Heading</option>
                                    <option value="conclusion">Conclusion</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Generate Content</button>
                        </form>
                        <div id="ai-results" class="results-area"></div>
                    </div>
                </div>

                <!-- Local SEO View -->
                <div class="view" id="view-local-seo">
                    <div class="card">
                        <h3>Local SEO Locations</h3>
                        <button class="btn btn-primary" onclick="showAddLocationForm()">Add New Location</button>
                        <div id="locations-list" class="list-area">
                            <p class="text-muted">No locations added yet.</p>
                        </div>
                    </div>
                </div>

                <!-- Image SEO View -->
                <div class="view" id="view-image-seo">
                    <div class="card">
                        <h3>Image SEO Optimizer</h3>
                        <form id="image-bulk-form" class="form">
                            <div class="form-group">
                                <label>Analyze All Images from URL</label>
                                <input type="url" id="image-url" placeholder="https://example.com" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Analyze Images</button>
                        </form>
                        <div id="image-results" class="results-area"></div>
                    </div>
                </div>

                <!-- Sitemaps View -->
                <div class="view" id="view-sitemaps">
                    <div class="card">
                        <h3>Sitemap Generator</h3>
                        <form id="sitemap-crawl-form" class="form">
                            <div class="form-group">
                                <label>Website URL to Crawl</label>
                                <input type="url" id="sitemap-url" placeholder="https://example.com" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Generate Sitemap</button>
                        </form>
                        <div id="sitemap-results" class="results-area"></div>
                    </div>
                </div>

                <!-- 404 Monitor View -->
                <div class="view" id="view-404-monitor">
                    <div class="card">
                        <h3>404 Error Monitor</h3>
                        <button class="btn btn-secondary" onclick="load404Logs()">Refresh</button>
                        <button class="btn btn-danger" onclick="clear404Logs()">Clear All</button>
                        <div id="404-logs" class="list-area">
                            <p class="text-muted">No 404 errors logged yet.</p>
                        </div>
                    </div>
                </div>

                <!-- Redirections View -->
                <div class="view" id="view-redirections">
                    <div class="card">
                        <h3>URL Redirections</h3>
                        <button class="btn btn-primary" onclick="showAddRedirectionForm()">Add Redirection</button>
                        <div id="redirections-list" class="list-area">
                            <p class="text-muted">No redirections configured yet.</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast"></div>

    <script src="assets/js/app.js"></script>
</body>
</html>
