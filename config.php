<?php
const DBHOST = 'localhost';
const DBNAME = 'databasename';
const DBCHARSET = 'utf8mb4';
const DBUSERNAME = "username";
const DBPASSWD = "password";
const CARD_TRANSFER_AMOUNT_LIMIT = ['min' => 20000, 'max' => 2000000];
$connect = mysqli_connect(DBHOST, DBUSERNAME, DBPASSWD, DBNAME);

if ($connect->connect_error) {
    die("The connection to the database failed:" . $connect->connect_error);
}
mysqli_set_charset($connect, DBCHARSET);

$APIKEY = "**TOKEN**";
$SUID = "5522424631";
$BOTURL = "domain.com/bot";
$BOTUSERNAME = "marzbaninfobot";

// Check if incoming IP is within Telegram's range; may impact performance.
const TELEGRAM_IP_CHECK = false;

const DBOPTIONS = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

$dsn = "mysql:host=" . DBHOST . ";dbname=" . DBNAME . ";charset=" . DBCHARSET;

try {
     $pdo = new PDO($dsn, DBUSERNAME, DBPASSWD, DBOPTIONS);
} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}