<?php

namespace App\Services\File;

class PropertyFileConfig implements InterfaceFileConfig
{
    private $buildingId;
    private $categoryId;

    public function __construct(int $buildingId, int $categoryFileId)
    {
        $this->buildingId = $buildingId;
        $this->categoryId = $categoryFileId;
    }

    public function makePath(string $path, string $ext): string
    {
        $folder = sprintf(
            '/%s/%s',
            $this->buildingId,
            $this->categoryId
        );
        return $folder;
    }
}
