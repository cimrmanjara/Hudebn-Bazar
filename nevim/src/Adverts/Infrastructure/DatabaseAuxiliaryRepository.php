<?php

declare(strict_types=1);

namespace Ondra\App\Adverts\Infrastructure;

use Nette\Database\Connection;
use Nette\Database\Explorer;
use Ondra\App\Adverts\Application\IAuxiliaryRepository;

final class DatabaseAuxiliaryRepository implements IAuxiliaryRepository
{
	public function __construct(private readonly Explorer $explorer, private readonly Connection $connection)
	{
	}

    public function getCategories(): array
    {
        return $this->getSubordinateCategories();
    }
    public function getSubcategories(): array
    {
        $rows = $this->connection->query("SELECT id, name, sc1.higher_id as superordinate
            FROM categories
            LEFT OUTER JOIN superordinate_category AS sc1 ON id = sc1.lower_id
            LEFT OUTER JOIN superordinate_category AS sc2 ON sc1.higher_id = sc2.lower_id
            WHERE sc1.higher_id IS NOT NULL AND sc2.higher_id IS NULL");
        $result = [];
        foreach ($rows as $row) {
            $result[$row->superordinate][$row->id] = $row->name;
        }
        foreach ($this->getCategories() as $categoryId => $_){
            if (!isset($result[$categoryId])){
                $result[$categoryId] = [];
            }
        }
        return $result;
    }
    public function getSubsubcategories(): array
    {
        $rows = $this->connection->query("SELECT id, name, sc1.higher_id as superordinate
            FROM categories
            LEFT OUTER JOIN superordinate_category AS sc1 ON id = sc1.lower_id
            LEFT OUTER JOIN superordinate_category AS sc2 ON sc1.higher_id = sc2.lower_id
            WHERE sc2.higher_id IS NOT NULL");
        $result = [];
        foreach ($rows as $row) {
            $result[$row->superordinate][$row->id] = $row->name;
        }
        foreach ($this->getSubcategories() as $subcategories) {
            foreach ($subcategories as $subcategoryId => $_) {
                if (!isset($result[$subcategoryId])) {
                    $result[$subcategoryId] = [];
                }
            }
        }
        return $result;
    }
    public function getSubordinateCategories(?int $superordinateId = null): array
    {
        if (isset($superordinateId)) {
            $rows = $this->connection->query("SELECT id, name FROM categories LEFT JOIN superordinate_category ON id = lower_id WHERE higher_id = ?", $superordinateId);
        }
        else {
            $rows = $this->connection->query("SELECT id, name FROM categories LEFT OUTER JOIN superordinate_category ON id = lower_id WHERE higher_id IS NULL");
        }
        $result = [];
        foreach ($rows as $row) {
            $result[$row->id] = $row->name;
        }
        return $result;
    }

	public function getCategoryName(int $id): ?string
	{
		return $this->explorer->table('categories')->where('id', $id)->select('name')->fetch() ?->name;
    }
}