<?php

namespace Ginkelsoft\Buildora\Tests\Unit\Validation\Rules;

use Ginkelsoft\Buildora\Tests\TestCase;
use Ginkelsoft\Buildora\Validation\Rules\BlocksExecutableUploads;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\Test;

class BlocksExecutableUploadsTest extends TestCase
{
    #[Test]
    public function it_fails_a_php_upload(): void
    {
        $rule = new BlocksExecutableUploads();
        $file = UploadedFile::fake()->create('shell.php', 1);

        $failed = false;
        $rule->validate('avatar', $file, function () use (&$failed) {
            $failed = true;
        });

        $this->assertTrue($failed, 'Expected validation to fail for .php upload.');
    }

    #[Test]
    public function it_passes_a_png_upload(): void
    {
        $rule = new BlocksExecutableUploads();
        $file = UploadedFile::fake()->image('logo.png');

        $failed = false;
        $rule->validate('avatar', $file, function () use (&$failed) {
            $failed = true;
        });

        $this->assertFalse($failed, 'Did not expect validation to fail for .png upload.');
    }

    #[Test]
    public function it_inspects_the_client_original_name_not_the_temporary_name(): void
    {
        // The temp path Laravel assigns has no extension; the rule must consult
        // the client-supplied original name instead.
        $rule = new BlocksExecutableUploads();
        $file = UploadedFile::fake()->create('payload.phtml', 1);

        $failed = false;
        $rule->validate('avatar', $file, function () use (&$failed) {
            $failed = true;
        });

        $this->assertTrue($failed);
    }

    #[Test]
    public function non_upload_values_are_left_to_other_rules(): void
    {
        // A string or null isn't this rule's concern — Laravel's "file" rule
        // will catch that. This rule should not blow up on non-UploadedFile
        // input.
        $rule = new BlocksExecutableUploads();

        $failed = false;
        $callback = function () use (&$failed) {
            $failed = true;
        };

        $rule->validate('avatar', 'just-a-string', $callback);
        $rule->validate('avatar', null, $callback);
        $rule->validate('avatar', 123, $callback);

        $this->assertFalse($failed);
    }
}
