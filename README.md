# Test assignment

##### Table of Contents
- [Notes about the API endpoints](#notes-about-the-api-endpoints)    
- [Application overview](#application-overview)  
- [Local setup](#local-setup)  
- [Run application](#run-application)
- [Remove application](#remove-application)
- [Debug application](#debug-application)  
  - [Xdebug](#xdebug)  
  - [Logs](#logs)  
- [Unit tests](#unit-tests)

This application connects to a fictional social media network. One endpoint registers a token and the other endpoint
fetches posts made by fictional users across a 6-month period.

We would like you to add one extra calculation to this code. Currently, this
application calculates:

- Average character length of posts in given date range
- The longest post by character length in given date range  
- Total posts split by week number  

**1. Add the following calculation**:

- Average number of posts **per user per month**.

**2. Add at least one test of any kind. We leave the decision on the number and kind of tests to you**.

For the above please **do not FORK or make PRs to our repository**, instead clone it and
send us the link to a Pull Request / Merge Request to your clone of the repository so that
we can clearly see what you changed.

## Notes about the API endpoints

This application is using two custom API endpoints:

**POST: https://api.supermetrics.com/assignment/register**

This endpoint registers a token for use against the second endpoint.

PARAMS:
```
client_id: ju16a6m81mhid5ue1z3v2g0uh
email: your@email.address
name: Your Name
```

RETURNS:
```
sl_token: This token string should be used in the subsequent query. Please note that this token will only last 1 hour from when the REGISTER call happens. You will need to register and fetch a new token as you need it.
client_id: returned for informational purposes only
email: returned for informational purposes only
```



**GET: https://api.supermetrics.com/assignment/posts**

This endpoint fetches posts along with the number of pages using the registered token.

PARAMS:
```
sl_token: Token from the register call
page: integer page number of posts (1-10)
```

RETURNS:
```
page: What page was requested or retrieved
posts: 100 posts per page
```

## Application overview

This application is shipped with the Docker Compose environment and requires Docker to be installed locally and running.
If you're not familiar with Docker or don't have it locally, please reach out to 
[the official website](https://www.docker.com)
 to learn more and follow the Docker installation instructions to install it on your platform:   

[Docker for Mac](https://docs.docker.com/desktop/install/mac-install/)  
[Docker for Linux](https://docs.docker.com/desktop/get-started/)  
[Docker for Windows](https://docs.docker.com/desktop/install/windows-install/)

The test assignment application is containerized within two containers that have PHP-FPM and Nginx respectively. 
You don't need to build anything locally, the related images will be automatically pulled from the remote registry 
as soon as you run the application for the first time.

Included tools:
- PHP 8.1.12
- Xdebug 3.1.6
- PHPUnit 9.5.10
- Memcached
- composer

Dependencies: 
* `guzzlehttp/guzzle` - to make API calls
* `symfony/cache` - not to disturb the API much
* `vlucas/phpdotenv` - to keep some config data out of files

## Local setup

Once you have Docker up and running please perform the following steps:

**1. Working directory**

Change the current working directory to `php-assignment`.  

**2. Setup .env files**

Copy `/.env.dist` to `/.env`.  
Copy `/env/composer.env.dist` to `/env/composer.env`.  
Copy `/env/nginx.env.dist` to `/env/nginx.env`.  
Copy `/env/php-cli.env.dist` to `/env/php-cli.env`.  
Copy `/env/php-fpm.env.dist` to `/env/php-fpm.env`.  

Copy `/app/.env.dist` to `/app/.env`. Add required settings there. Finding out the required settings is part of 
the assignment.

## Run application

Please execute the following command to start the application:

    docker-compose up

Alternatively you can start the application containers in detached mode to suppress containers messages:

    docker-compose up --detach

Please see the [Logs section](#Logs) for more details about log messages. 

If you run the application for the first time, this will pull two images from the remote repository (~125Mb), 
create `sm_assignment_app_web` and `sm_assignment_app_php_fpm` containers in the `sm_assignment` Compose project and 
run the `composer install` command.

The container will be listening on port `7777` on your `localhost`, you can access the application main page using the 
following URL: [http://localhost:7777](http://localhost:7777).

## Remove application

As soon as you are done with the test assignment you can stop the application:

    docker-compose down

This will stop the application and remove containers & network.

You can also remove the images associated with the test assignment and free ~125MB of your disk space:

    docker image rm superrakhmanchuk/sm_assignment_app_php_fpm
    docker image rm superrakhmanchuk/sm_assignment_app_web

## Debug application

### Xdebug

The application comes with pre-installed Xdebug. You can enable and configure it separately for PHP-FPM and PHP cli
in the following files:
- `/env/php-cli.env`
- `/env/php-fpm.env`

The `Xdebug` section in the files above allows you to fine tune this tool according to your needs:
   ```` 
    PHP_XDEBUG_ENABLE=no
    PHP_XDEBUG_IDE_KEY=SM_ASSIGNMENT
    PHP_XDEBUG_MODE=debug
    PHP_XDEBUG_START_WITH_REQUEST=yes
    PHP_XDEBUG_CLIENT_HOST=docker.for.mac.localhost
    PHP_XDEBUG_CLIENT_PORT=9003
    PHP_XDEBUG_DISCOVER_CLIENT_HOST=yes
    PHP_XDEBUG_CLI_COLOR=1
    PHP_XDEBUG_VAR_DISPLAY_MAX_DEPTH=10
    PHP_XDEBUG_LOG_LEVEL=0
    PHP_IDE_CONFIG=serverName=sm_assignment 
  ```` 

Once you made changes to these files, please remember to restart the application:

    docker-compose down && docker-compose up

Alternatively you can restart the application in detached mode:

    docker-compose down && docker-compose up --detach

### Logs

By default, PHP sends logs to the container `STDERR` and `STDOUT`. 

Please run the following command to see the logs:

    docker-compose logs

Alternatively you can see combined PHP and Nignx logs interactively by restarting the application in
non-detached mode:

    docker-compose down && docker-compose up

## Unit tests

PHP Unit tests are performed inside the `sm_assignment_app_php_fpm` container, so first you need to connect to it by 
creating a terminal:

    docker exec -it sm_assignment_app_php_fpm /bin/bash

This command should take you to path `/srv/sm_assignment`, but make sure of that by running command `pwd`, otherwise 
change the working directory inside the container by running `cd /srv/sm_assignment`.

Run the tests with the following command:

    ./vendor/bin/phpunit -c ./tests/phpunit.xml
