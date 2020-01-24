# THE FOLLOWING DESCRIBES THE STEPS TO CONFIGURE THIS APPLICATION
> This application was programmed on a framework i made myself, based in the MVC framework by Engineer Cesar Miranda Meza. I have already configured everything required so that this application works in development mode (production mode instructions are not explained here).

----
## What to do to simulate this application

> The only thing that would be required to do is to set your simulator's (i use Apache with XAMPP) Document root to the directory of this projects folder named as "public" (/knowledgeCityTest/public) and to then configure the database settings and that's it!.

----
## Example: How to set up project in Apache with XAMPP
1. Open XAMPP
2. Open Apache's Config file named as "httpd.conf"
3. Search for "DocumentRoot: The directory out of which you will serve your"
4. Below this text in Apache's Config file, you'll just have to replace the defined DocumentRoot for with a new one were you define the "public" folder of this application as explained before (/knowledgeCityTest/public) and that's it. Just save the file and then run the Apache simulator.

NOTE: If you need to define a different base ulr than the one defined for this project (localhost), please read the last section of this file (Extra information that could be helpful)
> Example of how i defined it in my computer.

    DocumentRoot "C:/xampp/htdocs/knowledgeCityTest/public"
    <Directory "C:/xampp/htdocs/knowledgeCityTest/public">

----
## Example: How to set up the database
1. Import the SQL file that you will find by the name of "kctest.sql" in the root directory of this project
2. Open the file "Config.php" (this file acts similar as those known as enviromental variable). You will find this file in the following directory: "/knowledgeCityTest/App/Config.php"
3. Change the value of the constant "DB_HOST" to the ip were you'll host your database.
4. Change the value of the constant "DB_NAME" to the name that you defined for the database of this project (eg. i named it kctest)
5. Change the value of the constant "DB_USERNAME" to the username with which you are hosting the database with
6. Change the value of the constant "DB_PASSWORD" to the password with which you are hosting the database with
    
----
## How do i display the application in a browser?
> All you have to do is to open your browser and type, in the url, "localhost" and that's all, enjoy.

----
## EXTRA INFORMATION THAT COULD BE HELPFUL
If for any reason you have troubles running this web-app after following the instructions explained here, make sure you have installed composer in your computer. Then delete the vendor folder within this project (knowledgeCityTest/vendor) and then, on composer, run the command "composer update".

Also, withing the Config.php file mentioned in the section of how to set up your database, feel free to change (if needed) the constant "APP_DEV_URL" to the url that you are going to open with project with, accordingly to the one defined in your server of course. But if you change this value, you will also have to set that same new value to the javascript variable "APP_URL" localted in the "Config.js" file (/knowledgeCityTest/public/js/Config.js)