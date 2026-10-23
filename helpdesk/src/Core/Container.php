<?php
declare(strict_types=1);

namespace App\Core;

/** Dependency Injection container แบบเรียบง่าย — ใช้ reflection สร้าง class ผ่าน constructor */
final class Container
{
    private array $bindings = [];
    private array $instances = [];

    public function bind(string $id, callable $factory): void
    {
        $this->bindings[$id] = $factory;
    }

    public function get(string $id): object
    {
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }
        if (isset($this->bindings[$id])) {
            return $this->instances[$id] = ($this->bindings[$id])($this);
        }
        $ref = new \ReflectionClass($id);
        $ctor = $ref->getConstructor();
        if (!$ctor) {
            return $this->instances[$id] = new $id();
        }
        $args = [];
        foreach ($ctor->getParameters() as $p) {
            $type = $p->getType();
            if ($type instanceof \ReflectionNamedType && !$type->isBuiltin()) {
                $args[] = $this->get($type->getName());
            } elseif ($p->isDefaultValueAvailable()) {
                $args[] = $p->getDefaultValue();
            }
        }
        return $this->instances[$id] = $ref->newInstanceArgs($args);
    }
}
