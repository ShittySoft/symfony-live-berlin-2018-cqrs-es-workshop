<?php

declare(strict_types=1);

namespace Specification;

use Assert\Assert;
use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\PendingException;
use Building\Domain\Aggregate\Building;
use Building\Domain\DomainEvent\CheckInAnomalyDetected;
use Building\Domain\DomainEvent\NewBuildingWasRegistered;
use Building\Domain\DomainEvent\UserCheckedIn;
use Prooph\EventSourcing\AggregateChanged;
use Prooph\EventSourcing\EventStoreIntegration\AggregateTranslator;
use Prooph\EventStore\Aggregate\AggregateType;
use Rhumsaa\Uuid\Uuid;

final class CheckInCheckOut implements Context
{
    /** @var AggregateChanged[] */
    private $pastEvents = [];

    /** @var AggregateChanged[]|null */
    private $recordedEvents;

    /** @var Building|null */
    private $building;

    /**
     * @Given a building was registered
     */
    public function aBuildingWasRegistered() : void
    {
        $this->pastEvents[] = NewBuildingWasRegistered::occur(
            Uuid::uuid4()->toString(),
            [
                'name' => 'Example',
            ]
        );
    }

    /**
     * @Given ":username" has checked into the building
     */
    public function hasCheckedIntoTheBuilding(string $username) : void
    {
        $this->pastEvents[] = UserCheckedIn::toBuilding(
            Uuid::uuid4(),
            $username
        );
    }

    /**
     * @When ":username" checks into the building
     */
    public function checksIntoTheBuilding(string $username) : void
    {
        $this->building()->checkInUser($username);
    }

    /**
     * @Then ":username" should have been checked into the building
     */
    public function shouldHaveBeenCheckedIntoTheBuilding(string $username) : void
    {
        /** @var UserCheckedIn $event */
        $event = $this->extractNextPendingEvent();

        Assert::that($event)->isInstanceOf(UserCheckedIn::class);
        Assert::that($event->username())->eq($username);
    }

    /**
     * @Given a check-in anomaly should have been detected for ":username"
     */
    public function aCheckInAnomalyShouldHaveBeenDetectedFor(string $username) : void
    {
        /** @var CheckInAnomalyDetected $event */
        $event = $this->extractNextPendingEvent();

        Assert::that($event)->isInstanceOf(CheckInAnomalyDetected::class);
        Assert::that($event->username())->eq($username);
    }

    private function extractNextPendingEvent() : AggregateChanged
    {
        if (null !== $this->recordedEvents) {
            return array_shift($this->recordedEvents);
        }

        $this->recordedEvents = (new AggregateTranslator())
            ->extractPendingStreamEvents($this->building());

        return array_shift($this->recordedEvents);
    }

    private function building() : Building
    {
        if ($this->building) {
            return $this->building;
        }

        return $this->building = (new AggregateTranslator())
            ->reconstituteAggregateFromHistory(
                AggregateType::fromAggregateRootClass(Building::class),
                new \ArrayIterator($this->pastEvents)
            );
    }
}
