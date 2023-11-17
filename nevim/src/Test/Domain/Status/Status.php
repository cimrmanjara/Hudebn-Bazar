<?php

declare(strict_types=1);

namespace Ondra\App\Test\Domain\Status;

final class Status
{
	public function __construct(public readonly int $id, public readonly string $name)
	{
	}
}
