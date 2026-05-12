<?php

namespace Ginkelsoft\Buildora\Fields\Types;

use Ginkelsoft\Buildora\Fields\Field;
use Ginkelsoft\Buildora\Validation\Rules\BlocksExecutableUploads;

/**
 * Represents a file upload field with options for validation, disk storage, and preview.
 */
class FileField extends Field
{
    public string $type = 'file';

    protected ?string $accept = null;
    protected ?int $maxSizeKb = null;
    protected ?int $maxWidth = null;
    protected ?int $maxHeight = null;
    protected string $disk = 'public';
    protected string $path = '/';
    protected bool $showPreview = false;

    /**
     * Server-side MIME-type whitelist. When set, uploads with any other MIME
     * type are rejected by Laravel's mimetypes rule.
     *
     * @var array<int, string>|null
     */
    protected ?array $allowedMimeTypes = null;

    /**
     * Create a new FileField instance.
     *
     * @param string $name The name of the field.
     * @param string|null $label Optional label for the field.
     * @param string $type Field type (default: 'file').
     * @return static
     */
    public static function make(string $name, ?string $label = null, string $type = 'file'): static
    {
        return new static($name, $label ?? ucfirst($name), $type);
    }

    /**
     * Specify allowed MIME types or extensions.
     *
     * @param string $mimeOrExtension Comma-separated list of accepted formats.
     * @return static
     */
    public function accept(string $mimeOrExtension): static
    {
        $this->accept = $mimeOrExtension;
        return $this;
    }

    /**
     * Set maximum file size in kilobytes.
     *
     * @param int $kilobytes Maximum size allowed.
     * @return static
     */
    public function maxSize(int $kilobytes): static
    {
        $this->maxSizeKb = $kilobytes;
        return $this;
    }

    /**
     * Set maximum image dimensions.
     *
     * @param int $width Max width in pixels.
     * @param int $height Max height in pixels.
     * @return static
     */
    public function imageDimensions(int $width, int $height): static
    {
        $this->maxWidth = $width;
        $this->maxHeight = $height;
        return $this;
    }

    /**
     * Set the storage disk to use.
     *
     * @param string $disk The disk name (e.g., 'public').
     * @return static
     */
    public function disk(string $disk): static
    {
        $this->disk = $disk;
        return $this;
    }

    /**
     * Set the path on the disk where files will be stored.
     *
     * @param string $path Relative path inside the disk.
     * @return static
     */
    public function path(string $path): static
    {
        $this->path = trim($path, '/');
        return $this;
    }

    /**
     * Server-side whitelist of accepted MIME types.
     *
     * Note: this is enforced via Laravel's "mimetypes:" rule (real magic-byte
     * detection). The accept() method only hints to the browser and is not
     * trustworthy as a security boundary.
     *
     * @param array<int, string> $types e.g. ['image/jpeg', 'image/png', 'application/pdf']
     * @return static
     */
    public function allowedMimeTypes(array $types): static
    {
        $this->allowedMimeTypes = array_values(array_unique(array_filter($types)));
        return $this;
    }

    /**
     * @return array<int, string>|null
     */
    public function getAllowedMimeTypes(): ?array
    {
        return $this->allowedMimeTypes;
    }

    /**
     * Build the validation rules for this field.
     *
     * Combines (in order):
     *   - any rules a developer registered through ->validation()
     *   - file rule (so non-uploads short-circuit the others)
     *   - BlocksExecutableUploads — always active, even without a whitelist
     *   - mimetypes:<whitelist> when allowedMimeTypes() is set
     *   - max:<kb> when maxSize() is set
     *
     * @return array<int, mixed>
     */
    public function getValidationRules(mixed $model = null): array
    {
        $rules = parent::getValidationRules($model);

        $rules[] = 'file';
        $rules[] = new BlocksExecutableUploads();

        if (! empty($this->allowedMimeTypes)) {
            $rules[] = 'mimetypes:' . implode(',', $this->allowedMimeTypes);
        }

        if ($this->maxSizeKb !== null) {
            $rules[] = 'max:' . $this->maxSizeKb;
        }

        return $rules;
    }

    /**
     * Enable or disable file preview in the UI.
     *
     * @param bool $state Whether to show preview.
     * @return static
     */
    public function showPreview(bool $state = true): static
    {
        $this->showPreview = $state;
        return $this;
    }

    /**
     * Get the raw accept string.
     *
     * @return string|null
     */
    public function getAccept(): ?string
    {
        return $this->accept;
    }

    /**
     * Get accepted types as an array.
     *
     * @return array
     */
    public function getAcceptArray(): array
    {
        return array_map('trim', explode(',', $this->accept ?? ''));
    }

    /**
     * Get the maximum upload size in KB.
     *
     * @return int|null
     */
    public function getMaxSizeKb(): ?int
    {
        return $this->maxSizeKb;
    }

    /**
     * Get the maximum width for uploaded images.
     *
     * @return int|null
     */
    public function getMaxWidth(): ?int
    {
        return $this->maxWidth;
    }

    /**
     * Get the maximum height for uploaded images.
     *
     * @return int|null
     */
    public function getMaxHeight(): ?int
    {
        return $this->maxHeight;
    }

    /**
     * Get the configured filesystem disk.
     *
     * @return string
     */
    public function getDisk(): string
    {
        return $this->disk;
    }

    /**
     * Get the upload path relative to the disk.
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Determine if the file preview should be shown.
     *
     * @return bool
     */
    public function shouldShowPreview(): bool
    {
        return $this->showPreview;
    }

    /**
     * Generate help text describing upload restrictions.
     *
     * @return string|null
     */
    public function getHelpText(): ?string
    {
        $parts = [];

        if ($this->accept) {
            $parts[] = "Allowed: {$this->accept}";
        }

        if ($this->maxSizeKb) {
            $parts[] = "Max size: {$this->maxSizeKb}KB";
        }

        if ($this->maxWidth && $this->maxHeight) {
            $parts[] = "Max dimensions: {$this->maxWidth}×{$this->maxHeight}px";
        }

        return implode(' | ', $parts) ?: parent::getHelpText();
    }
}
