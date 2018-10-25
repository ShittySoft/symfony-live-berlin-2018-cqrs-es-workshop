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

    /** @var array<string, null> */
    private $checkedInUsers = [];

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
        $anomaly = array_key_exists($username, $this->checkedInUsers);

        $this->recordThat(DomainEvent\UserCheckedIn::toBuilding(
            $this->uuid,
            $username
        ));

        if ($anomaly) {
            $this->recordThat(DomainEvent\CheckInAnomalyDetected::inBuilding(
                $this->uuid,
                $username
            ));
        }
    }

    public function checkOutUser(string $username) : void
    {
        $anomaly = ! array_key_exists($username, $this->checkedInUsers);

        $this->recordThat(DomainEvent\UserCheckedOut::ofBuilding(
            $this->uuid,
            $username
        ));

        if ($anomaly) {
            $this->recordThat(DomainEvent\CheckInAnomalyDetected::inBuilding(
                $this->uuid,
                $username
            ));
        }
    }

    protected function whenNewBuildingWasRegistered(DomainEvent\NewBuildingWasRegistered $event) : void
    {
        $this->uuid = Uuid::fromString($event->aggregateId());
        $this->name = $event->name();
    }

    protected function whenUserCheckedIn(DomainEvent\UserCheckedIn $event) : void
    {
        $this->checkedInUsers[$event->username()] = null;
    }

    protected function whenUserCheckedOut(DomainEvent\UserCheckedOut $event) : void
    {
        unset($this->checkedInUsers[$event->username()]);
    }

    protected function whenCheckInAnomalyDetected(DomainEvent\CheckInAnomalyDetected $event) : void
    {
        // unused
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
