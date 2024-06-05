#!/bin/bash
docker run -d -p 80:80 --name my-apache-php-app -v "$PWD":/var/www/html php:8.2-apache