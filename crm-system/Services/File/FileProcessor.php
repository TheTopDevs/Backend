<?php

namespace App\Services\File;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileProcessor
{
    /**
     * @var Filesystem|FilesystemAdapter
     */
    protected $storage;

    /**
     * Storage Disk Folder
     */
    public const STORAGE_PREFIX = 'files';

    /**
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->storage = $filesystem;
    }

    /**
     * @param UploadedFile $file
     * @param InterfaceFileConfig $config
     * @return false|string
     */
    public function uploadFile(UploadedFile $file, InterfaceFileConfig $config)
    {
        //full path to upload file in storage
        $path = sprintf(
            '%s%s',
            self::STORAGE_PREFIX,
            $config->makePath($file->getPathname(), $file->clientExtension())
        );

        //make folder for upload file
        $this->storage->makeDirectory($path);

        return $file->store($path);
    }
}
