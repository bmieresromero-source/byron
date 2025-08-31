<?php
define('DB_HOST','localhost');
define('DB_NAME','coile_gestion');
define('DB_USER','root');
define('DB_PASS','password');
function db(){ static $pdo=null; if($pdo===null){ $dsn='mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4'; $pdo=new PDO($dsn, DB_USER, DB_PASS,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]); } return $pdo; }
function has_dompdf(){ if(file_exists(__DIR__.'/vendor/autoload.php')){ require_once __DIR__.'/vendor/autoload.php'; return class_exists(\Dompdf\Dompdf::class); } return false; }
