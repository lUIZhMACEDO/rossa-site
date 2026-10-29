<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Include CSS
    |--------------------------------------------------------------------------
    |
    | The modal uses Bootstrap CSS, if you don't use Bootstrap you will need
    | to set this parameter to true. This includes the modern-normalize css.
    |
    */
    'include_css' => false,

    /*
    |--------------------------------------------------------------------------
    | Include JS
    |--------------------------------------------------------------------------
    |
    | Livewire UI will inject the required Javascript in your blade template.
    | If you want to bundle the required Javascript you can set this to false
    | and add `require('vendor/wire-elements/modal/resources/js/modal');`
    | to your script bundler like webpack.
    |
    */
    'include_js' => false,

    /*
    |--------------------------------------------------------------------------
    | Modal Component Defaults
    |--------------------------------------------------------------------------
    |
    | Configure the default properties for a modal component.
    |
    | Note: The `modal_max_width` setting uses short keys to determine the
    | Bootstrap modal width class. If you are using the CustomModalComponent
    | for a Bootstrap implementation, the mapping is as follows:
    | - 'sm'  => 'modal-sm'
    | - 'md'  => '' (default Bootstrap modal size)
    | - 'lg'  => 'modal-lg'
    | - 'xl'  => 'modal-xl'
    | - '2xl' => 'modal-xxl'
    */
    'component_defaults' => [
        'modal_max_width' => 'lg',

        'close_modal_on_click_away' => true,

        'close_modal_on_escape' => true,

        'close_modal_on_escape_is_forceful' => true,

        'dispatch_close_event' => false,

        'destroy_on_close' => false,
    ],
];
