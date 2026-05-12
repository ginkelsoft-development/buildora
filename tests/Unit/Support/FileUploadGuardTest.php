<?php

namespace Ginkelsoft\Buildora\Tests\Unit\Support;

use Ginkelsoft\Buildora\Support\FileUploadGuard;
use Ginkelsoft\Buildora\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class FileUploadGuardTest extends TestCase
{
    /**
     * @return array<string, array{0: string}>
     */
    public static function blockedFilenames(): array
    {
        return [
            'bare extension' => ['php'],
            'leading dot'    => ['.php'],
            'full filename'  => ['shell.php'],
            'uppercase'      => ['SHELL.PHP'],
            'mixed case'     => ['Shell.PhP'],
            'phar'           => ['exploit.phar'],
            'phtml'          => ['x.phtml'],
            'htaccess'       => ['.htaccess'],
            'double-ext'     => ['avatar.jpg.php'],
            'windows exe'    => ['payload.exe'],
            'windows bat'    => ['hack.bat'],
            'jsp'            => ['x.jsp'],
            'whitespace'     => ['  shell.php  '],
        ];
    }

    #[Test]
    #[DataProvider('blockedFilenames')]
    public function it_blocks_executable_uploads(string $filename): void
    {
        $this->assertTrue(
            FileUploadGuard::isBlocked($filename),
            "Expected '{$filename}' to be blocked."
        );
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function allowedFilenames(): array
    {
        return [
            'jpg'            => ['photo.jpg'],
            'png'            => ['logo.png'],
            'pdf'            => ['contract.pdf'],
            'docx'           => ['letter.docx'],
            'svg'            => ['icon.svg'],
            'xlsx'           => ['report.xlsx'],
            'no extension'   => ['archive'],
            'empty string'   => [''],
            'only dots'      => ['...'],
        ];
    }

    #[Test]
    #[DataProvider('allowedFilenames')]
    public function it_does_not_block_safe_uploads(string $filename): void
    {
        $this->assertFalse(
            FileUploadGuard::isBlocked($filename),
            "Did not expect '{$filename}' to be blocked."
        );
    }

    #[Test]
    public function blocked_extensions_list_contains_the_expected_baseline(): void
    {
        $list = FileUploadGuard::blockedExtensions();

        // Spot-check a handful so future edits notice if anything regresses.
        $this->assertContains('php', $list);
        $this->assertContains('phar', $list);
        $this->assertContains('htaccess', $list);
        $this->assertContains('exe', $list);
    }
}
