#!/usr/bin/env php
<?php

declare(strict_types=1);

use Interop\Container\ContainerInterface;
use Prooph\EventStore\EventStore;

(function () {
    /** @var ContainerInterface $container */
    $container = require __DIR__ . '/../container.php';

    $eventStore = $container->get(EventStore::class);

    $users = [];

    foreach ($eventStore->??? as $event) {
        // ...

        // TODO TODO TODO
        file_put_contents(
            __DIR__ . '/../public/building-' . $buildingId . '.json',
            json_encode($users[$buildingId])
        );
    }
})();

