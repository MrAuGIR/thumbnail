<?php

namespace MrAuGir\Thumbnail\Tests;

use MrAuGir\Thumbnail\Exception\ForbiddenSourceException;
use MrAuGir\Thumbnail\ImageFileManager;
use PHPUnit\Framework\TestCase;

class ImageFileManagerTest extends TestCase
{
    public function testRejectsDisallowedScheme() : void
    {
        // The scheme is checked before any network/filesystem access.
        $this->expectException(ForbiddenSourceException::class);

        (new ImageFileManager())->createResource('file:///etc/passwd');
    }

    public function testRejectsHostOutsideAllowList() : void
    {
        // Host check also happens before any fetch, so no network is involved.
        $this->expectException(ForbiddenSourceException::class);

        (new ImageFileManager(['covers.openlibrary.org']))
            ->createResource('https://evil.example.com/cover.jpg');
    }

    public function testCleanerRemovesTempFile() : void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'thumb_test_');
        file_put_contents($tmp, 'x');
        $this->assertFileExists($tmp);

        (new ImageFileManager())->cleaner($tmp);

        $this->assertFileDoesNotExist($tmp);
    }

    public function testCleanerIsSafeOnMissingFile() : void
    {
        // Must not raise even if the file is already gone (idempotent cleanup).
        (new ImageFileManager())->cleaner(sys_get_temp_dir().'/thumb_does_not_exist_'.uniqid());

        $this->addToAssertionCount(1);
    }
}
