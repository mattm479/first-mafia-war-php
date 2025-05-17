<?php

namespace Fmw;

use Exception;

class BaseClass implements BaseInterface
{
    protected readonly Application $application;

    protected function __construct(Application $application)
    {
        $this->application = $application;
    }

    /**
     * @throws Exception
     */
    protected function render(string $template, array $data = []): void
    {
        try {
            $this->application->template->render($template, [
                'header' => $this->application->header->getHeaderData(),
                'sidebar' => $this->application->header->getSidebarData(),
                'data' => $data
            ]);
        } catch (Exception $e) {
            $this->application->logger->error($e->getMessage());
        }
    }
}