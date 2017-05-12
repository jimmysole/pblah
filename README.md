# p-blah
p-Blah
---------------------------------------

    p-Blah is diverse, simple yet powerful forum management system.


    To setup p-Blah, please follow the steps below:



    Editing global.php & local.php



    Before we can run the setup process, we must edit global.php and local.php (they are located in the config/autoload directory).

    Let's open the files
    (We will only be editing the database part of the file). Don't edit any other sections!
    Locate the following code in global.php and local.php


    'db' => array(
        'driver' => 'Pdo',
        'dsn'    => 'mysql:dbname=pblah;host=localhost',
    ),

    'db' => array(
        'username' => 'username',
        'password' => 'password',
    ),


    Usually your web host provider allows you to create MySQL databases, if that is so, create a database
    named pblah and keep the dbname set to pblah. Your MySQL host is likely to be different than "localhost"
    so you will need to change this part to whatever your web host provider has set the MySQL host name to. However,
    if your MySQL host name is localhost, you need not change anything in global.php but instead only change the MySQL
    credentials in local.php to match your own. Once you have edited these files, upload them back into the config/autoload
    directory and fill out the setup form to complete the setup.
