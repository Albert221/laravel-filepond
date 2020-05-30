<?php

declare(strict_types=1);

namespace Albert221\Filepond;

use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\TestCase;

class FilepondRuleTest extends TestCase
{
    public function testWithUploadedFile(): void
    {
        $serializer = $this->createMock(FilepondSerializer::class);
        $serializer->method('exists')
            ->willReturn(true);

        $rule = new FilepondRule($serializer);

        $file = $this->fakeHtmlFile();

        $this->assertTrue($rule->passes('', $file));
    }

    public function testWithFilepond(): void
    {
        $file = $this->fakeHtmlFile();

        $serializer = $this->createMock(FilepondSerializer::class);
        $serializer->method('exists')
            ->willReturn(true);
        $serializer->method('retrieve')
            ->willReturn($file);

        $rule = new FilepondRule($serializer);

        $this->assertTrue($rule->passes('', 'some-server-id'));
    }

    public function testWithRules(): void
    {
        $this->markTestIncomplete('I have to mock Validation\Factory for this test to work.');

        $serializer = $this->createMock(FilepondSerializer::class);
        $serializer->method('exists')
            ->willReturn(true);

        $rule = new FilepondRule(
            $serializer,
            'mimetypes:text/html',
        );
        $this->assertTrue($rule->passes('', $this->fakeHtmlFile()));

        $rule = new FilepondRule(
            $serializer,
            'mimetypes:text/html',
        );
        $this->assertFalse($rule->passes('', $this->fakeMp3File()));
    }

    private function fakeHtmlFile(): UploadedFile
    {
        return UploadedFile::fake()
            ->create('test.html', 10, 'text/html');
    }

    private function fakeMp3File(): UploadedFile
    {
        return UploadedFile::fake()
            ->create('test.mp3', 10, 'audio/mpeg');
    }
}
