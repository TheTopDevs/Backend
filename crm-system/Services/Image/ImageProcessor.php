<?php

namespace App\Services\Image;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;


class ImageProcessor
{
    /**
     * @var ImageConfiguration
     */
    protected $configuration;

    /**
     * @var ImageManager
     */
    protected $image;

    /**
     * @var Filesystem|FilesystemAdapter
     */
    protected $storage;

    public function __construct(ImageConfiguration $imageConfiguration)
    {
        $this->configuration = $imageConfiguration;
        $this->image = new ImageManager();
        $this->storage = Storage::disk('public');
    }

    protected function resize(string $source, string $destination)
    {
        return $this->image->make($source)
            ->fit($this->configuration->getThumbnailWidth(), $this->configuration->getThumbnailHeight())
            ->save($destination);
    }

    protected function resizeTo($source, $destination)
    {
        try {
            return $this->image->make($source)
                ->resize($this->configuration->getPictureWidth(), null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })
                ->save($destination);
        } catch (\Exception $exception) {
            Log::debug($exception->getMessage());
            return false;
        }
    }

    /**
     * @param UploadedFile $file
     * @param string $option
     * @return array
     */
    public function saveImage(UploadedFile $file, $option = 'FullHD')
    {
        $pathInfo = $this->configuration->makePath($file->getPathname(), $file->guessExtension());
        $this->storage->makeDirectory($pathInfo['path']);

        switch ($option) {
            case 'FullHD':
                $this->resizeTo($file->getPathname(), $this->storage->path($pathInfo['imagePath']));
                $this->resize($file->getPathname(), $this->storage->path($pathInfo['thumbnailPath']));
                break;
            case 'Avatar':
                $this->resize($file->getPathname(), $this->storage->path($pathInfo['imagePath']));
                break;
        }

        return [
            'image' => $pathInfo['imagePath'],
            'thumbnail' => $pathInfo['thumbnailPath'],
        ];
    }


    public function deleteImage(string $path)
    {
        if (!$this->storage->delete($path)) {
            Log::error('picture not deleted: ' . $path);
        }
    }
}
