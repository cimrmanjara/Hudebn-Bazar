<?php

declare(strict_types=1);

namespace Ondra\App\Adverts\Application\Query\Messages\Response;

use Ondra\App\Shared\Application\Query\Query;

final readonly class GetAdvertsCountResponse implements Query
{
	public function __construct(public int $count)
	{
	}
}
