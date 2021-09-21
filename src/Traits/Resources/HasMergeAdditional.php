<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Traits\Resources;

trait HasMergeAdditional
{
    public function additional(array $data)
    {
        $this->additional = array_merge($this->additional, $data);

        return $this;
    }
}
