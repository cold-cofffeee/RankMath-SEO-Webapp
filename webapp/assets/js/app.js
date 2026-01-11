/**
 * RankMath SEO Webapp - Frontend JavaScript
 */

// Use auto-detected configuration
const API_BASE = window.APP_CONFIG.apiBase;
const BASE_PATH = window.APP_CONFIG.basePath;

// Navigation with URL routing
document.querySelectorAll('.nav-item').forEach(item => {
    item.addEventListener('click', (e) => {
        e.preventDefault();
        const view = item.getAttribute('data-view');
        const url = view === 'dashboard' ? (BASE_PATH || '/') : BASE_PATH + '/' + view;
        
        // Update URL without page reload
        history.pushState({ view: view }, '', url);
        switchView(view);
    });
});

// Handle browser back/forward buttons
window.addEventListener('popstate', (e) => {
    if (e.state && e.state.view) {
        switchView(e.state.view, false);
    } else {
        // Determine view from URL
        const view = getViewFromURL();
        switchView(view, false);
    }
});

// Get view name from current URL
function getViewFromURL() {
    const path = window.location.pathname;
    const segments = path.replace(BASE_PATH, '').split('/').filter(s => s);
    
    if (segments.length === 0) {
        return 'dashboard';
    }
    
    return segments[0];
}

function switchView(viewName, updateURL = true) {
    // Update active nav item
    document.querySelectorAll('.nav-item').forEach(item => {
        item.classList.remove('active');
    });
    const activeNav = document.querySelector(`[data-view="${viewName}"]`);
    if (activeNav) {
        activeNav.classList.add('active');
    }
    
    // Update active view
    document.querySelectorAll('.view').forEach(view => {
        view.classList.remove('active');
    });
    const activeView = document.getElementById(`view-${viewName}`);
    if (activeView) {
        activeView.classList.add('active');
    }
    
    // Update page title
    const titles = {
        'dashboard': 'Dashboard',
        'seo-analysis': 'SEO Analysis',
        'competitor': 'Competitor Analysis',
        'analytics': 'Analytics',
        'content-ai': 'Content AI',
        'local-seo': 'Local SEO',
        'image-seo': 'Image SEO',
        'sitemaps': 'Sitemaps',
        '404-monitor': '404 Monitor',
        'redirections': 'Redirections'
    };
    document.getElementById('page-title').textContent = titles[viewName];
    document.title = `RankMath SEO - ${titles[viewName]}`;
    
    // Update URL if needed
    if (updateURL) {
        const url = viewName === 'dashboard' ? (BASE_PATH || '/') : BASE_PATH + '/' + viewName;
        history.replaceState({ view: viewName }, '', url);
    }
    
    // Load view-specific data
    if (viewName === 'dashboard') {
        loadDashboard();
    } else if (viewName === 'analytics') {
        loadAnalytics();
    } else if (viewName === '404-monitor') {
        load404Logs();
    } else if (viewName === 'redirections') {
        loadRedirections();
    } else if (viewName === 'local-seo') {
        loadLocations();
    }
}

// Toast notification
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.className = `toast ${type} show`;
    
    setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}

// API helper
async function apiRequest(endpoint, method = 'GET', data = null) {
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json'
        }
    };
    
    if (data && method !== 'GET') {
        options.body = JSON.stringify(data);
    }
    
    try {
        const response = await fetch(API_BASE + endpoint, options);
        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.message || 'Request failed');
        }
        
        return result;
    } catch (error) {
        showToast(error.message, 'error');
        throw error;
    }
}

// SEO Analysis
document.getElementById('seo-analysis-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const url = document.getElementById('seo-url').value;
    const resultsDiv = document.getElementById('seo-results');
    
    resultsDiv.innerHTML = '<p class="text-center">Analyzing website... This may take a moment.</p>';
    
    try {
        const response = await fetch(API_BASE + '/api/seo-analysis/analyze', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ url: url, is_competitor: false })
        });
        
        const result = await response.json();
        
        if (result.success) {
            displaySeoResults(result.data);
            showToast('Analysis complete!');
        }
    } catch (error) {
        resultsDiv.innerHTML = '<p class="text-muted">Error: ' + error.message + '</p>';
    }
});

function displaySeoResults(data) {
    const resultsDiv = document.getElementById('seo-results');
    const score = data.score;
    const scoreClass = score >= 80 ? 'good' : score >= 50 ? 'average' : 'poor';
    
    let html = `
        <div class="result-card">
            <h3 class="text-center">SEO Score</h3>
            <div class="result-score ${scoreClass}">${score}/100</div>
            
            <div class="result-section">
                <h4>Title</h4>
                <div class="result-item">
                    <span>${data.results.basic.title || 'No title found'}</span>
                    <span>${data.results.basic.title_length} chars</span>
                </div>
                <p class="text-muted">${data.results.basic.title_optimal ? '✅ Title length is optimal' : '⚠️ Title should be 30-60 characters'}</p>
            </div>
            
            <div class="result-section">
                <h4>Meta Description</h4>
                <div class="result-item">
                    <span>${data.results.meta.description || 'No description found'}</span>
                    <span>${data.results.meta.description_length} chars</span>
                </div>
                <p class="text-muted">${data.results.meta.description_optimal ? '✅ Description length is optimal' : '⚠️ Description should be 120-160 characters'}</p>
            </div>
            
            <div class="result-section">
                <h4>Headings</h4>
                <div class="result-item">
                    <span>H1 Tags</span>
                    <span>${data.results.headings.h1_count}</span>
                </div>
                <p class="text-muted">${data.results.headings.h1_optimal ? '✅ Exactly one H1 tag' : '⚠️ Should have exactly one H1 tag'}</p>
            </div>
            
            <div class="result-section">
                <h4>Images</h4>
                <div class="result-item">
                    <span>Total Images</span>
                    <span>${data.results.images.total_images}</span>
                </div>
                <div class="result-item">
                    <span>With Alt Text</span>
                    <span>${data.results.images.images_with_alt} (${data.results.images.alt_ratio}%)</span>
                </div>
                <p class="text-muted">${data.results.images.alt_ratio >= 90 ? '✅ Great alt text coverage' : '⚠️ Add alt text to all images'}</p>
            </div>
            
            <div class="result-section">
                <h4>Performance</h4>
                <div class="result-item">
                    <span>Load Time</span>
                    <span>${data.results.performance.load_time}s</span>
                </div>
                <p class="text-muted">${data.results.performance.load_time_optimal ? '✅ Good load time' : '⚠️ Consider optimizing page speed'}</p>
            </div>
            
            <div class="result-section">
                <h4>Security & Mobile</h4>
                <div class="result-item">
                    <span>HTTPS</span>
                    <span>${data.results.security.uses_https ? '✅ Enabled' : '❌ Disabled'}</span>
                </div>
                <div class="result-item">
                    <span>Mobile Friendly</span>
                    <span>${data.results.mobile.mobile_friendly ? '✅ Yes' : '❌ No'}</span>
                </div>
            </div>
            
            <div class="result-section">
                <h4>Structured Data</h4>
                <div class="result-item">
                    <span>Schema Found</span>
                    <span>${data.results.structured_data.has_schema ? '✅ Yes' : '❌ No'}</span>
                </div>
            </div>
        </div>
    `;
    
    resultsDiv.innerHTML = html;
}

// Competitor Analysis
document.getElementById('competitor-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const url = document.getElementById('competitor-url').value;
    const resultsDiv = document.getElementById('competitor-results');
    
    resultsDiv.innerHTML = '<p class="text-center">Analyzing competitor... This may take a moment.</p>';
    
    try {
        const response = await fetch(API_BASE + '/api/seo-analysis/analyze', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ url: url, is_competitor: true })
        });
        
        const result = await response.json();
        
        if (result.success) {
            displaySeoResults(result.data);
            document.getElementById('competitor-results').innerHTML = document.getElementById('seo-results').innerHTML;
            showToast('Competitor analysis complete!');
        }
    } catch (error) {
        resultsDiv.innerHTML = '<p class="text-muted">Error: ' + error.message + '</p>';
    }
});

// Content AI
document.getElementById('content-ai-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const keyword = document.getElementById('ai-keyword').value;
    const contentType = document.getElementById('ai-content-type').value;
    const resultsDiv = document.getElementById('ai-results');
    
    resultsDiv.innerHTML = '<p class="text-center">Generating content...</p>';
    
    try {
        const response = await fetch(API_BASE + '/api/content-ai/generate', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ keyword, content_type: contentType })
        });
        
        const result = await response.json();
        
        if (result.success) {
            resultsDiv.innerHTML = `
                <div class="result-card">
                    <h4>Generated ${contentType.replace('_', ' ')}</h4>
                    <p style="margin-top: 15px; line-height: 1.8;">${result.data.content}</p>
                    <button class="btn btn-secondary mt-20" onclick="copyToClipboard('${result.data.content.replace(/'/g, "\\'")}')">Copy to Clipboard</button>
                </div>
            `;
            showToast('Content generated!');
        }
    } catch (error) {
        resultsDiv.innerHTML = '<p class="text-muted">Error: ' + error.message + '</p>';
    }
});

function copyToClipboard(text) {
    navigator.clipboard.writeText(text);
    showToast('Copied to clipboard!');
}

// Analytics
async function loadAnalytics() {
    const dashboardDiv = document.getElementById('analytics-dashboard');
    dashboardDiv.innerHTML = '<p class="text-muted">Loading analytics data...</p>';
    
    try {
        const response = await fetch(API_BASE + '/api/analytics/dashboard');
        const result = await response.json();
        
        if (result.success) {
            const data = result.data;
            let html = '<h4>Top Keywords</h4><div class="list-area">';
            
            if (data.keywords && data.keywords.length > 0) {
                data.keywords.forEach(kw => {
                    html += `
                        <div class="list-item">
                            <div>
                                <strong>${kw.keyword}</strong><br>
                                <small class="text-muted">Impressions: ${kw.total_impressions} | Clicks: ${kw.total_clicks}</small>
                            </div>
                            <div>Pos: ${parseFloat(kw.avg_position).toFixed(1)}</div>
                        </div>
                    `;
                });
            } else {
                html += '<p class="text-muted">No keyword data available yet. <a href="#" onclick="switchView(\'dashboard\')">Run an SEO analysis</a> to get started.</p>';
            }
            
            html += '</div>';
            dashboardDiv.innerHTML = html;
        }
    } catch (error) {
        dashboardDiv.innerHTML = '<p class="text-muted">Error loading analytics. Please try again.</p>';
        console.error('Analytics error:', error);
    }
}

// 404 Monitor
async function load404Logs() {
    const logsDiv = document.getElementById('404-logs');
    logsDiv.innerHTML = '<p class="text-muted">Loading 404 logs...</p>';
    
    try {
        const response = await fetch(API_BASE + '/api/404-monitor/logs');
        const result = await response.json();
        
        if (result.success) {
            const logs = result.data.logs;
            
            if (logs.length === 0) {
                logsDiv.innerHTML = '<p class="text-muted">No 404 errors logged yet. Great job! 🎉</p>';
                return;
            }
            
            let html = '';
            logs.forEach(log => {
                html += `
                    <div class="list-item">
                        <div>
                            <strong>${log.uri}</strong><br>
                            <small class="text-muted">Hits: ${log.hits} | Last: ${log.last_accessed}</small>
                        </div>
                        <div class="list-item-actions">
                            <button class="btn btn-danger" onclick="delete404Log(${log.id})">Delete</button>
                        </div>
                    </div>
                `;
            });
            
            logsDiv.innerHTML = html;
        }
    } catch (error) {
        logsDiv.innerHTML = '<p class="text-muted">Error loading 404 logs. Please try again.</p>';
        console.error('404 Monitor error:', error);
    }
}

async function delete404Log(id) {
    if (!confirm('Delete this 404 log?')) return;
    
    try {
        await fetch(API_BASE + `/api/404-monitor/${id}`, { method: 'DELETE' });
        showToast('Log deleted');
        load404Logs();
    } catch (error) {
        showToast(error.message, 'error');
    }
}

async function clear404Logs() {
    if (!confirm('Clear all 404 logs? This cannot be undone.')) return;
    
    try {
        await fetch(API_BASE + '/api/404-monitor/clear', { method: 'POST' });
        showToast('All logs cleared');
        load404Logs();
    } catch (error) {
        showToast(error.message, 'error');
    }
}

// Redirections
async function loadRedirections() {
    const listDiv = document.getElementById('redirections-list');
    listDiv.innerHTML = '<p class="text-muted">Loading redirections...</p>';
    
    try {
        const response = await fetch(API_BASE + '/api/redirections');
        const result = await response.json();
        
        if (result.success) {
            const redirections = result.data;
            
            if (redirections.length === 0) {
                listDiv.innerHTML = '<p class="text-muted">No redirections configured yet. Click "Add Redirection" to create one.</p>';
                return;
            }
            
            let html = '';
            redirections.forEach(redir => {
                html += `
                    <div class="list-item">
                        <div>
                            <strong>${redir.source_url}</strong> → ${redir.target_url}<br>
                            <small class="text-muted">Type: ${redir.redirect_type} | Hits: ${redir.hits}</small>
                        </div>
                        <div class="list-item-actions">
                            <button class="btn btn-danger" onclick="deleteRedirection(${redir.id})">Delete</button>
                        </div>
                    </div>
                `;
            });
            
            listDiv.innerHTML = html;
        }
    } catch (error) {
        listDiv.innerHTML = '<p class="text-muted">Error loading redirections. Please try again.</p>';
        console.error('Redirections error:', error);
    }
}

async function deleteRedirection(id) {
    if (!confirm('Delete this redirection?')) return;
    
    try {
        await fetch(API_BASE + `/api/redirections/${id}`, { method: 'DELETE' });
        showToast('Redirection deleted');
        loadRedirections();
    } catch (error) {
        showToast(error.message, 'error');
    }
}

function showAddRedirectionForm() {
    const source = prompt('Enter source URL:');
    if (!source) return;
    
    const target = prompt('Enter target URL:');
    if (!target) return;
    
    const type = prompt('Enter redirect type (301, 302, 307, 308):', '301');
    
    addRedirection(source, target, type);
}

async function addRedirection(source, target, type) {
    try {
        await fetch(API_BASE + '/api/redirections', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                source_url: source,
                target_url: target,
                redirect_type: type
            })
        });
        
        showToast('Redirection added');
        loadRedirections();
    } catch (error) {
        showToast(error.message, 'error');
    }
}

// Local SEO
async function loadLocations() {
    const listDiv = document.getElementById('locations-list');
    listDiv.innerHTML = '<p class="text-muted">Loading locations...</p>';
    
    try {
        const response = await fetch(API_BASE + '/api/local-seo/locations');
        const result = await response.json();
        
        if (result.success) {
            const locations = result.data;
            
            if (locations.length === 0) {
                listDiv.innerHTML = '<p class="text-muted">No locations added yet. Click "Add New Location" to get started.</p>';
                return;
            }
            
            let html = '';
            locations.forEach(loc => {
                html += `
                    <div class="list-item">
                        <div>
                            <strong>${loc.name}</strong><br>
                            <small class="text-muted">${loc.address || ''} ${loc.city || ''}</small>
                        </div>
                        <div class="list-item-actions">
                            <button class="btn btn-danger" onclick="deleteLocation(${loc.id})">Delete</button>
                        </div>
                    </div>
                `;
            });
            
            listDiv.innerHTML = html;
        }
    } catch (error) {
        listDiv.innerHTML = '<p class="text-muted">Error loading locations. Please try again.</p>';
        console.error('Locations error:', error);
    }
}

async function deleteLocation(id) {
    if (!confirm('Delete this location?')) return;
    
    try {
        await fetch(API_BASE + `/api/local-seo/locations/${id}`, { method: 'DELETE' });
        showToast('Location deleted');
        loadLocations();
    } catch (error) {
        showToast(error.message, 'error');
    }
}

function showAddLocationForm() {
    const name = prompt('Enter location name:');
    if (!name) return;
    
    const address = prompt('Enter address:');
    const city = prompt('Enter city:');
    const phone = prompt('Enter phone:');
    
    addLocation(name, address, city, phone);
}

async function addLocation(name, address, city, phone) {
    try {
        await fetch(API_BASE + '/api/local-seo/locations', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ name, address, city, phone })
        });
        
        showToast('Location added');
        loadLocations();
    } catch (error) {
        showToast(error.message, 'error');
    }
}

// Image SEO bulk analysis
document.getElementById('image-bulk-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const url = document.getElementById('image-url').value;
    const resultsDiv = document.getElementById('image-results');
    
    resultsDiv.innerHTML = '<p class="text-center">Analyzing images...</p>';
    
    try {
        const response = await fetch(API_BASE + '/api/image-seo/bulk-analyze', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ url })
        });
        
        const result = await response.json();
        
        if (result.success) {
            let html = '<h4>Image Analysis Results</h4><div class="list-area">';
            
            result.data.forEach(img => {
                html += `
                    <div class="list-item">
                        <div>
                            <strong>${img.src.substring(0, 80)}...</strong><br>
                            <small class="text-muted">
                                ${img.has_alt ? '✅ Has alt text' : '⚠️ Missing alt text'} | 
                                Size: ${(img.size / 1024).toFixed(1)}KB
                            </small>
                        </div>
                    </div>
                `;
            });
            
            html += '</div>';
            resultsDiv.innerHTML = html;
            showToast(`Analyzed ${result.data.length} images`);
        }
    } catch (error) {
        resultsDiv.innerHTML = '<p class="text-muted">Error: ' + error.message + '</p>';
    }
});

// Sitemap crawler
document.getElementById('sitemap-crawl-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const url = document.getElementById('sitemap-url').value;
    const resultsDiv = document.getElementById('sitemap-results');
    
    resultsDiv.innerHTML = '<p class="text-center">Crawling website and generating sitemap... This may take a few minutes.</p>';
    
    try {
        const response = await fetch(API_BASE + '/api/sitemap/crawl', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ base_url: url, max_pages: 100 })
        });
        
        const result = await response.json();
        
        if (result.success) {
            resultsDiv.innerHTML = `
                <div class="result-card">
                    <h4>Sitemap Generated</h4>
                    <p>Found: ${result.data.total_found} URLs</p>
                    <p>Added: ${result.data.added} URLs</p>
                    <a href="${API_BASE}/api/sitemap/generate-xml" class="btn btn-primary" target="_blank">Download XML Sitemap</a>
                </div>
            `;
            showToast('Sitemap generated!');
        }
    } catch (error) {
        resultsDiv.innerHTML = '<p class="text-muted">Error: ' + error.message + '</p>';
    }
});

// Dashboard
async function loadDashboard() {
    try {
        const response = await fetch(API_BASE + '/api/dashboard/stats');
        const result = await response.json();
        
        if (result.success) {
            const stats = result.data;
            
            // Update stat cards
            const statValues = document.querySelectorAll('.stat-value');
            if (statValues[0]) statValues[0].textContent = stats.seo_score || 'N/A';
            if (statValues[1]) statValues[1].textContent = (stats.total_keywords || 0).toLocaleString();
            if (statValues[2]) statValues[2].textContent = (stats.impressions || 0).toLocaleString();
            if (statValues[3]) statValues[3].textContent = (stats.clicks || 0).toLocaleString();
            
            // Update recent activity
            const activityDiv = document.getElementById('recent-activity');
            if (stats.recent_activity && stats.recent_activity.length > 0) {
                let html = '';
                stats.recent_activity.forEach(activity => {
                    html += `<p class="text-muted" style="margin: 5px 0;">${activity}</p>`;
                });
                activityDiv.innerHTML = html;
            }
        }
    } catch (error) {
        console.error('Failed to load dashboard:', error);
    }
}

// Init - Load correct view based on URL
function initApp() {
    const currentView = getViewFromURL();
    switchView(currentView, false);
    console.log('RankMath SEO Webapp initialized with view:', currentView);
}

// Start the app when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initApp);
} else {
    initApp();
}
