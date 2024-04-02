<?php

$servername = "localhost"; // Server name
$username = "root"; // MySQL root username
$password = "root"; // MySQL root password
$logFile = "/home/masum/Desktop/champion_dre/champion_dre_sites_updated.log"; // Path to your log file

try {
    $pdo = new PDO("mysql:host=$servername", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("SHOW DATABASES");
    $databases = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $logEntries = [];

    foreach ($databases as $db) {

        $pdo->exec("USE `$db`");
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

        foreach ($tables as $table) {

            // Check to identify only the tables that ends with '_options'
            if (preg_match('/_options$/', $table)) {

                $stmt = $pdo->query("SELECT option_value FROM `$table` WHERE option_name = 'blog_public'");
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($result && $result['option_value'] == '0') {
                    $pdo->exec("UPDATE `$table` SET option_value = '1' WHERE option_name = 'blog_public'");
                    $logEntries[] = "Updated {$db}.{$table} to allow search engine indexing.";
                }

                break;
            }
        }
    }

    // Write to log file
    if (!empty($logEntries)) {

        $date = new DateTime();
        $logTimestamp = $date->format('Y-m-d H:i:s');
        array_unshift($logEntries, "Log started at $logTimestamp");
        file_put_contents($logFile, implode("\n", $logEntries) . "\n\n", FILE_APPEND);

    }

    echo "Process completed. Check the log file for details.\n";

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

?>
