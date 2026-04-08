<?php
declare (strict_types=1);

return [
    \Kernel\Plugin\Handle\Database::class => \App\Plugin\VirtualCardShip\Handle\Database::class,
    \Kernel\Plugin\Handle\Ship::class => App\Plugin\VirtualCardShip\Handle\Ship::class
];