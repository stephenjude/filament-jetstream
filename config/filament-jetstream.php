<?php

// config for FilamentJetstream/FilamentJetstream
return [

    /*
     * Disable Jetstream and Fortify routes. If you want your users to still used
     */
    'enable_route' => [
        'fortify' => true,
        'jetstream' => false,
    ],

    'navigation_items' => [
        'profile' => [
            'display' => false,
            'sort' => 1,
        ],
        'team' => [
            'display' => false,
            'sort' => 2,
        ],
        'api_tokens' => [
            'display' => false,
            'sort' => 3,
        ],
    ],
];
