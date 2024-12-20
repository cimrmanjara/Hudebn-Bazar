<?php

declare(strict_types=1);

namespace Ondra\App\Adverts\Infrastructure;

use Nette\Database\Explorer;
use Nette\Database\Connection;
use Nette\Database\Table\Selection;
use Ondra\App\Adverts\Application\IAdvertReadRepository;
use Ondra\App\Adverts\Application\Query\DTOs\AdvertDetailDTO;
use Ondra\App\Adverts\Application\Query\DTOs\AdvertOverviewDTO;
use Ondra\App\Adverts\Application\Query\DTOs\SearchCriteria;

final class DatabaseAdvertReadRepository implements IAdvertReadRepository
{
    private const TABLE_JOIN = 'adverts 
                LEFT JOIN items ON adverts.id = items.id 
                LEFT JOIN states ON items.state_id = states.id
                LEFT JOIN users ON adverts.seller_id = users.id
                LEFT JOIN subsubcategories ON items.subsubcategory_id = subsubcategories.id
                LEFT JOIN subcategories ON subsubcategories.subcategory_id = subcategories.id
                LEFT JOIN categories ON subcategories.category_id = categories.id
                LEFT JOIN (SELECT extension, id, item_images.item_id AS item_id FROM item_images RIGHT JOIN
                    (SELECT MIN(created_at) AS date, item_id FROM item_images GROUP BY item_id) AS i
                        ON i.date = item_images.created_at and i.item_id = item_images.item_id) AS item_images
                    ON items.id = item_images.item_id';
	public function __construct(private readonly Explorer $explorer, private readonly Connection $connection)
	{
	}
	public function getDetail(string $id): ?AdvertDetailDTO
	{
		$data = $this->connection->fetch("SELECT adverts.*, 
                items.name AS name, state_id, details, brand, subsubcategory_id, 
                states.name AS state, 
                subsubcategories.name AS subsubcategory_name, subcategory_id, 
                subcategories.name AS subcategory_name, category_id,
                categories.name AS category_name,
                item_images.id AS image_id, extension,
                username FROM "
                . self::TABLE_JOIN .
                " WHERE adverts.id = ?", $id);

		if (isset($data)) {
			$imagesData = $this->explorer->table('item_images')->select('id, extension')->where(
				'item_id = ?',
				$data->id,
			)->order('created_at ASC');

			$imageNames = [];
			foreach ($imagesData as $image) {
				$imageNames[] = $image->id . '.' . $image->extension;
			}
            if (isset($data->image_id)) {
                $mainImageName = $data->image_id . '.' . $data->extension;
            } else {
                $mainImageName = null;
            }
            unset($imageNames[0]);

            return new AdvertDetailDTO(
                $data->id,
                $data->name,
                $data->price,
                $data->quantity,
                $data->state_id,
                $data->state,
                $data->username,
                $data->seller_id,
                $data->details,
                (string) $data->created_at,
                $imageNames,
                $data->category_id,
                $data->subcategory_id,
                $data->subsubcategory_id,
                $data->category_name,
                $data->subcategory_name,
                $data->subsubcategory_name,
                $data->brand,
                (string) $data->updated_at,
                $mainImageName,
            );
		}
		return null;
	}
	public function generateWhere(SearchCriteria $criteria): array
	{
		$where = [];

        if ($criteria->stateIds) {
            $where["state_id"] = $criteria->stateIds;
        }
        if ($criteria->brands) {
            $where["brand"] = $criteria->brands;
        }
        if ($criteria->subsubcategoryId) {
            $where["subsubcategory_id"] = $criteria->subsubcategoryId;
        } elseif ($criteria->subcategoryId) {
            $where["subcategories.id"] = $criteria->subcategoryId;
        } elseif ($criteria->categoryId) {
            $where["categories.id"] = $criteria->categoryId;
        }
        if ($criteria->sellerId) {
            $where["seller_id"] = $criteria->sellerId;
        }
        if ($criteria->minPrice) {
            $where["price >="] = $criteria->minPrice;
        }
        if ($criteria->maxPrice) {
            $where["price <="] = $criteria->maxPrice;
        }
        if ($criteria->expression) {
            $words = explode(" ", $criteria->expression);
            foreach ($words as $word) {
                $word = '%' . $word . '%';
                $where[] = $this->connection::literal('items.name ILIKE ? OR details     ILIKE ?', $word, $word);
            }
        }
		return $where;
	}
	public function generateOrderBy(SearchCriteria $criteria): string
	{
		switch ($criteria->orderBy) {
			case 'date':
				$orderBy = 'created_at';
				break;
			case 'price':
				$orderBy = 'price';
				break;
			default:
				throw new \Exception(
					'Invalid value in criteria order by: ' . $criteria->orderBy . ". Use predefined constants only.",
				);
		}

		switch ($criteria->direction) {
			case 'asc':
				$orderBy = $orderBy . ' ASC';
				break;
			case 'desc':
				$orderBy = $orderBy . ' DESC';
				break;
			default:
				throw new \Exception(
					'Invalid value in criteria direction: ' . $criteria->direction . ". Use predefined constants only.",
				);
		}
		return $orderBy;
	}
	public function getCount(SearchCriteria $criteria): int
	{
		return $this->connection->fetch("SELECT COUNT(adverts.id) AS advert_count FROM " . self::TABLE_JOIN .
            " WHERE ?", $this->generateWhere($criteria))->advert_count;
	}
	public function getOverviews(SearchCriteria $criteria): array
	{
		$dataArray = $this->connection->query("SELECT adverts.*, 
                items.name AS name, details, brand, subsubcategory_id, 
                states.name AS state, 
                subsubcategories.name AS subsubcategory_name, subcategory_id,
                item_images.id AS image_id, extension, 
                username 
                FROM " . self::TABLE_JOIN .
                " WHERE ? ORDER BY " . $this->generateOrderBy($criteria),
            $this->generateWhere($criteria)
        );
		$dtos = [];

		foreach ($dataArray as $data) {
			if (isset($data->image_id)) {
				$imageName = $data->image_id . '.' . $data->extension;
			} else {
				$imageName = null;
			}

			$dtos[] = new AdvertOverviewDTO(
				$data->id,
				$data->name,
				$data->price,
				$data->state,
				$data->username,
				$data->seller_id,
				$data->details,
				$data->subsubcategory_name,
                (string) $data->created_at,
				$data->brand,
                (string) $data->updated_at,
				$imageName,
			);
		}
		return $dtos;
	}
}
