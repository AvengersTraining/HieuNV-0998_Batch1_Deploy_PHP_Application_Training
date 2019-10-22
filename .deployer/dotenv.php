<?php

namespace Deployer;

use Symfony\Component\Console\Input\InputOption;

option('dbhost', null,  InputOption::VALUE_REQUIRED, 'Specify database host.');
option('dbport', null, InputOption::VALUE_OPTIONAL, 'Specify database port.', 3306);
option('dbname', null, InputOption::VALUE_OPTIONAL, 'Specify database name.', 'larasun');
option('dbuser', null, InputOption::VALUE_REQUIRED, 'Specify database user.');
option('dbpass', null, InputOption::VALUE_REQUIRED, 'Specify database password.');
option('renew_app_key', null,  InputOption::VALUE_OPTIONAL, 'Whether if regenerate app key.', false);

set('env_path', '{{deploy_path}}/shared/.env');

// Remove the configuration cache file.
desc('Remove the configuration cache file');
task('artisan:config:clear', function () {
    run('{{bin/php}} {{release_path}}/artisan config:clear');
});

// Main task
desc('Update variable to the .env file');
task('deploy:config_env', function () {
    $appKey = false;

    // Get the old app key from the previous release.
    if (test('[ -f {{env_path}} ]')) {
        $appKey = escapeSlashChar(run('grep ^APP_KEY {{deploy_path}}/shared/.env | cut -d "=" -f 2-'));
    }

    // Copy .env.example to .env
    run('cp {{release_path}}/.env.example {{env_path}}');

    /**
     * Regenerate new app key if the option '--renew_app_key="true"' or empty app key.
     * Restore the old app key from the previous release.
     */
    if (input()->hasOption('renew_app_key') && input()->getOption('renew_app_key') === "true" || !$appKey) {
        invoke('key:generate');
    } else {
        run("sed -i 's/APP_KEY=.*/APP_KEY={$appKey}/' {{env_path}}");
    }

    /**
     * Update database configuration.
     */
    if (input()->hasOption('dbhost')) {
        set('dbhost', escapeSlashChar(input()->getOption('dbhost')));
        run('sed -i "s/DB_HOST=.*/DB_HOST={{dbhost}}/" {{env_path}}');
    }

    if (input()->hasOption('dbport')) {
        set('dbport', escapeSlashChar(input()->getOption('dbport')));
        run('sed -i "s/DB_PORT=.*/DB_PORT={{dbport}}/" {{env_path}}');
    }

    if (input()->hasOption('dbname')) {
        set('dbname', escapeSlashChar(input()->getOption('dbname')));
        run('sed -i "s/DB_DATABASE=.*/DB_DATABASE={{dbname}}/" {{env_path}}');
    }

    if (input()->hasOption('dbuser')) {
        set('dbuser', escapeSlashChar(input()->getOption('dbuser')));
        run('sed -i "s/DB_USERNAME=.*/DB_USERNAME={{dbuser}}/" {{env_path}}');
    }

    if (input()->hasOption('dbpass')) {
        set('dbpass', escapeSlashChar(input()->getOption('dbpass')));
        run('sed -i "s/DB_PASSWORD=.*/DB_PASSWORD={{dbpass}}/" {{env_path}}');
    }
    // End update database configuration.
});

// Set the application key.
desc('Set the application key');
task('key:generate', function () {
    run('{{bin/php}} {{release_path}}/artisan key:generate --ansi --force');
});

// Clear cached config before reconfigure env.
before('deploy:config_env', 'artisan:config:clear');

/**
 * Escape forward slash char for regex pattern.
 *
 * @param $string
 *
 * @return string
 */
function escapeSlashChar($string) {
    return preg_quote($string, '/');
}
