<?php

namespace Idkwhoami\FluxWizards\Concretes;

use Idkwhoami\FluxWizards\Abstracts\Makeable;
use Illuminate\Support\Facades\Session;
use Livewire\Wireable;

class Wizard extends Makeable implements Wireable
{
    protected ?Step $root = null;
    protected ?string $current = null;

    protected array $data = [];

    public function boot(): void
    {
        $this->data = Session::get($this->sessionKey('data'), []);
        $this->current = Session::get($this->sessionKey('current'), $this->root?->getName());
    }

    public function next(): bool
    {
        if (!$this->getCurrent()->validate($this->data)) {
            return false;
        }

        $next = $this->getCurrent()->resolveNext($this->data);
        $this->setCurrent($next?->getName());
        return !is_null($next);
    }

    public function previous(): bool
    {
        $previous = $this->getCurrent()->getParent();
        $this->setCurrent($previous?->getName());

        return !is_null($previous);
    }

    public function getCurrent(): Step
    {
        if (is_null($this->current)) {
            return $this->root;
        }

        return $this->findByName($this->current);
    }

    /**
     * @param  string|null  $current
     */
    protected function setCurrent(?string $current): void
    {
        $this->current = $current;
        Session::put($this->sessionKey('current'), $current);
    }

    protected function findByName(string $name): Step
    {
        $searchStep = function (Step $step) use (&$searchStep, $name): ?Step {
            if ($step->getName() === $name) {
                return $step;
            }

            foreach ($step->getChildren() as $child) {
                if ($result = $searchStep($child)) {
                    return $result;
                }
            }

            return null;
        };

        return $searchStep($this->root);
    }

    /**
     * @return Step[]
     */
    public function getAllSteps(): array
    {
        $steps = [];
        $traverse = function (Step $step) use (&$traverse, &$steps) {
            $steps[] = $step;
            foreach ($step->getChildren() as $child) {
                $traverse($child);
            }
        };
        $traverse($this->root);
        return $steps;
    }


    /**
     * @return string[]
     */
    public function getAllStepNames(): array
    {
        return array_map(fn (Step $step) => $step->getName(), $this->getAllSteps());
    }

    public function setData(array $data): void
    {
        $this->data = array_merge($this->data, $data);
        Session::put($this->sessionKey('data'), $this->data);
    }

    public function root(Step $root): Wizard
    {
        $this->root = $root;
        return $this;
    }

    public function getRoot(): ?Step
    {
        return $this->root;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return Session::get($this->sessionKey('data'), $this->data);
    }

    protected function sessionKey(string $key): string
    {
        return "flux-wizards::wizard::{$this->name}::$key";
    }

    public function toLivewire(): array
    {
        return [
            'name' => $this->name,
            'current' => $this->current,
            'root' => $this->root,
            'data' => $this->data,
        ];
    }

    public static function fromLivewire($value): Wizard
    {
        return (new static($value['name']))
            ->fill($value);
    }
}
