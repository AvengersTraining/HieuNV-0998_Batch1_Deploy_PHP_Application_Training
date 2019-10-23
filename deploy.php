<?php
namespace Deployer;

/**
 * Laravel recipe.
 * https://github.com/deployphp/deployer/blob/master/recipe/laravel.php
 */
require 'recipe/laravel.php';

require __DIR__ . '/.deployer/npm.php';
require __DIR__ . '/.deployer/dotenv.php';

use Symfony\Component\Console\Input\InputOption;

inventory(__DIR__ . '/.deployer/hosts.yml');

option('freshdb', null, InputOption::VALUE_OPTIONAL, 'Whether if fresh database.', false);

// Project name
set('application', 'Larasun');

// Project repository
set('repository', 'git@github.com:hieunv-0998/larasun.git');

// [Optional] Allocate tty for git clone.
set('git_tty', false);

set('writable_mode', 'acl');

// Specify http user
set('http_user', 'deploy');

/**
 * Prevent run "php artisan" before "composer install"
 * (https://github.com/deployphp/deployer/blob/master/recipe/laravel.php#L39)
 */
set('laravel_version', '5.8');

// Shared files/dirs between deploys
add('shared_files', []);
add('shared_dirs', []);

// Writable dirs by web server
add('writable_dirs', []);

// Tasks

// Database migration
desc('Database migration');
task('db:migrate', function () {
    if (input()->hasOption('freshdb') && input()->getOption('freshdb') === "true") {
        invoke('artisan:migrate:fresh');

        return;
    }

    invoke('artisan:migrate');
});

// Seed database after migrating database.
after('db:migrate', 'artisan:db:seed');

// [Optional] if deploy fails, automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Main task
desc('Deploy the project');
task('deploy', [
    'deploy:info',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'deploy:vendors',
    'artisan:optimize:clear',
    //'deploy:config_env',
    'deploy:writable',
    'artisan:storage:link',
    'artisan:queue:restart',
    'db:migrate',
    'yarn:install',
    'yarn:run',
    'artisan:view:cache',
    'artisan:config:cache',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
]);
after('deploy', 'success');
