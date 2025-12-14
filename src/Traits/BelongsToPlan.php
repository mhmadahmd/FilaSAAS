<?php

namespace Mhmadahmd\Filasaas\Traits;

use Mhmadahmd\Filasaas\Models\Plan;

trait BelongsToPlan
{
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
}
