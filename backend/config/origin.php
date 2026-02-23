<?php 

return [
    /*
    |--------------------------------------------------------------------------
    | Allowed Origins
    |--------------------------------------------------------------------------
    |
    | Here you may specify the origins that are allowed to access your
    | application resources. This is useful for configuring CORS settings.
    | You can set multiple origins by separating them with commas.
    |
    */
    'allowed_origins' => array_values(array_filter(
        explode(',', env('ALLOWED_ORIGINS', ''))
    )),
];