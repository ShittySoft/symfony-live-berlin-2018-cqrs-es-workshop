#!/usr/bin/env php
<?php

declare(strict_types=1);

namespace Projection;

use Building\Domain\Aggregate\Building;
use Building\Domain\DomainEvent\UserCheckedIn;
use Building\Domain\DomainEvent\UserCheckedOut;
use Interop\Container\ContainerInterface;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\Stream\StreamName;

(function () {
    /** @var ContainerInterface $container */
    $container = require __DIR__ . '/../container.php';

    $eventStore = $container->get(EventStore::class);

    /** @var array<string, array<string, null>> $users */
    $users = [];

    foreach ($eventStore->loadEventsByMetadataFrom(
        new StreamName('event_stream'),
        ['aggregate_type' => Building::class]
    ) as $event) {
        if ($event instanceof UserCheckedIn) {
            $users[$event->buildingId()->toString()][$event->username()] = null;
        }

        if ($event instanceof UserCheckedOut) {
            unset($users[$event->buildingId()->toString()][$event->username()]);
        }
    }

    array_walk($users, function (array $usernames, string $buildingId) : void {
        file_put_contents(
            __DIR__ . '/../public/building-' . $buildingId . '.json',
            json_encode(array_keys($usernames))
        );
    });
})();

