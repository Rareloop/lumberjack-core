<?php

// Backwards compatibilty alias for Tightenco collections
$aliases = [
    Illuminate\Support\Collection::class => Tightenco\Collect\Support\Collection::class,
    Illuminate\Support\Arr::class => Tightenco\Collect\Support\Arr::class,
    Illuminate\Support\HigherOrderCollectionProxy::class => Tightenco\Collect\Support\HigherOrderCollectionProxy::class,
    Illuminate\Support\Traits\Macroable::class => Tightenco\Collect\Support\Traits\Macroable::class,
];

foreach ($aliases as $illuminate => $tighten) {
    if (!class_exists($tighten) && !interface_exists($tighten) && !trait_exists($tighten)) {
        class_alias($illuminate, $tighten);
    }
}
