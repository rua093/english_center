<?php
declare(strict_types=1);

function db_transaction(PDO $pdo, callable $operation): mixed
{
	$pdo->beginTransaction();

	try {
		$result = $operation();
		$pdo->commit();
		return $result;
	} catch (Throwable $exception) {
		if ($pdo->inTransaction()) {
			$pdo->rollBack();
		}

		throw $exception;
	}
}
