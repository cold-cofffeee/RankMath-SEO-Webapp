<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install RankMath SEO Webapp</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
            padding: 40px;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
        }
        .step {
            display: none;
        }
        .step.active {
            display: block;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #444;
            font-weight: 500;
        }
        input[type="text"],
        input[type="password"],
        input[type="email"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
            transition: border 0.3s;
        }
        input:focus {
            outline: none;
            border-color: #667eea;
        }
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 14px 32px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
            width: 100%;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .alert {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .progress {
            height: 8px;
            background: #e0e0e0;
            border-radius: 4px;
            margin-bottom: 30px;
            overflow: hidden;
        }
        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            transition: width 0.4s;
        }
        .feature-list {
            list-style: none;
            padding: 0;
        }
        .feature-list li {
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
            color: #555;
        }
        .feature-list li:before {
            content: "✓";
            color: #667eea;
            font-weight: bold;
            margin-right: 10px;
        }
        .requirements {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .requirements h3 {
            font-size: 16px;
            margin-bottom: 10px;
            color: #333;
        }
        .req-item {
            padding: 5px 0;
            display: flex;
            justify-content: space-between;
        }
        .req-status {
            font-weight: 600;
        }
        .req-status.pass {
            color: #28a745;
        }
        .req-status.fail {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 RankMath SEO Webapp</h1>
        <p class="subtitle">Complete SEO toolkit for your website</p>
        
        <div class="progress">
            <div class="progress-bar" id="progressBar" style="width: 33%"></div>
        </div>

        <!-- Step 1: Requirements Check -->
        <div class="step active" id="step1">
            <h2 style="margin-bottom: 20px;">System Requirements</h2>
            <div class="requirements" id="reqCheck">
                <div class="req-item">
                    <span>PHP Version (>= 7.4)</span>
                    <span class="req-status" id="php-version">Checking...</span>
                </div>
                <div class="req-item">
                    <span>MySQL/MariaDB</span>
                    <span class="req-status" id="mysql">Checking...</span>
                </div>
                <div class="req-item">
                    <span>PDO Extension</span>
                    <span class="req-status" id="pdo">Checking...</span>
                </div>
                <div class="req-item">
                    <span>cURL Extension</span>
                    <span class="req-status" id="curl">Checking...</span>
                </div>
                <div class="req-item">
                    <span>JSON Extension</span>
                    <span class="req-status" id="json">Checking...</span>
                </div>
            </div>
            
            <div id="reqResult"></div>
            
            <button class="btn" onclick="checkRequirements()">Check Requirements</button>
            <button class="btn" id="nextBtn1" onclick="nextStep(2)" style="display:none; margin-top: 10px;">Continue to Database Setup →</button>
        </div>

        <!-- Step 2: Database Configuration -->
        <div class="step" id="step2">
            <h2 style="margin-bottom: 20px;">Database Configuration</h2>
            <form id="dbForm">
                <div class="form-group">
                    <label>Database Host</label>
                    <input type="text" name="db_host" value="localhost" required>
                </div>
                <div class="form-group">
                    <label>Database Port</label>
                    <input type="text" name="db_port" value="3306" required>
                </div>
                <div class="form-group">
                    <label>Database Name</label>
                    <input type="text" name="db_name" value="rankmath_webapp" required>
                </div>
                <div class="form-group">
                    <label>Database Username</label>
                    <input type="text" name="db_user" value="root" required>
                </div>
                <div class="form-group">
                    <label>Database Password</label>
                    <input type="password" name="db_pass" placeholder="Leave empty for XAMPP default">
                </div>
                <div class="form-group">
                    <label>Table Prefix</label>
                    <input type="text" name="db_prefix" value="rm_" required>
                </div>
                <div id="dbResult"></div>
                <button type="submit" class="btn">Test Connection & Install Database</button>
            </form>
        </div>

        <!-- Step 3: Success -->
        <div class="step" id="step3">
            <h2 style="margin-bottom: 20px;">🎉 Installation Complete!</h2>
            
            <div class="alert alert-success">
                Your RankMath SEO Webapp has been successfully installed!
            </div>

            <h3 style="margin: 20px 0 10px;">Available Features:</h3>
            <ul class="feature-list">
                <li>SEO Analysis & Competitor Analysis</li>
                <li>Analytics & Keyword Tracking</li>
                <li>Content AI Writing Assistant</li>
                <li>Local SEO Management</li>
                <li>Image SEO Optimization</li>
                <li>Sitemap Generator (News & Video)</li>
                <li>404 Error Monitor</li>
                <li>Redirections Manager</li>
                <li>Schema Markup Generator</li>
            </ul>

            <button class="btn" onclick="window.location.href='index.php'" style="margin-top: 30px;">
                Go to Dashboard →
            </button>
        </div>
    </div>

    <script>
        let currentStep = 1;

        function nextStep(step) {
            document.getElementById('step' + currentStep).classList.remove('active');
            currentStep = step;
            document.getElementById('step' + step).classList.add('active');
            updateProgress();
        }

        function updateProgress() {
            const progress = (currentStep / 3) * 100;
            document.getElementById('progressBar').style.width = progress + '%';
        }

        function checkRequirements() {
            fetch('install-handler.php?action=check_requirements')
                .then(res => res.json())
                .then(data => {
                    document.getElementById('php-version').textContent = data.php_version;
                    document.getElementById('php-version').className = 'req-status ' + (data.php_ok ? 'pass' : 'fail');
                    
                    document.getElementById('mysql').textContent = data.mysql_ok ? 'Available' : 'Not Found';
                    document.getElementById('mysql').className = 'req-status ' + (data.mysql_ok ? 'pass' : 'fail');
                    
                    document.getElementById('pdo').textContent = data.pdo_ok ? 'Available' : 'Not Found';
                    document.getElementById('pdo').className = 'req-status ' + (data.pdo_ok ? 'pass' : 'fail');
                    
                    document.getElementById('curl').textContent = data.curl_ok ? 'Available' : 'Not Found';
                    document.getElementById('curl').className = 'req-status ' + (data.curl_ok ? 'pass' : 'fail');
                    
                    document.getElementById('json').textContent = data.json_ok ? 'Available' : 'Not Found';
                    document.getElementById('json').className = 'req-status ' + (data.json_ok ? 'pass' : 'fail');

                    if (data.all_ok) {
                        document.getElementById('reqResult').innerHTML = '<div class="alert alert-success">All requirements met! You can proceed with installation.</div>';
                        document.getElementById('nextBtn1').style.display = 'block';
                    } else {
                        document.getElementById('reqResult').innerHTML = '<div class="alert alert-error">Some requirements are not met. Please install missing extensions.</div>';
                    }
                });
        }

        document.getElementById('dbForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            document.getElementById('dbResult').innerHTML = '<div class="alert alert-info">Installing database...</div>';
            
            fetch('install-handler.php?action=install_database', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('dbResult').innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
                    setTimeout(() => nextStep(3), 1500);
                } else {
                    document.getElementById('dbResult').innerHTML = '<div class="alert alert-error">' + data.message + '</div>';
                }
            })
            .catch(err => {
                document.getElementById('dbResult').innerHTML = '<div class="alert alert-error">Installation failed: ' + err.message + '</div>';
            });
        });

        // Auto-check requirements on load
        window.addEventListener('load', checkRequirements);
    </script>
</body>
</html>
