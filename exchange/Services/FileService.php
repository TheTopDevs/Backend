<?php


namespace App\Services;


use Illuminate\Support\Facades\Storage;
use JetBrains\PhpStorm\ArrayShape;

class FileService
{

    public function uploadListerPhoto(int $userId, $file): string
    {
        $path = 'public/listers/' . $userId . '/avatar';
        $this->deleteAllFiles($path);
        return Storage::put($path, $file);
    }

    public function uploadUserPhoto(int $userId, $file): string
    {
        $path = 'public/users/' . $userId . '/avatar';
        $this->deleteAllFiles($path);
        return Storage::put($path, $file);
    }

    public function deleteAllFiles(string $path): void
    {
        $files = Storage::allFiles($path);
        Storage::delete($files);
    }

    #[ArrayShape([
        'id_front_page_path' => "string",
        'id_back_page_path' => "string",
        'user_with_photo_path' => "string",
        'user_facial_path' => "string"
    ])] public function uploadProfileDocs(
        int $userId,
        array $files
    ): array {
        Storage::delete($this->generateUserPath($userId));
        return [
            'id_front_page_path' => Storage::disk('s3')->put($this->generateUserPath($userId), $files['idFrontPage']),
            'id_back_page_path' => Storage::disk('s3')->put($this->generateUserPath($userId), $files['idBackPage']),
            'user_with_photo_path' => Storage::disk('s3')->put(
                $this->generateUserPath($userId),
                $files['userWithPhoto']
            ),
            'user_facial_path' => Storage::disk('s3')->put($this->generateUserPath($userId), $files['userFacial']),
        ];
    }

    private function generateUserPath(int $userId): string
    {
        return 'user/' . $userId . 'documents';
    }
}
