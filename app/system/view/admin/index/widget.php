<?php

use \yi\Auth;

return [
    'index' => [
        'main-right-menu' => function() {
            echo '<a-menu-item><a class="open-dialog" data-title="' . lang('Edit User Profile') . '" href="/system/admin/admin/edit?id=' . Auth::instance()->id . '">' . lang('User Profile') . '</a></a-menu-item>';
        }
    ]
];