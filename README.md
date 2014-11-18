# README #

## Dependencies ##
* Webserver, PHP, MySQL, PDO
* Composer

## How do I get set up? ##

* Place the code under a web accessible directory
* cd into project and run:
```
composer install
```
* Create a new database(and preferably a user too) for the project
* Load app/data/ggs.sql into newly created database
* Edit app/config/main.php to update database credentials
* Visit app at /ggs/index.php?r=post/list

## Who do I talk to? ##

* Repo owner or admin
* shoaibi@dotgeek.me
* imshoaibi @ skype

## Things i would have loved to incorporate ##
* Logging
* Unit testing
* Caching posts and comments (memory, or as serialized objects)

## License ##
Code is provided as is with no liability and terms whatsoever. It may turn your toaster to zombie, it may trigger doomsday device. Try at your own risk.