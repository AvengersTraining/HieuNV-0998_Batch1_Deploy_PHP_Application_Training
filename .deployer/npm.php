<?php

namespace Deployer;

# Npm
set('bin/npm', function () {
    return run('which npm');
});

desc('Install npm packages');
task('npm:install', function () {
    if (has('previous_release')) {
        if (test('[ -d {{previous_release}}/node_modules ]')) {
            run('cp -R {{previous_release}}/node_modules {{release_path}}');

            // If package-lock.json is unmodified, then skip running `npm install`
            if (!run('diff {{previous_release}}/package-lock.json {{release_path}}/package-lock.json')) {
                return;
            }
        }
    }

    run("cd {{release_path}} && {{bin/npm}} install");
});

desc('Build npm resources');
task('npm:run', function () {
    run("cd {{release_path}} && {{bin/npm}} run prod");
});

desc('Install npm packages with a clean slate');
task('npm:ci', function () {
    run("cd {{release_path}} && {{bin/npm}} ci");
});

# Yarn
set('bin/yarn', function () {
    return run('which yarn');
});

desc('Install yarn packages');
task('yarn:install', function () {
    if (has('previous_release')) {
        if (test('[ -d {{previous_release}}/node_modules ]')) {
            run('cp -R {{previous_release}}/node_modules {{release_path}}');

            // If yarn.lock is unmodified, then skip running `yarn install`
            if (!run('diff {{previous_release}}/yarn.lock {{release_path}}/yarn.lock')) {
                return;
            }
        }
    }

    run("cd {{release_path}} && {{bin/yarn}} install");
});

desc('Build yarn resources');
task('yarn:run', function () {
    run("cd {{release_path}} && {{bin/yarn}} prod");
});
