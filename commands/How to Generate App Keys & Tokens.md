How to generate App Key
=======================

```bash 
To generate an application key, you can use the following command in your terminal:

php artisan key:generate

``` This command will generate a new application key and set it in your .env file. The application key is used for encryption and should be kept secret. Make sure to run this command before deploying your application to production.

How to generate JWT tokens: 
===========================
To generate JWT tokens, you can use the following command in your terminal:

```bash
php artisan jwt:secret

``` This command will generate a new JWT secret key and set it in your .env file. The JWT secret key is used to sign and verify JSON Web Tokens (JWTs) and should be kept secret. Make sure to run this command before using JWT authentication in your application.
---

How to JWT REFRESH tokens: 
To generate JWT refresh tokens, you can use the following command in your terminal:

```bash

php artisan jwt:refresh

``` This command will generate a new JWT refresh token and set it in your .env file. The JWT refresh token is used to obtain a new access token when the current access token expires. Make sure to run this command before using JWT refresh tokens in your application.