J.O.'s LAMP Base Application Kitchen
http://truejay.com
===============================

I've structured a base application kitchen for LAMP developers.
This kitchen intends to reduce hassle and enables developers to easily start a new LAMP project.
The main ingredients are composed of "Codeigniter" framework with "Tank auth" authentication library, "Doctrine ORM" and "Zend" libraries.
Feel free to extend its' features and please don't hesitate to let me know if you find any bugs.

Ingredients
-----------

Codeigniter 1.7.2
  - Libraries
    - Tank auth (rewrote to use Doctrine ORM)
    - Facebook graph
    - Twitter
    - S3
    - Jquery pagination
    - Zend
  - Extended libraries
    - MY_Controller
    - MY_Form_validation
    - MY_Input
    - MY_Router
    - MY_Upload
  - Helpers
    - csv_helper
    - recaptcha_helper
  - Hooks
    - doctrine_profiler
  - Models
    - ci_sessions
    - login_attempts
    - user_autologin
    - user_profiles
    - users
  - View
    - Custom error 404 page
    - Header / Footer layout
 
Doctrine ORM 1.2
  - Cache: APC (default - enabled), memcached
  - Master / multiple slaves configurable
 
Requirements
------------

PHP 5
  - APC extension
MYSQL

Installation
------------

- Upload all files on server
- Define "html" as the web root folder
- Fill in required values in "applicaton/config/config.php" (each required input is commented as "FILL IN REQUIRED")
- Import "base_app.sql" into a selected database
