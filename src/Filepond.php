<?php

declare(strict_types=1);

namespace Albert221\Filepond;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class Filepond
{
    private FilepondSerializer $serializer;

    public function __construct(FilepondSerializer $serializer)
    {
        $this->serializer = $serializer;
    }

    public function getUploadedFile(string $serverId): UploadedFile
    {
        return $this->serializer->retrieve($serverId);
    }

    /**
     * @param Request $request
     * @param string $fieldName
     *
     * @return UploadedFile|UploadedFile[]|null
     */
    public function fromRequest(Request $request, string $fieldName)
    {
        if ($request->hasFile($fieldName)) {
            return $request->file($fieldName);
        }

        $serverIds = (array) $request->input($fieldName);
        $results = [];
        foreach ($serverIds as $serverId) {
            $results[] = $this->getUploadedFile($serverId);
        }

        return $results;
    }
}
