<?php

namespace Fmw;

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;

class Application implements ApplicationInterface
{
    /**
     * @var string $logDirName
     */
    private readonly string $logDirName;

    /**
     * @var Database $db
     */
    public readonly Database $db;

    /**
     * @var Header $header
     */
    public readonly Header $header;

    /**
     * @var Logger $logger
     */
    public readonly Logger $logger;

    /**
     * @var array $settings
     */
    public array $settings;

    /**
     * @var Template $template
     */
    public readonly Template $template;

    /**
     * @var array $user
     */
    public array $user;

    public function __construct()
    {
        $config = require '../config/application.php';
        $this->db = new Database($config['database']);
        $this->loadUser($_SESSION['userId'] ?? 664);
        $this->loadSettings();
        $this->header = new Header($this->db, $this->user, $this->settings);
        $this->logger = new Logger(self::LogName);
        $this->logDirName = self::LogDir . self::LogName . "-" . date('Y-m-d') . ".log";
        $this->logger->pushHandler(new StreamHandler($this->logDirName, Level::Debug));
        $this->template = new Template($config['template']['templateDir'], $config['template']['options']);
    }

    public function __destruct()
    {
        $this->db->close();
    }

    /**
     * @return void
     */
    public function loadSettings(): void
    {
        $query = $this->db->query("SELECT conf_name, conf_value FROM settings");

        while ($row = mysqli_fetch_assoc($query)) {
            $this->settings[$row['conf_name']] = $row['conf_value'];
        }
    }

    /**
     * @param int $userId
     * @return void
     */
    public function loadUser(int $userId): void
    {
        $query = $this->db->query("SELECT u.*, us.* FROM users u LEFT JOIN userstats us ON u.userid = us.userid WHERE u.userid = {$userId}");
        $this->user = mysqli_fetch_assoc($query);
    }
}
