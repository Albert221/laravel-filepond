<?php

declare(strict_types=1);

namespace Albert221\Filepond;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class FilepondController extends Controller
{
    private Filepond $filepond;
    private FilepondSerializer $serializer;

    public function __construct(Filepond $filepond, FilepondSerializer $serializer)
    {
        $this->filepond = $filepond;
        $this->serializer = $serializer;
    }

    public function process(Request $request): Response
    {
        $file = Arr::flatten($request->allFiles())[0] ?? null;
        if (!$file instanceof UploadedFile) {
            throw new UnprocessableEntityHttpException('No uploaded file could be found.');
        }

        try {
            $serverId = $this->serializer->store($file);
        } catch (FilepondException $e) {
            throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage(), $e);
        }

        return new Response($serverId, Response::HTTP_OK, ['Content-Type' => 'text/plain']);
    }

    public function revoke(Request $request): Response
    {
        $this->serializer->delete($request->getContent());

        return new Response('', Response::HTTP_OK, ['Content-Type' => 'text/plain']);
    }
}
