<?php

use Illuminate\Support\Facades\Broadcast;

/* Broadcast::channel('kanban-pedidos', function ($user) {
    return $user->hasRole('gestor') || $user->hasRole('admin');
}); */

Broadcast::channel('kanban', function () {
    return true; 
});
