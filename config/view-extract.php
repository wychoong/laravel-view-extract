<?php

// config for Wychoong/ViewExtract
return [
    /**
     * Exclude views when resync views (when using sync all)
     */
    'exclude' => [
        // 'namespace::foo.bar.blade-name'
    ],

    /**
     * Only sync these views (when using sync all)
     *     - `only` take priority over `exclude
     *     - if same view listed in `exclude` it will still be excluded
     */
    'only' => [
        // 'namespace::foo.bar.blade-name'
    ],

];
