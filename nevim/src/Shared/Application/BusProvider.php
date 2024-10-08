<?php

declare(strict_types=1);

namespace Ondra\App\Shared\Application;

use Contributte\Messenger\Bus\CommandBus;
use Contributte\Messenger\Bus\QueryBus;
use Ondra\App\Shared\Application\Command\CommandRequest;
use Ondra\App\Shared\Application\Query\Query;

final class BusProvider
{
	public function __construct(
		protected CommandBus $commandBus,
		protected QueryBus $queryBus,
	) {
	}

	public function sendCommand(CommandRequest $command): void
	{
		$this->commandBus->handle($command);
	}

	/**
	 * @phpstan-template T of Query
	 * @phpstan-param T $query
	 * @phpstan-return T
	 */
	public function sendQuery(
		Query $query,
	): Query {
		return $this->queryBus->query($query);
	}
}
