<?php

namespace App\Services\File;

interface InterfaceFileConfig
{
    /**
     * @param string $path
     * @param string $ext
     * @return string
     */
    public function makePath(string $path, string $ext): string;
}
