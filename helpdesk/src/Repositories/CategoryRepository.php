<?php
declare(strict_types=1);

namespace App\Repositories;

final class CategoryRepository extends AbstractRepository
{
    protected string $table = 'categories';
    protected array $columns = ['name', 'description'];
}
