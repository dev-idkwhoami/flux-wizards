<?php

namespace Idkwhoami\FluxWizards\Concretes;

use Closure;
use Idkwhoami\FluxWizards\Abstracts\Makeable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use Laravel\SerializableClosure\SerializableClosure;
use Livewire\Wireable;

class Step extends Makeable implements Wireable
{
    protected ?Step $parent = null;

    protected ?string $label = null;
    protected ?string $view = null;

    protected ?Closure $flow = null;

    /**
     * @var Step[]
     */
    protected array $children = [];

    /**
     * @var array<string, mixed>
     */
    protected array $validationRules = [];

    protected ?MessageBag $errors = null;

    public function is(string $name): bool
    {
        return $this->name === $name;
    }

    public function hasErrors(): bool
    {
        return $this->errors !== null;
    }

    public function isFirst(): bool
    {
        return $this->parent === null;
    }

    public function isLast(): bool
    {
        return $this->children === [];
    }

    /**
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getView(): string
    {
        return $this->view ?? $this->name;
    }

    /**
     * @return MessageBag|null
     */
    public function getErrors(): ?MessageBag
    {
        return $this->errors;
    }

    /**
     * @return Step|null
     */
    public function getParent(): ?Step
    {
        return $this->parent;
    }

    /**
     * @return array
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    protected function propagateErrors(MessageBag $errors): void
    {
        if (is_null($this->parent)) {
            $this->errors = $errors;
            return;
        }

        $this->parent->propagateErrors($errors);
    }

    public function validate(array $data): bool
    {
        $validator = Validator::make($data[$this->name], $this->validationRules);
        $validator->validate();

        return true;
    }

    public function resolveNext(array $data): ?Step
    {
        $childCount = count($this->children);

        if (!$this->flow && $childCount > 1) {
            throw new \Exception("Flow is not defined but step has more than one child.");
        }

        if (!$this->flow && $childCount === 1) {
            return $this->children[0];
        }

        $flowMatches = array_filter($this->children,
            fn(Step $child) => $this->flow->call($this, $this, Arr::dot($data), $child));

        if (count($flowMatches) !== 1) {
            throw new \Exception("Flow can not match more or less than one child.");
        }

        return $flowMatches[0];
    }

    public function children(array $children): Step
    {
        $this->children = $this->injectParentStep($children);
        return $this;
    }

    /**
     * The validation rules for the step data.
     *
     * <b>Warning</b>: The key will be prefixed like this `data.[step name].[rule key]`
     *
     * @param  array<string, mixed>  $rules
     * @return $this
     */
    public function rules(array $rules): Step
    {
        /*foreach ($rules as $key => $rule) {
            $this->validationRules["data.{$this->name}.$key"] = $rule;
        }*/
        $this->validationRules = $rules;

        return $this;
    }

    /**
     * The closure should return a boolean whether the given child is the next step.
     *
     * Example:
     * <pre>
     *      $this->flow(
     *          fn(Step $current, array $data, Step $next) =>
     *              $next->is('stepC')
     *              && $data['fieldName'] === false
     *      );
     * </pre>
     *
     * <b>Warning</b>: The passed closure will be called for each child, do not call anything besides the flow logic within the closure.
     *
     * @param  Closure(Step, array, Step): bool  $flow
     * @return $this
     */
    public function flow(Closure $flow): Step
    {
        $this->flow = $flow;
        return $this;
    }

    public function label(string $label): Step
    {
        $this->label = $label;
        return $this;
    }

    public function view(string $view): Step
    {
        $this->view = $view;
        return $this;
    }

    protected function injectParentStep(array $children): array
    {
        array_walk($children, fn(Step $child) => $child->parent = $this);
        return $children;
    }

    public function filled(): void
    {
        $this->children = $this->injectParentStep($this->children);
    }

    public function toLivewire(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'view' => $this->view,
            'validationRules' => $this->validationRules,

            'flow' => is_null($this->flow) ? null : serialize(new SerializableClosure($this->flow)),
            'children' => $this->children,
        ];
    }

    public static function fromLivewire($value): Step
    {
        return (new static(
            $value['name'],
        ))->fill($value);
    }
}
