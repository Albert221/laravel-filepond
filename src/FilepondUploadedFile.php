<?php

declare(strict_types=1);

namespace Albert221\Filepond;

use Illuminate\Http\UploadedFile;

class FilepondUploadedFile extends UploadedFile
{
    public function isValid(): bool
    {
        // Because the overriden implementation uses `is_uploaded_file` which returns false
        // for `UploadedFile` instances that we create for FilePond uploads.
        return true;
    }
}
