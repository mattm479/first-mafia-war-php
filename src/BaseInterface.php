<?php

namespace Fmw;

interface BaseInterface
{
    function render(string $template, array $data = []): void;
}