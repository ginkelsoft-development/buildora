<?php

namespace Ginkelsoft\Buildora\Validation\Rules;

use Closure;
use Ginkelsoft\Buildora\Support\FileUploadGuard;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

/**
 * Refuses uploads whose extension is on Buildora's executable deny-list,
 * regardless of any per-field MIME whitelist.
 *
 * Always evaluates the client-supplied original filename. MIME-type checks
 * (mimes: / mimetypes:) are still useful — but they trust the file's magic
 * bytes, which an attacker can spoof. The extension is what the web server
 * actually uses when serving the file, so it is the authoritative axis for
 * blocking RCE-class uploads.
 */
final class BlocksExecutableUploads implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile) {
            return;
        }

        $original = (string) $value->getClientOriginalName();

        if (FileUploadGuard::isBlocked($original)) {
            $fail("The {$attribute} has a file extension that is not allowed.");
        }
    }
}
