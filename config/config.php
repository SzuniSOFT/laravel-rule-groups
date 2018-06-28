<?php

return [

    /*
     * Specify the directory where rule groups will be generated
     * This is relative to the app folder
     * */
    'directory' => 'RuleGroups',

    'laravel' => [

        /*
         * The delimiter character used by Laravel.
         * It's not recommended to modify unless Laravel would have been changed
         * the rule explode implementation.
         *
         * In this case you don't need to wait for further updates.
         * */
        'inline_rule_delimiter' => '|'

    ]

];