<?php

namespace Rareloop\Lumberjack;

use Spatie\Macroable\Macroable;
use Timber\PostType as TimberPostType;

final class PostType extends TimberPostType
{
    use Macroable;

    /**
     * Get the Post class associated with this post type.
     *
     * @return string|null
     */
    public function postClass(): ?string
    {
        return Post::postClass($this->name);
    }
}
