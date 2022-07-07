<?php

namespace App\Services\Image;

class ImageConfiguration
{
    /**
     * Thumbnail Width
     */
    const THUMBNAIL_WIDTH = 300;

    /**
     * Thumbnail Height
     */
    const THUMBNAIL_HEIGHT = 300;

    /**
     * Picture Width
     */
    const RESIZE_TO_MAX_WIDTH = 1920;

    /**
     * Storage Disk Folder
     */
    const STORAGE_PREFIX = 'pictures/';

    /**
     * @return int
     */
    public function getThumbnailHeight()
    {
        return static::THUMBNAIL_HEIGHT;
    }

    /**
     * @return int
     */
    public function getThumbnailWidth()
    {
        return static::THUMBNAIL_WIDTH;
    }

    /**
     * @return int
     */
    public function getPictureWidth()
    {
        return static::RESIZE_TO_MAX_WIDTH;
    }

    /**
     * @param string $path
     * @param string $ext
     * @return array
     */
    public function makePath($path, $ext)
    {
        $name = sprintf('%s.%s.%s.%s',
            $hash = hash_file('sha256', $path, false),
            $utc = dechex(time()),
            $rnd = dechex(mt_rand()),
            $ext
        );

        $thumbnail = sprintf('%s.%s.%s.%s',
            $hash,
            $utc,
            $rnd,
            'thumbnail.jpg'
        );

        $folder = sprintf('%s/%s/',
            substr($name, 0, 2),
            substr($name, 2, 2)
        );

        return [
            'image' => $name,
            'thumbnail' => $thumbnail,
            'path' => static::STORAGE_PREFIX . $folder,
            'imagePath' => static::STORAGE_PREFIX . $folder . $name,
            'thumbnailPath' => static::STORAGE_PREFIX . $folder . $thumbnail,
        ];
    }
}
