<?php

namespace Saccharine\Documents\Interfaces;

interface HtmlToPdfInterface
{
    public function convertHtml(string $html): string;
}