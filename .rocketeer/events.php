<?php

use Rocketeer\Facades\Rocketeer;

Rocketeer::addTaskListeners('deploy', 'before-symlink', function ($task) {
    // Yarn install and compile
    $task->runForCurrentRelease('yarn install');
    $task->runForCurrentRelease('yarn run prod');

    // Clear and cache files.
    $task->runForCurrentRelease('php artisan optimize:clear');
    $task->runForCurrentRelease('php artisan config:cache');
    $task->runForCurrentRelease('php artisan view:cache');

    // Database migration and seeding
    $task->runForCurrentRelease('php artisan migrate --seed --force');
});
