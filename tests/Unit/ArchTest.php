<?php

declare(strict_types=1);

arch()->preset()->php();
arch()->preset()->security();
arch()
    ->expect('Vptrading\ChapaLaravel')
    ->toUseStrictTypes()
    ->not->toUse(['die', 'dd', 'dump']);
