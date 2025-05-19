<?php

namespace Ginkelsoft\Buildora\Fields\Types;

use Ginkelsoft\Buildora\Fields\Field;
use Illuminate\Database\Eloquent\Model;
use Closure;

/**
 * Represents a custom field that renders a Blade view with optional dynamic data.
 */
class ViewField extends Field
{
    /**
     * Optional closure to dynamically fetch view data.
     *
     * @var Closure|null
     */
    protected ?Closure $closure = null;

    /**
     * The name of the Blade view to render.
     *
     * @var string
     */
    protected string $view;

    /**
     * The variable name passed to the view.
     *
     * @var string
     */
    protected string $var_key;

    /**
     * ViewField constructor.
     *
     * @param string $name The name of the field.
     * @param string|null $label The display label for the field.
     * @param string $type The field type, defaults to 'view'.
     * @param Closure|null $closure Optional closure to provide view data.
     * @param string $view The Blade view path.
     */
    public function __construct(
        string $name,
        ?string $label = null,
        string $type = 'view',
        ?Closure $closure = null,
        string $view = ''
    ) {
        parent::__construct($name, $label ?? ucfirst($name), $type);
        $this->closure = $closure;
        $this->view = $view;
        $this->var_key = $name;
    }

    /**
     * Factory method to create a ViewField.
     *
     * @param string $name
     * @param string|null $label
     * @param string $type
     * @return self
     */
    public static function make(string $name = '', ?string $label = '', string $type = ''): self
    {
        return new self($name, $label, $type);
    }

    /**
     * Indicates whether this field supports search functionality.
     *
     * @return bool
     */
    public function supportsSearch(): bool
    {
        return false;
    }

    /**
     * Set a closure that returns the data to be passed to the view.
     *
     * @param Closure $closure
     * @return self
     */
    public function closure(Closure $closure): self
    {
        $this->closure = $closure;
        return $this;
    }

    /**
     * Get the closure used to fetch view data.
     *
     * @return Closure|null
     */
    public function getClosure(): ?Closure
    {
        return $this->closure;
    }

    /**
     * Set the Blade view path.
     *
     * @param string $view
     * @return self
     */
    public function view(string $view): self
    {
        $this->view = $view;
        return $this;
    }

    /**
     * Get the Blade view path.
     *
     * @return string
     */
    public function getView(): string
    {
        return $this->view;
    }

    /**
     * Get the variable name passed to the view.
     *
     * @return string
     */
    public function getVarKey(): string
    {
        return $this->var_key;
    }

    /**
     * Set the value for the field by executing the closure or using the model.
     *
     * @param Model $model
     * @return self
     */
    public function setValue(Model $model): self
    {
        $viewData = $this->closure ? call_user_func($this->closure, $model) : $model;
        $this->value = $viewData;

        return $this;
    }

    /**
     * Render the view with the current value for the detail page.
     *
     * @return string
     */
    public function detailPage(): string
    {
        return view($this->getView(), [
            $this->getVarKey() => $this->value,
        ])->render();
    }
}
