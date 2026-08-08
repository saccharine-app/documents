<?php

namespace Saccharine\Documents\Interfaces;

interface FillablePdfInterface
{
    public function fill(string $templatePath, array $payload): string;
}