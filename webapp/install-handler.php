<?php
/**
 * Installation Handler
 * Processes installation steps via AJAX
 */

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'check_requirements':
        checkRequirements();
        break;
    
    case 'install_database':
        installDatabase();
        break;
    
    default:
        echo json_encode(['error' => 'Invalid action']);
}

function checkRequirements() {
    $phpVersion = phpversion();
    $phpOk = version_compare($phpVersion, '7.4.0', '>=');
    
    $mysqlOk = extension_loaded('mysqli') || extension_loaded('pdo_mysql');
    $pdoOk = extension_loaded('pdo');
    $curlOk = extension_loaded('curl');
    $jsonOk = extension_loaded('json');
    
    $allOk = $phpOk && $mysqlOk && $pdoOk && $curlOk && $jsonOk;
    
    echo json_encode([
        'php_version' => $phpVersion,
        'php_ok' => $phpOk,
        'mysql_ok' => $mysqlOk,
        'pdo_ok' => $pdoOk,
        'curl_ok' => $curlOk,
        'json_ok' => $jsonOk,
        'all_ok' => $allOk
    ]);
}

function installDatabase() {
    try {
        $host = $_POST['db_host'] ?? 'localhost';
        $port = $_POST['db_port'] ?? 3306;
        $dbname = $_POST['db_name'] ?? 'rankmath_webapp';
        $username = $_POST['db_user'] ?? 'root';
        $password = $_POST['db_pass'] ?? '';
        $prefix = $_POST['db_prefix'] ?? 'rm_';
        
        // Connect to MySQL (without database first)
        $dsn = "mysql:host=$host;port=$port;charset=utf8mb4";
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        // Create database if not exists
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$dbname`");
        
        // Read and execute schema file
        $schema = file_get_contents(__DIR__ . '/database/schema.sql');
        
        // Replace prefix placeholder
        $schema = str_replace('rm_', $prefix, $schema);
        
        // Split into individual queries and execute
        $queries = array_filter(array_map('trim', explode(';', $schema)));
        
        foreach ($queries as $query) {
            if (!empty($query)) {
                $pdo->exec($query);
            }
        }
        
        // Update config file
        $configPath = __DIR__ . '/config/database.php';
        $configContent = <<<PHP
<?php
/**
 * Database Configuration
 * Generated during installation
 */

return [
    'host' => '$host',
    'port' => $port,
    'database' => '$dbname',
    'username' => '$username',
    'password' => '$password',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '$prefix',
    
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];
PHP;
        
        file_put_contents($configPath, $configContent);
        
        // Create .htaccess for Apache
        $htaccess = <<<HTACCESS
# RankMath SEO Webapp
RewriteEngine On

# Redirect to install.php if not installed
RewriteCond %{REQUEST_URI} !^/rankmath/webapp/install.php
RewriteCond %{REQUEST_URI} !^/rankmath/webapp/install-handler.php
RewriteCond %{REQUEST_URI} !^/rankmath/webapp/assets/
RewriteCond %{DOCUMENT_ROOT}/rankmath/webapp/config/database.php !-f
RewriteRule ^(.*)$ install.php [L]

# API Routes
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^api/(.*)$ api.php [L,QSA]

# Frontend Routes
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [L,QSA]
HTACCESS;
        
        file_put_contents(__DIR__ . '/.htaccess', $htaccess);
        
        echo json_encode([
            'success' => true,
            'message' => 'Database installed successfully!'
        ]);
        
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Installation error: ' . $e->getMessage()
        ]);
    }
}
