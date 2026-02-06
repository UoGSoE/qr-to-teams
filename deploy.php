<?php
namespace Deployer;

require 'recipe/laravel.php';

// Config
set('repository', 'git@versions.eng.gla.ac.uk:eng-itsupport/qr-to-teams.git');

add('shared_files', []);
add('shared_dirs', []);
add('writable_dirs', []);

// Hosts
host('gently.cose.gla.ac.uk')
    ->set('remote_user', 'deployer')
    ->set('deploy_path', '/var/www/qrtt.cose.gla.ac.uk/');

// Tasks
task('build', function () {
    run('cd {{release_path}} && npm install && npm run build');
});

// Disable migrate for now (no .env on server yet)
task('artisan:migrate', function () {
    // disabled
});

// Hooks
after('deploy:vendors', 'build');
after('deploy:failed', 'deploy:unlock');
