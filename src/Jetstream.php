<?php

namespace Filament\Jetstream;

use Illuminate\Support\Arr;

class Jetstream
{
    /**
     * Find the path to a localized Markdown resource.
     *
     * @param  string  $name
     * @return string|null
     */
    public static function localizedMarkdownPath($name)
    {
        $localName = preg_replace('#(\.md)$#i', '.' . app()->getLocale() . '$1', $name);

        return Arr::first([
            resource_path('markdown/' . $localName),
            resource_path('markdown/' . $name),
        ], function ($path) {
            return file_exists($path);
        });
    }

    public static function plugin(): JetstreamPlugin
    {
        return JetstreamPlugin::get();
    }
}
