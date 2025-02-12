<?php

declare(strict_types=1);

namespace Ondra\App\Users\Application\Query\DTOs;

final readonly class SellerProfileDTO implements IProfileDTO
{
	public function __construct(public string $description, public ProfileDTO $profile)
	{
	}
}
