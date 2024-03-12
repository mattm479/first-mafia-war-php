<?php

namespace Fmw;

use Exception;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;

class Template
{
    /**
     * @var FilesystemLoader $_loader
     */
    private readonly FilesystemLoader $_loader;

    /**
     * @var Environment $_twig
     */
    private readonly Environment $_twig;

    /**
     * @var Logger $_logger
     */
    private readonly Logger $_logger;

    /**
     * @param string $templateDir
     * @param array $options
     */
    public function __construct(string $templateDir, array $options = []) {
        $this->_loader = new FilesystemLoader($templateDir);
        $this->_twig = new Environment($this->_loader, $options);
        $this->_logger = new Logger('templates');
        $this->_logger->pushHandler(new StreamHandler("../var/logs/templates_" . date('Y-m-d') . ".log", Level::Debug));
    }

    /**
     * @param string $templateName
     * @param array $data
     * @return void
     * @throws Exception
     */
    public function render(string $templateName, array $data = []): void {
        try {
            echo $this->_twig->render($templateName, $data);
        } catch (Exception $e) {
            $this->_logger->log(Level::Error, $e->getMessage());
            echo $this->_twig->render("{$e->getCode()}.html.twig", [
                'header' => $data['header'],
                'sidebar' => $data['sidebar'],
                'message' => $e->getMessage()
            ]);
        }
    }
}