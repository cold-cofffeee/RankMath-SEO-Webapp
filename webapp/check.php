<?php
echo "Loaded php.ini: " . php_ini_loaded_file() . "<br>";
echo "extension_dir: " . ini_get('extension_dir') . "<br>";
echo "curl loaded: " . (extension_loaded('curl') ? 'YES' : 'NO') . "<br>";
echo "mysqli loaded: " . (extension_loaded('mysqli') ? 'YES' : 'NO') . "<br>";
echo "pdo_mysql loaded: " . (extension_loaded('pdo_mysql') ? 'YES' : 'NO') . "<br>";
