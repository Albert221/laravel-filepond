<?php

declare(strict_types=1);

namespace Albert221\Filepond;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;

class FilepondRule implements Rule
{
    private FilepondSerializer $serializer;

    /**
     * @var array|string
     */
    private $rules;

    private array $validationMessages = [];

    /**
     * @param FilepondSerializer $serializer
     * @param string|array $rules
     */
    public function __construct(FilepondSerializer $serializer, $rules = [])
    {
        $this->serializer = $serializer;
        $this->rules = $rules;
    }

    public function passes($attribute, $value): bool
    {
        if ($value instanceof UploadedFile) {
            $file = $value;
        } elseif ($this->serializer->exists($value)) {
            $file = $this->serializer->retrieve($value);
        } else {
            return false;
        }

        if (empty($this->rules)) {
            return true;
        }

        $validator = Validator::make(['file' => $file], ['file' => $this->rules]);
        $this->validationMessages = $validator->getMessageBag()->all();

        return !$validator->fails();
    }

    public function message(): array
    {
        return $this->validationMessages;
    }
}
