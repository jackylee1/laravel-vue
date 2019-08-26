<?php

namespace Deployer;

$composerHome = getenv("COMPOSER_HOME") ?: getenv("HOME") . '/.composer/';
include $composerHome . '/vendor/autoload.php';
require 'recipe/laravel.php';
require 'recipe/rsync.php';

$workSpace = realpath(dirname(__FILE__) . "/../../");
$cwd       = realpath(dirname(__FILE__)) . '/';
chdir($workSpace);
// Project name
set('application', 'wx.xinglin.ai');

// Project repository
//set('repository', 'git@git.coding.net:yueqilai/backend.git');

// [Optional] Allocate tty for git clone. Default value is false.
//set('git_tty', true);

// Shared files/dirs between deploys
add('shared_files', []);
add('shared_dirs', []);

// Writable dirs by web server
add('writable_dirs', []);
set('allow_anonymous_stats', false);

set('ssh_multiplexing', true);//注意 这里有坑

// Hosts
inventory($cwd . 'hosts.yml');

/**
 * RSYNC Patterns:
 *
 * If a pattern doesn't contain a /, it applies to the file name sans directory.
 * If a pattern ends with /, it applies to directories only.
 * If a pattern starts with /, it applies to the whole path from the directory that was passed as an argument to rsync.
 * any substring of a single directory component (i.e. never matches /); ** matches any path substring.
 */

set('rsync', [
    'exclude'       => [
        '.git',
        'deployer/',
        'ci/',
        '\.*',
    ],
    'exclude-file'  => false,
    'include'       => [],
    'include-file'  => false,
    'filter'        => [],
    'filter-file'   => false,
    'filter-perdir' => false,
    'flags'         => 'rzcE', // Recursive, with compress
    'options'       => ['delete'],
    'timeout'       => 60 * 5,
]);

set('rsync_src', $workSpace);
set('rsync_dest', '{{release_path}}');

//set('http_user', 'www');
//set('http_group', 'www');

// Tasks
//task('build', function () {
//    run('composer install -n --no-dev');
//});

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

desc('一系列seeder任务');
task('project:seed_others', function () {
    $output = run('{{bin/php}} {{release_path}}/artisan db:seed  --class=RbacTableSeeder  --force');
    writeln('<info>' . $output . '</info>');
});

task('project:init_agent', function () {
    $output = run('{{bin/php}} {{release_path}}/artisan init_agent');
    writeln('<info>' . $output . '</info>');
});

desc('对有migrate角色的服务器执行 migrate');
task('project:migrate_on_roles', [
    'artisan:migrate',
    'project:init_agent',
    'project:seed_others',
])->onRoles('migrate');

desc('对有queen角色的服务器执行 重启队列');
task('project:restart_queen', [
    'artisan:queue:restart',
])->onRoles('queen');

// Migrate database before symlink new release.
before('deploy:symlink', 'project:migrate_on_roles');
after('deploy:symlink', 'project:restart_queen');

desc('根据环境部署对应的配置');
task('deploy:env', function () {
    upload('.env.{{stage}}', '{{deploy_path}}/shared/.env');
});

desc('部署完成后运行小命令');
task('deploy:runCommand', function () {
    run('
    mkdir -p /var/log/app-log/;
    ln -sf {{deploy_path}}/shared/storage/logs/laravel.log /var/log/app-log/agent.laravel.log;
    ');
});

/**
 * Main task
 */
desc('Deploy your project');
task('deploy', [
    'deploy:info',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    //    'deploy:update_code',//使用rsync
    'rsync:warmup',//从服务器上已经有的release存档预热rsync
    'rsync',
    'deploy:env',
    'deploy:shared',
    //    'deploy:vendors',//已经本地安装
    'deploy:writable',
    //    'artisan:storage:link',//只有Laravel 5.3需要
    'artisan:view:clear',
    'artisan:cache:clear',
    'artisan:config:cache',
    //    'artisan:optimize',
    'deploy:symlink',
    'deploy:runCommand',
    'deploy:unlock',
    'cleanup',
]);
