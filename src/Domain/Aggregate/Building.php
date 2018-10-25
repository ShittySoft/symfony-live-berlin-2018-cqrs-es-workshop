<?php

declare(strict_types=1);

namespace Building\Domain\Aggregate;

use Building\Domain\DomainEvent;
use Prooph\EventSourcing\AggregateRoot;
use Rhumsaa\Uuid\Uuid;

final class Building extends AggregateRoot
{
    /**
     * @var Uuid
     */
    private $uuid;

    /**
     * @var string
     */
    private $name;

    public static function new(string $name) : self
    {
        $self = new self();

        $self->recordThat(DomainEvent\NewBuildingWasRegistered::occur(
            (string) Uuid::uuid4(),
            [
                'name' => $name
            ]
        ));

        return $self;
    }

    public function checkInUser(string $username) : void
    {
        $this->recordThat(DomainEvent\UserCheckedIn::toBuilding(
            $this->uuid,
            $username
        ));
    }

    public function checkOutUser(string $username) : void
    {
        $this->recordThat(DomainEvent\UserCheckedOut::ofBuilding(
            $this->uuid,
            $username
        ));
    }

    protected function whenNewBuildingWasRegistered(DomainEvent\NewBuildingWasRegistered $event) : void
    {
        $this->uuid = Uuid::fromString($event->aggregateId());
        $this->name = $event->name();
    }

    protected function whenUserCheckedIn(DomainEvent\UserCheckedIn $event) : void
    {
        // empty on purpose
    }

    protected function whenUserCheckedOut(DomainEvent\UserCheckedOut $event) : void
    {
        // empty on purpose
    }

    /**
     * {@inheritDoc}
     */
    protected function aggregateId() : string
    {
        return (string) $this->uuid;
    }

    /**
     * {@inheritDoc}
     */
    public function id() : string
    {
        return $this->aggregateId();
    }
}
