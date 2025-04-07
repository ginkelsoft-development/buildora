<?php

namespace Ginkelsoft\Buildora\Fields\Types;

use Ginkelsoft\Buildora\Fields\Field;

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
