<?php

namespace Ginkelsoft\Buildora\Actions;

/**
 * Class PageAction
 *
 * Represents a page-level action that can be triggered from a resource index page.
 * Similar to BulkAction but for general page operations.
 */
class PageAction
{
    protected ?string $permission = null;
    protected string $label;
    protected string $icon;
    protected string $route;
    protected string $method;
    protected array $parameters = [];
    protected ?string $confirmMessage = null;
    protected string $style = 'primary'; // primary, secondary, success, danger, warning

    /**
     * PageAction constructor.
     *
     * @param string $label The display label of the action
     * @param string $icon The icon class (e.g., FontAwesome)
     * @param string $route The Laravel route name to trigger
     * @param array $parameters Optional route parameters
     */
    public function __construct(string $label, string $icon, string $route, array $parameters = [])
    {
        $this->label = $label;
        $this->icon = $icon;
        $this->route = $route;
        $this->parameters = $parameters;
        $this->method = 'POST';
    }

    /**
     * Factory method to quickly create a new PageAction instance.
     */
    public static function make(string $label, string $icon, string $route, array $parameters = []): self
    {
        return new self($label, $icon, $route, $parameters);
    }

    /**
     * Set the HTTP method for this action.
     */
    public function method(string $method): self
    {
        $this->method = strtoupper($method);
        return $this;
    }

    /**
     * Set a confirmation message.
     */
    public function confirm(string $message): self
    {
        $this->confirmMessage = $message;
        return $this;
    }

    /**
     * Set the required permission for this action.
     */
    public function permission(string $permission): self
    {
        $this->permission = $permission;
        return $this;
    }

    /**
     * Set the button style.
     */
    public function style(string $style): self
    {
        $this->style = $style;
        return $this;
    }

    /**
     * Get the label.
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Get the icon.
     */
    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * Get the route name.
     */
    public function getRoute(): string
    {
        return $this->route;
    }

    /**
     * Get the HTTP method.
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Get the parameters.
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Get the confirmation message.
     */
    public function getConfirmMessage(): ?string
    {
        return $this->confirmMessage;
    }

    /**
     * Get the required permission.
     */
    public function getPermission(): ?string
    {
        return $this->permission;
    }

    /**
     * Get the button style.
     */
    public function getStyle(): string
    {
        return $this->style;
    }

    /**
     * Convert to array for JSON serialization.
     */
    public function toArray(): array
    {
        return [
            'label' => $this->label,
            'icon' => $this->icon,
            'route' => $this->route,
            'method' => $this->method,
            'parameters' => $this->parameters,
            'confirmMessage' => $this->confirmMessage,
            'permission' => $this->permission,
            'style' => $this->style,
        ];
    }
}
