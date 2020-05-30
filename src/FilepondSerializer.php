<?php

declare(strict_types=1);

namespace Albert221\Filepond;

use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;

class FilepondSerializer
{
    private const METADATA_SUFFIX = '.metadata';

    private Encrypter $encrypter;
    private string $uploadTemporaryDir;

    public function __construct(Encrypter $encrypter, string $uploadTemporaryDir)
    {
        $this->encrypter = $encrypter;
        $this->uploadTemporaryDir = $uploadTemporaryDir;
    }

    public function store(UploadedFile $file): string
    {
        $tmpFilepath = @tempnam($this->uploadTemporaryDir, 'laravel-filepond');
        $tmpPathinfo = pathinfo($tmpFilepath);

        try {
            $file->move($tmpPathinfo['dirname'], $tmpPathinfo['basename']);
        } catch (FileException $e) {
            throw new FilepondException('Cannot save file.', $e);
        }

        $metadataFilepath = $this->metadataFilepathForFilepath($tmpFilepath);
        $metadata = json_encode([
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'error' => $file->getError(),
        ]);

        if (!file_put_contents($metadataFilepath, $metadata)) {
            throw new FilepondException('Cannot save metadata file.');
        }

        return $this->encrypter->encrypt($tmpFilepath);
    }

    public function exists(string $serverId): bool
    {
        $tmpFilepath = $this->encrypter->decrypt($serverId);
        if (!Str::startsWith($tmpFilepath, $this->uploadTemporaryDir)) {
            return false;
        }

        if (!file_exists($tmpFilepath)) {
            return false;
        }

        $metadataFilepath = $this->metadataFilepathForFilepath($tmpFilepath);

        return file_exists($metadataFilepath);
    }

    public function retrieve(string $serverId): UploadedFile
    {
        $tmpFilepath = $this->encrypter->decrypt($serverId);

        if (!Str::startsWith($tmpFilepath, $this->uploadTemporaryDir)) {
            throw new FilepondException("Invalid file path.");
        }

        if (!file_exists($tmpFilepath)) {
            throw new FilepondException("Cannot retrieve file.");
        }

        $metadataFilepath = $this->metadataFilepathForFilepath($tmpFilepath);
        $metadataFile = file_get_contents($metadataFilepath);
        if (!$metadataFile) {
            throw new FilepondException("Cannot retrieve metadata file.");
        }

        $metadata = json_decode($metadataFile, true);

        $uploadedFile = new SymfonyUploadedFile(
            $tmpFilepath,
            $metadata['original_name'],
            $metadata['mime_type'],
            $metadata['error']
        );

        return FilepondUploadedFile::createFromBase($uploadedFile);
    }

    public function delete(string $serverId): void
    {
        $tmpFilepath = $this->encrypter->decrypt($serverId);
        $metadataFilepath = $this->metadataFilepathForFilepath($tmpFilepath);

        unlink($tmpFilepath);
        unlink($metadataFilepath);
    }

    private function metadataFilepathForFilepath(string $filepath): string
    {
        return $filepath . self::METADATA_SUFFIX;
    }
}
