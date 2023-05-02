#!/bin/bash
set -e

PHP_INI_RECOMMENDED="$PHP_INI_DIR/php.ini-development"

ln -sf "$PHP_INI_RECOMMENDED" "$PHP_INI_DIR/php.ini"

cp $PHP_INI_DIR/templates/zz-php.ini $PHP_INI_DIR/conf.d/zz-php.ini
cp $PHP_INI_DIR/templates/zz-xdebug.ini $PHP_INI_DIR/conf.d/zz-xdebug.ini

### Configure PHP
[ ! -z "${PHP_MEMORY_LIMIT}" ] && sed -i "s|{{PHP_MEMORY_LIMIT}}|${PHP_MEMORY_LIMIT}|g" "$PHP_INI_DIR/conf.d/zz-php.ini"
[ ! -z "${PHP_UPLOAD_MAX_FILESIZE}" ] && sed -i "s|{{PHP_UPLOAD_MAX_FILESIZE}}|${PHP_UPLOAD_MAX_FILESIZE}|g" "$PHP_INI_DIR/conf.d/zz-php.ini"
[ ! -z "${PHP_POST_MAX_SIZE}" ] && sed -i "s|{{PHP_POST_MAX_SIZE}}|${PHP_POST_MAX_SIZE}|g" "$PHP_INI_DIR/conf.d/zz-php.ini"
[ ! -z "${PHP_MAX_EXECUTION}" ] && sed -i "s|{{PHP_MAX_EXECUTION}}|${PHP_MAX_EXECUTION}|g" "$PHP_INI_DIR/conf.d/zz-php.ini"

### OPCache
if [[ "${PHP_OPCACHE_ENABLE}" =~ ^(yes|true|1|on)$ ]]; then
	sed -i "s|{{PHP_OPCACHE_ENABLE}}|${PHP_OPCACHE_ENABLE}|g" "$PHP_INI_DIR/conf.d/zz-php.ini"
	echo "OPCache is enabled"
else
	sed -i "s|{{PHP_OPCACHE_ENABLE}}|0|g" "$PHP_INI_DIR/conf.d/zz-php.ini"
	echo "OPCache is disabled"
fi

if [[ "${PHP_OPCACHE_ENABLE_CLI}" =~ ^(yes|true|1|on)$ ]]; then
	sed -i "s|{{PHP_OPCACHE_ENABLE_CLI}}|${PHP_OPCACHE_ENABLE_CLI}|g" "$PHP_INI_DIR/conf.d/zz-php.ini"
	echo "OPCache CLI is enabled"
else
	sed -i "s|{{PHP_OPCACHE_ENABLE_CLI}}|0|g" "$PHP_INI_DIR/conf.d/zz-php.ini"
	echo "OPCache CLI is disabled"
fi

if [[ "${PHP_OPCACHE_CONSISTENCY_CHECKS}" =~ ^(yes|true|1|on)$ ]]; then
	sed -i "s|{{PHP_OPCACHE_CONSISTENCY_CHECKS}}|${PHP_OPCACHE_CONSISTENCY_CHECKS}|g" "$PHP_INI_DIR/conf.d/zz-php.ini"
	echo "OPCache Consistency Checks is enabled"
else
	sed -i "s|{{PHP_OPCACHE_CONSISTENCY_CHECKS}}|0|g" "$PHP_INI_DIR/conf.d/zz-php.ini"
	echo "OPCache Consistency Checks is disabled"
fi

if [[ "${PHP_OPCACHE_VALIDATE_TIMESTAMPS}" =~ ^(yes|true|1|on)$ ]]; then
	sed -i "s|{{PHP_OPCACHE_VALIDATE_TIMESTAMPS}}|${PHP_OPCACHE_VALIDATE_TIMESTAMPS}|g" "$PHP_INI_DIR/conf.d/zz-php.ini"
	echo "OPCache Validate Timestamps is enabled"
else
	sed -i "s|{{PHP_OPCACHE_VALIDATE_TIMESTAMPS}}|0|g" "$PHP_INI_DIR/conf.d/zz-php.ini"
	echo "OPCache Validate Timestamps is disabled"
fi

sed -i "s|{{PHP_OPCACHE_MEMORY_CONSUMPTION}}|${PHP_OPCACHE_MEMORY_CONSUMPTION}|g" "$PHP_INI_DIR/conf.d/zz-php.ini"
sed -i "s|{{PHP_OPCACHE_MAX_ACCELERATED_FILES}}|${PHP_OPCACHE_MAX_ACCELERATED_FILES}|g" "$PHP_INI_DIR/conf.d/zz-php.ini"

### XDebug
if [[ "${PHP_XDEBUG_ENABLE}" =~ ^(yes|true|1|on)$ ]] && [[ "${PHP_XDEBUG_MODE}" != "off" ]]; then
	docker-php-ext-enable xdebug &>/dev/null || exit 1

	sed -i "s|{{PHP_XDEBUG_IDE_KEY}}|${PHP_XDEBUG_IDE_KEY}|g" "$PHP_INI_DIR/conf.d/zz-xdebug.ini"
	sed -i "s|{{PHP_XDEBUG_MODE}}|${PHP_XDEBUG_MODE}|g" "$PHP_INI_DIR/conf.d/zz-xdebug.ini"
	sed -i "s|{{PHP_XDEBUG_CLIENT_HOST}}|${PHP_XDEBUG_CLIENT_HOST}|g" "$PHP_INI_DIR/conf.d/zz-xdebug.ini"
	sed -i "s|{{PHP_XDEBUG_CLIENT_PORT}}|${PHP_XDEBUG_CLIENT_PORT}|g" "$PHP_INI_DIR/conf.d/zz-xdebug.ini"
	sed -i "s|{{PHP_XDEBUG_CLI_COLOR}}|${PHP_XDEBUG_CLI_COLOR}|g" "$PHP_INI_DIR/conf.d/zz-xdebug.ini"
	sed -i "s|{{PHP_XDEBUG_VAR_DISPLAY_MAX_DEPTH}}|${PHP_XDEBUG_VAR_DISPLAY_MAX_DEPTH}|g" "$PHP_INI_DIR/conf.d/zz-xdebug.ini"
	sed -i "s|{{PHP_XDEBUG_LOG_LEVEL}}|${PHP_XDEBUG_LOG_LEVEL}|g" "$PHP_INI_DIR/conf.d/zz-xdebug.ini"
	sed -i "s|{{PHP_XDEBUG_START_WITH_REQUEST}}|${PHP_XDEBUG_START_WITH_REQUEST}|g" "$PHP_INI_DIR/conf.d/zz-xdebug.ini"
	sed -i "s|{{PHP_XDEBUG_DISCOVER_CLIENT_HOST}}|${PHP_XDEBUG_DISCOVER_CLIENT_HOST}|g" "$PHP_INI_DIR/conf.d/zz-xdebug.ini"
	echo "Xdebug is enabled"

	sed -i "s|{{PHP_OPCACHE_JIT}}|disable|g" "$PHP_INI_DIR/conf.d/zz-php.ini"
	sed -i "s|{{PHP_OPCACHE_JIT_BUFFER_SIZE}}|0|g" "$PHP_INI_DIR/conf.d/zz-php.ini"
	echo "JIT is disabled"
else
	echo "Xdebug is disabled"

	sed -i "s|{{PHP_OPCACHE_JIT}}|${PHP_OPCACHE_JIT}|g" "$PHP_INI_DIR/conf.d/zz-php.ini"
	sed -i "s|{{PHP_OPCACHE_JIT_BUFFER_SIZE}}|${PHP_OPCACHE_JIT_BUFFER_SIZE}|g" "$PHP_INI_DIR/conf.d/zz-php.ini"
	echo "JIT is enabled"
fi

echo "Running composer install ..."
composer install --prefer-install=dist --dev
echo "Composer install finished"

exec docker-php-entrypoint "$@"
