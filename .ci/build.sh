#!/usr/bin/env bash
set -eu
composer install -n --no-dev -o
rm -f .env
cp .env.ci .env
php artisan clear
# Install Node dependencies..
cnpm install || (rm -rf node_modules/ && cnpm install ) # 如果NPM安装失败 删除node_modules 然后重试
npm run prod || (rm -rf node_modules/ && cnpm install && npm run prod ) #同上
# Run database migrations.
php artisan migrate
