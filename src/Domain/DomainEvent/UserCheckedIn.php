<?php

declare(strict_types=1);

namespace Building\Domain\DomainEvent;

use Prooph\EventSourcing\AggregateChanged;
use Rhumsaa\Uuid\Uuid;

final class UserCheckedIn extends AggregateChanged
{
    public static function toBuilding(Uuid $buildingId, string $username) : self
    {
        return self::occur($buildingId->toString(), ['username' => $username]);
    }
    public function username() : string
    {
        return $this->payload['username'];
    }

    public function buildingId() : Uuid
    {
        return Uuid::fromString($this->aggregateId());
    }
}
