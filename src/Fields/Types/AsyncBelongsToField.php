<?php

namespace Ginkelsoft\Buildora\Fields\Types;

use Ginkelsoft\Buildora\Exceptions\BuildoraException;
use Ginkelsoft\Buildora\Fields\Field;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AsyncBelongsToField extends Field
{
    protected ?string $relatedModel = null;
    public string $returnColumn = 'id';
    public string $displayColumn = 'name';
    public array $searchColumns = ['name'];
    protected ?Model $parentModel = null;
    public bool $createInForm = false;

    public function __construct(string $name, ?string $label = null, string $type = 'asyncBelongsTo')
    {
        parent::__construct($name, $label ?? ucfirst($name), $type);
    }

    public static function make(string $name, ?string $label = null, string $type = 'asyncBelongsTo'): self
    {
        return new self($name, $label, $type);
    }

    public function relatedTo(string $model): self
    {
        $this->relatedModel = $model;
        return $this;
    }

    public function setParentModel(Model $parentModel): self
    {
        $this->parentModel = $parentModel;
        return $this;
    }

    public function getRelatedModel(): string
    {
        if ($this->relatedModel) {
            return $this->relatedModel;
        }

        if ($this->parentModel && method_exists($this->parentModel, $this->name)) {
            return get_class($this->parentModel->{$this->name}()->getRelated());
        }

        throw new BuildoraException("AsyncBelongsToField: Related model for '{$this->name}' not found.");
    }


    public function searchColumns(array $columns): self
    {
        $this->searchColumns = $columns;
        return $this;
    }

    public function displayUsing(string $column): self
    {
        $this->displayColumn = $column;
        return $this;
    }

    public function pluck(string $returnColumn, string $displayColumn): self
    {
        $this->returnColumn = $returnColumn;
        $this->displayColumn = $displayColumn;
        return $this;
    }

    public function setValue(mixed $model): self
    {
        if ($model instanceof Model && method_exists($model, $this->name) && $model->exists) {
            $relation = $model->{$this->name}();

            if ($relation instanceof BelongsTo) {
                $relatedInstance = $relation->getResults();

                if ($relatedInstance) {
                    $this->value = $relatedInstance->{$this->displayColumn};
                } else {
                    $this->value = null;
                }
            } else {
                $this->value = null;
            }
        } else {
            $this->value = null;
        }

        return $this;
    }

    /**
     * Geef de zoek-URL voor AJAX requests.
     * Bijvoorbeeld: /buildora/async/user/search
     */
    public function getSearchUrl(): string
    {
        $modelSlug = Str::kebab(class_basename($this->getRelatedModel()));

        return route('buildora.async.search', [
            'model' => $modelSlug,
            'search_columns' => implode(',', $this->searchColumns),
            'display_column' => $this->displayColumn,
        ]);
    }

    /**
     * Haalt de tekstuele waarde op van de geselecteerde ID
     */
    public function getSelectedLabel(mixed $id): ?string
    {
        if (!$id) {
            return null;
        }

        $modelClass = $this->getRelatedModel();
        $record = $modelClass::find($id);
        return $record?->{$this->displayColumn};
    }

    /**
     * Let op: override voor compatibiliteit, maar haalt geen volledige lijst op.
     */
    public function getOptions(): array
    {
        return []; // geen preload, want async
    }
}
