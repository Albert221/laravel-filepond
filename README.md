# `Albert221/laravel-filepond`

This package provides basic FilePond Laravel functionality and helps handle files sent with JS disabled.

This package was written in a real hurry. It may not be well documented or well tested, but should work. Pull Requests fixing that are welcome.

## Installation

```bash
composer require albert221/laravel-filepond
```

If you need to change configuration:

```bash
php artisan vendor:publish --provider="Albert221\Filepond\FilepondServiceProvider"
```

## Usage

### Frontend

```js
FilePond.setOptions({
  server: '/filepond'
});
```

### Backend

```php
// app/Http/Controllers/SomeController.php

public function someAction(Request $request, Filepond $filepond)
{
    // Thanks to `fromRequest` method, it works both when JS was on and off.
    /** @var UploadedFile|[]UploadedFile $file */
    $file = $filepond->fromRequest($request, 'file');
    
    // (...)
}
```

#### Validation

```php
// app/Http/Requests/SomeRequest.php

public function rules(FilepondSerializer $filepondSerializer): array
{
    return [
        'foobar' => 'required',
        'file' => [
            'required',
            // It validates both UploadedFile and FilePond's serverId
            new FilepondRule($filepondSerializer, [
                'mimetypes:audio/mpeg'
            ]),
        ],
    ];
}
```

## License

This project was heavily inspired by [Sopamo/laravel-filepond](https://github.com/Sopamo/laravel-filepond).

This project is on [Apache 2.0 license](LICENSE).
