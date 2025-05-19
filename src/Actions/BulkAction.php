<?php

namespace Ginkelsoft\Buildora\Actions;

/**
 * Class BulkAction
 *
 * Represents a bulk action that can be triggered from a datatable.
 * Allows configuration of the action label, HTTP method, target route, and parameters.
 */
class BulkAction
{
    protected ?string $permission = null;
    /**
     * The label that will be shown in the UI for the action.
     */
    protected string $label;

    /**
     * The HTTP method that should be used (e.g., GET, POST, DELETE).
     */
    protected string $method;

    /**
     * The named route that this action should invoke.
     */
    protected string $route;

    /**
     * The parameters that should be passed to the route.
     */
    protected array $parameters = [];

    /**
     * BulkAction constructor.
     *
     * @param string $label The display label of the action
     * @param string $route The Laravel route name to trigger
     * @param array $parameters Optional route parameters
     */
    public function __construct(string $label, string $route, array $parameters = [])
    {
        $this->label = $label;
        $this->route = $route;
        $this->parameters = $parameters;
        $this->method = 'GET';
    }

    /**
     * Factory method to quickly create a new BulkAction instance.
     *
     * @param string $label
     * @param string $route
     * @param array $parameters
     * @return self
     */
    public static function make(string $label, string $route, array $parameters = []): self
    {
        return new self($label, $route, $parameters);
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
     * Get the label of the bulk action.
     *
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Explicitly set the permission required for this action.
     *
     * @param string $permission
     * @return self
     */
    public function permission(string $permission): self
    {
        $this->permission = $permission;
        return $this;
    }

    /**
     * Get the permission for this action.
     *
     * @return string|null
     */
    public function getPermission(): ?string
    {
        return $this->permission;
    }

    /**
     * Convert the bulk action to an array for rendering in the frontend.
     * Includes label, method, and the generated route URL.
     *
     * @param array $context Optional context parameters (unused currently)
     * @return array<string, string>
     */
    public function toArray(array $context = []): array
    {
        return [
            'label' => $this->label,
            'method' => $this->method,
            'url' => route($this->route, $this->parameters),
        ];
    }
}
