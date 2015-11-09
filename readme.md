## How to use it

Place the files in the relevant directory on your webserver.

Edit config.php.example and enter the details you require and rename the file to 
	
	config.php

Then install the dependencies with composer
    
    php composer.phar install

## Troubleshooting

If you are having issues you can enable debug mode in config.php to print all variables on load. 

	$debug = "true"; 
