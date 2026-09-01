@props(['name', 'type' => 'o'])

<x-dynamic-component :component="'heroicon-' . $type . '-' . $name" {{ $attributes }} />
