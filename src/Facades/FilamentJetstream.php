<?php

namespace FilamentJetstream\FilamentJetstream\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \FilamentJetstream\FilamentJetstream\FilamentJetstream
 */
class FilamentJetstream extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \FilamentJetstream\FilamentJetstream\FilamentJetstream::class;
    }
}
