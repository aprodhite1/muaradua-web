<?php\
require_once __DIR__ . '/../config/db.php';\
$files = ['muaradua_db.sql', 'migrate_5desa.sql', 'seed_desa_lain.sql', 'kuesioner_v2.sql'];\
foreach ($files as $f) {\
    $path = __DIR__ . '/' . $f;\
        if (!file_exists($path)) { echo "Skip: $f\
"; continue; }\
            $sql = file_get_contents($path);\
                $sql = preg_replace('/--.*\\n/', '', $sql);\
                    $sql = preg_replace('/\\/\\*.*?\\*\\//s', '', $sql);\
                        $stmts = array_filter(array_map('trim', explode(';', $sql)));\
                            $ok = 0; $err = 0;\
                                foreach ($stmts as $s) {\
                                        if (!$s) continue;\
                                                try { $pdo->exec($s); $ok++; } catch (PDOException $e) { $err++; }\
                                                    }\
                                                        echo "$f - OK: $ok, Error: $err<br>";\
                                                        }\
                                                        echo "Migration Fixed Successfully!";\
                                                        
