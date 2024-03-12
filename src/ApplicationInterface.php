<?php

namespace Fmw;

interface ApplicationInterface
{
    /**
     * string LogName
     */
    const LogName = "application";

    /**
     * string LogDir
     */
    const LogDir = "../var/logs/";

    /**
     * @return void
     */
    function loadSettings(): void;

    /**
     * @param int $userId
     * @return void
     */
    function loadUser(int $userId): void;
}