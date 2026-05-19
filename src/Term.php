<?php

namespace Rareloop\Lumberjack;

use Illuminate\Support\Arr;
use Spatie\Macroable\Macroable;
use Timber\Term as TimberTerm;

class Term extends TimberTerm
{
    use Macroable;

    /**
     * Get the Term class associated with a taxonomy slug.
     *
     * @param string $taxonomy
     * @return string|null
     */
    public static function termClass(string $taxonomy): ?string
    {
        $classMap = apply_filters('timber/term/classmap', [
            'post_tag' => Term::class,
            'category' => Term::class,
        ]);

        return Arr::get($classMap, $taxonomy);
    }
}
