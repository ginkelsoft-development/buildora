<?php

namespace Ginkelsoft\Buildora\Support;

/**
 * Centralises which file extensions Buildora refuses to accept on upload,
 * regardless of whether a resource defined an explicit MIME-type whitelist.
 *
 * The deny-list targets file types that the web server can execute or that
 * affect web server configuration when stored on a public disk — i.e. the
 * classic upload-to-RCE classes.
 */
final class FileUploadGuard
{
    /**
     * Extensions that are always blocked.
     *
     * Compared case-insensitively. Leading dot is tolerated.
     *
     * @var array<int, string>
     */
    private const DEFAULT_BLOCKED_EXTENSIONS = [
        // PHP variants
        'php', 'php3', 'php4', 'php5', 'php7', 'php8',
        'phtml', 'phar', 'pht', 'phps',
        // Other server-side scripts
        'jsp', 'jspx', 'asp', 'aspx', 'cer',
        'cgi', 'pl', 'py', 'rb',
        // Web server config / shell
        'htaccess', 'htpasswd',
        'sh', 'bash', 'zsh',
        // Windows executables
        'exe', 'bat', 'cmd', 'com', 'msi', 'dll',
    ];

    /**
     * @return array<int, string>
     */
    public static function blockedExtensions(): array
    {
        return self::DEFAULT_BLOCKED_EXTENSIONS;
    }

    /**
     * Returns true when the given filename or extension is on the deny-list.
     *
     * Accepts either a bare extension ("php"), an extension with a leading
     * dot (".php"), or a full filename ("shell.php"). Comparison is
     * case-insensitive.
     */
    public static function isBlocked(string $filenameOrExtension): bool
    {
        $extension = self::extractExtension($filenameOrExtension);

        if ($extension === '') {
            return false;
        }

        return in_array($extension, self::DEFAULT_BLOCKED_EXTENSIONS, true);
    }

    private static function extractExtension(string $input): string
    {
        $input = strtolower(trim($input));

        if ($input === '') {
            return '';
        }

        // Strip leading dot if the caller passed ".php" instead of "php".
        if (str_starts_with($input, '.')) {
            $input = substr($input, 1);
        }

        // If there is still a dot, treat the trailing segment as the extension.
        if (str_contains($input, '.')) {
            $parts = explode('.', $input);
            return (string) array_pop($parts);
        }

        return $input;
    }
}
