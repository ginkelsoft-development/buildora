<?php

namespace Ginkelsoft\Buildora\Actions;

use Ginkelsoft\Buildora\Support\UrlBuilder;

/**
 * Class RowAction
 *
 * Represents an action that can be triggered on an individual row in a datatable.
 * Supports icons, confirmation dialogs, dynamic parameter injection, and custom routes.
 */
class RowAction
{
    /** @var string The label displayed for the action */
    public string $label;

    /** @var string The icon class (e.g., FontAwesome) */
    public string $icon;

    /** @var string The action type (e.g., 'route', 'url') */
    public string $type;

    /** @var string The target route name or URL */
    public string $action;

    /** @var string The HTTP method to be used (GET, POST, etc.) */
    public string $method = 'GET';

    /** @var array Parameters to be injected into the action URL */
    public array $params = [];

    /** @var string|null Confirmation message before executing the action */
    public ?string $confirmMessage = null;

    /**
     * RowAction constructor.
     *
     * @param string $label  Display label for the action
     * @param string $icon   Icon to show beside the label
     * @param string $type   Type of action (e.g., 'route')
     * @param string $action Route name or URL to be executed
     */
    public function __construct(string $label, string $icon, string $type, string $action)
    {
        $this->label = $label;
        $this->icon = $icon;
        $this->type = $type;
        $this->action = $action;
    }

    /**
     * Factory method to create a new RowAction.
     *
     * @param string $label
     * @param string $icon
     * @param string $type
     * @param string $action
     * @return self
     */
    public static function make(string $label, string $icon, string $type, string $action): self
    {
        return new self($label, $icon, $type, $action);
    }

    /**
     * Set the HTTP method for the action.
     *
     * @param string $method
     * @return self
     */
    public function method(string $method): self
    {
        $this->method = strtoupper($method);
        return $this;
    }

    /**
     * Set the parameters that should be used in the URL.
     *
     * @param array $params
     * @return self
     */
    public function params(array $params): self
    {
        $this->params = $params;
        return $this;
    }

    /**
     * Set a confirmation message that is shown before the action is triggered.
     *
     * @param string $message
     * @return self
     */
    public function confirm(string $message): self
    {
        $this->confirmMessage = $message;
        return $this;
    }

    /**
     * Convert the action into an array format suitable for rendering.
     *
     * @param object $item The item (row) this action is bound to
     * @return array<string, mixed>
     *
     * @throws \InvalidArgumentException If the item is null or invalid
     */
    public function toArray(object $item): array
    {
        if (! $item) {
            throw new \InvalidArgumentException("RowAction requires a valid item to generate a URL.");
        }

        $values = collect($item->getFields())
            ->mapWithKeys(fn($field) => [$field->name => $field->value])
            ->toArray();

        return [
            'label' => $this->label,
            'icon' => $this->icon,
            'url' => UrlBuilder::build($this->type, $this->action, $item, $this->params),
            'method' => $this->method,
            'confirm' => $this->confirmMessage,
        ];
    }
}
