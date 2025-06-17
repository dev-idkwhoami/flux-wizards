<?php

namespace Idkwhoami\FluxWizards\Concretes;

use Idkwhoami\FluxWizards\Abstracts\Makeable;
use Livewire\Wireable;

class Wizard extends Makeable implements Wireable
{
    protected ?Step $root = null;
    protected ?string $current = null;
    protected ?string $directory = 'steps';

    protected array $data = [];

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
    public function setCurrent(?string $current): void
    {
        $this->current = $current;
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
        return $this->data;
    }

    public function directory(?string $directory): Wizard
    {
        $this->directory = $directory;
        return $this;
    }

    public function getDirectory(): ?string
    {
        return $this->directory;
    }

    public function toLivewire(): array
    {
        return [
            'name' => $this->name,
            'current' => $this->current,
            'directory' =>  $this->directory,
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
