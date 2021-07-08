## This is a Docker Package via docker-compose.yml with the following services / containers:

1.  **php-fpm-nginx:**  NGINX server / reverse proxy with php8.x and variety of other tools. PORTS ["443:443"]
2.  **pacs:**  Instance of Orthanc with some custom configurations. PORTS ["4444:4242","8042:8042"] 
3.  **postgres_index-db:**  postgres database for Orthanc. PORTS ["5555:5432"]
4.  **mysql_db:**  MySQL databases for RIS / Portal Laravel Application and MWL / MPPS servers.  PORTS ["3333:3306"]
5.  **php_myadmin:**  PORTS '11080:80', convenience item
6.  **python_mpps:**  Development project to handle MPPS N_CREATE, N_SET, etc.  PORTS ["104:11112"], best tested with DVTk or CLI tools.
7.  **python_mwl_api:**  Development project running Flask to handle MWL features as addition or replacement for Orthanc MWL plug-in.

6 & 7 are under development.

### There is a Portal front-end that runs under the NGINX server as a Laravel Application.

To get started:

1.  Clone or Download the package.
2.  Being a composer and Node / NPM package for the Laravel application, you'll need composer and NPM installed on your system.

    Composer Link:  https://getcomposer.org/download
    Node.js & NPM:  https://docs.npmjs.com/downloading-and-installing-node-js-and-npm
    
    In order to use composer you'll also need php on your system to run artisan commands and composer itself.  Composer and NPM are not setup on the containers,
    so for now you'll have to run composer and node on the host system, mostly if you are interested in using the Laravel Portal.  Navigate to:
    
    ```nginx-home/laravel```
    
    to run composer update and npm.  Since vendor and node_modules are ignored my github you'll need to run those to update the package, although node is not really
    used at this point.  In the future, I may add composer to the docker container for convenience, but it is actually convenient to be able to run that from the host
    also.
    
3.  Copy / Rename .env.example to .env in the laravel folder

4.  Copy / Rename PACS_Integration/php-fpm-nginx/default.conf.example.conf to default.conf.  That is the config for nginx.

4.  Navigate to the root of the package and run:
```
docker-compose up --build (-d)
```
and it should build and initialize in not too much time, including creation and population of some test databases, located in:
```
/MySQL_DB
/OrthancInde
/OrthancStorage
```
Orthanc should be accessible through both the NGINX proxy and localhost 8042, with the SSC set to orthanc.test domain.  So the Portal is at https://orthanc.test and Orthanc through http://orthanc.test:8042 or https://orthanc.test/pacs, proxied.

I'll provide additional details later, but the python scripts are heavily commented:
```
PACS_Integration/python_mwl_api/scripts_log/api.py
PACS_Integration/python_mpps/scripts_log/mpps.py
PACS_Integration/pacs/python/combined.py
```
I would recommend trying to get the Portal working because there are some dev tools and demos available through that.  Configured user is:

allprofiles@orthanc.test / Testing!1 after pointing https://orthanc.test to your docker host IP.
