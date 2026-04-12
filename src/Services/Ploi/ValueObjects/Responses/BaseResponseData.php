<?php

namespace Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses;

use Illuminate\Support\Collection;
use Livewire\Wireable;
use ReflectionProperty;

readonly class BaseResponseData implements Wireable
{
    public static function toCollection(array $items): Collection
    {
        return collect($items)
            ->map(fn (array $item) => static::fromLivewire($item));
    }

    public function toLivewire(): array
    {
        $reflect = new \ReflectionClass($this);
        $props = $reflect->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED);

        $data = [];

        foreach ($props as $prop) {
            $data[$prop->getName()] = $this->{$prop->getName()};
        }

        return $data;
    }

    public static function fromLivewire($value): static
    {
        $constructor = (new \ReflectionClass(static::class))->getConstructor();

        if ($constructor) {
            $known = array_flip(array_map(
                fn (\ReflectionParameter $p) => $p->getName(),
                $constructor->getParameters()
            ));

            $value = array_intersect_key($value, $known);
        }

        return new static(...$value);
    }
}
