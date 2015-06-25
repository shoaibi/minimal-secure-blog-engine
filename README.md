# README #

## What is this? ##
A minimalistic blog system focusing on security done as an exercise on a Saturday night. Check Features below for details on what can be done.

## How was it done? ##
* For the PHP/MySQL part I brewed a custom MVC character by character myself. It is very minimal and light.
* For the UI/JS: I opted to use JQuery as the purpose of the task was to test backend skills and I didn't want to indulge in cross browser JS issues.
* For the UI/CSS: I compiled a very minimal style.css

## Dependencies ##
* Webserver, PHP(5.5+), MySQL, PDO, JQuery v1.4+(bundled)
* Composer

## Setup ##

* Place the code under a web accessible directory. I am making the assmumption that the directory containing code is called ggs.
* cd into project and run:
```
composer install
```
* Create a new database(and preferably a user too) for the project
* Load app/data/ggs.sql into newly created database
* Edit app/config/main.php to update database credentials
* Visit app at /ggs/index.php?r=post/list  (You may need to replace the /ggs prefix to the path you have hosted code at relative to webroot)

## Features ##
* Add, Edit Post
* Add Comment
* Pages support infinite scrolling. Scrolling to page's end will load any previous data. This is true for both, posts and comments.
* Pages support auto-loading the new content(at 10 second interval by default). This is true for both, posts and comments.
* Security Features:
    * Strict Validation (XSS, SQL Injection, Remote Code Injection, Command Injection)
    * PDO statements with parameter binding (SQL Injection)
    * Checking Referrer for Post Requests (Spoofed Forms)
    * Honey Pot input for forms (Bot submissions)
    * CSRF Protection for forms (Spoofed forms with CSRF)
    * Session isn't used so Session Fixation and Hijacking attacks are avoided.


## Missing Features ##
* Preventing Timing Attacks against CSRF checks(specifically Models\Csrf::isValid())
* Managing datetimes for posts and comments
* Dynamic input id for CSRF and Honey Pot field
* Cron script to cleanup expired CSRF tokens from database
* Logging
* Unit Testing
* Caching
* Captcha
* Delete Post
* Edit Comment
* Delete Comment
* Advanced ORM Queries
* User Management
* Rights Management
* Comment and Post Moderation

## License ##
Code is provided as is with no liability and terms whatsoever. It may turn your toaster to zombie, it may trigger doomsday device. Try at your own risk.
