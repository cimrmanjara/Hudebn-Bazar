<?php

declare(strict_types=1);

namespace Ondra\App\Adverts\Domain;

final class Item
{
	public function __construct(
		private readonly string $id,
		private string $name,
		private string $details,
		private int $stateId,
		private array $itemImages,
		private int $subsubcategoryId,
		private ?string $brand = null,
	) {
	}

	public function getBrand(): ?string
	{
		return $this->brand;
	}

	public function setBrand(string $brand): void
	{
		$this->brand = $brand;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function setName(string $name): void
	{
		$this->name = $name;
	}

	public function getDetails(): string
	{
		return $this->details;
	}

	public function setDetails(string $details): void
	{
		$this->details = $details;
	}

	public function getStateId(): int
	{
		return $this->stateId;
	}

	public function setSubsubcategoryId(int $subsubcategoryId): void
	{
		$this->subsubcategoryId = $subsubcategoryId;
	}

	public function getSubsubcategoryId(): int
	{
		return $this->subsubcategoryId;
	}

	public function setStateId(int $stateId): void
	{
		$this->stateId = $stateId;
	}

	public function getId(): string
	{
		return $this->id;
	}

	public function getItemImages(): array
	{
		return $this->itemImages;
	}

	public function setItemImages(array $itemImages): void
	{
		$this->itemImages = array_slice($itemImages, 0, (int) $_ENV['MAX_IMAGES_PER_ADVERT']);
	}
}
