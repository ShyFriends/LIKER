
<div>
<center>
<img width='200' src="https://user-images.githubusercontent.com/43204507/57985512-f0387300-7aa3-11e9-8eea-a1cc4603d649.png">
</center>
</div>

# LIKER

**LIKER(Like Air)** is a customized health care platform. Now you can check your heart rate and aqi in current location. You can also check your health status by receiving real-time and historical information. In addition, a warning alarm is issued to the neighbors in case of a dangerous situation, so it has a more systematic system.

LIKER(Like Air) is an open source project developed by Qualcomm Institute Student B Team using Google API, Udoo/Polar Sensors etc..


```

## Test
-------------------

```bash
    $ cd mainapp
    $ python pdf2jpg.py -i ./input
```

## Installation
---------------
To install repository
```bash
    $ git clone https://github.com/ShyFriends/LIKER.git
    $ cd Team_B
```
You have to get Google Cloud Map API key for running this program.
```bash
    $ export GOOGLE_APPLICATION_CREDENTIALS="/my key location.json"
```

# Getting Started in Web
------------------------------

// Slim 3 MVC Skeleton

This is a simple skeleton project for Slim 3 that includes Doctrine, Twig, Flash messages and Monolog.

Base on https://github.com/akrabat/slim3-skeleton

## Slim 3 MVC Skeleton

This is a simple skeleton project for Slim 3 that includes Doctrine, Twig, Flash messages and Monolog.

Base on https://github.com/akrabat/slim3-skeleton

## Prepare

1. Create your project:

       `$ composer create-project -n -s dev vhchung/slim3-skeleton-mvc your-app`

1. Execute `your-app\sql\blog.sql` to create sample database (MySQL)
2. Change database connection settings at `entities_generator.php` and `app/settings.php`
3. Generate models (Doctrine entities):

```

$ cd your-app
$ php entities_generator.php

```

 Add namespace for each model: `namespace App\Model;`

 Notice: Delete all models before re-generate to update models.

### Run it:

1. `$ cd your-app`
2. `$ php -S 0.0.0.0:8888 -t public/`
3. Browse to http://localhost:8888

### Notice

Set `logs` and `cache` folder permission to writable when deploy to production environment

## Key directories

* `app`: Application code
* `app/src`: All class files within the `App` namespace
* `app/templates`: Twig template files
* `cache/twig`: Twig's Autocreated cache files
* `log`: Log files
* `public`: Webserver root
* `vendor`: Composer dependencies
* `sql`: sql dump file for sample database

## Key files

* `public/index.php`: Entry point to application
* `app/settings.php`: Configuration
* `app/dependencies.php`: Services for Pimple
* `app/middleware.php`: Application middleware
* `app/routes.php`: All application routes are here
* `app/src/controllers/HomeController.php`: Controller class for the home page
* `app/src/models/Post.php`: Entity class for post table
* `app/templates/home.twig`: Twig template file for the home page

## Contribute
----------------
* Issue Tracker: https://github.com/CAU-OSS-2019/team-project-team06/issues
* Source Code: https://github.com/CAU-OSS-2019/team-project-team06/

## Contribution guidelines
-----------------------
We use GitHub issues for tracking requests and bugs.

## License
------------------------
MIT license

# LIKER
