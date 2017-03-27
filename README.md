kunena-attachments-to-bbpress
=============================

This script allows you to import attachments from Kunena on Joomla into bbPress on Wordpress. It is recommended that you use this in conjunction with the [GD bbPress Attachments](https://wordpress.org/plugins/gd-bbpress-attachments/) plugin (or another bbPress attachment management plugin).

Usage is as follows:

-    configure the following settings in your `php.ini` file temporarily (just while running this import script):
    -    `allow_url_fopen` set to `True`
    -    `disable_functions` commented out
-    restart `php7.0-fpm` (or the equivalent service on your system) to apply the changes to `php.ini`
-    copy this script into the root directory of your wordpress install and edit the variables at the top, specifying the database connection information and URL of your Joomla site
-    load the script in your browser. It will tell you if it has successfully connected to your Joomla database and provide instructions to import the attachments in chunks so as to avoid PHP timeout errors
-    once done with the import, make sure to regenerate the thumbnails on your site using the [Regenerate Thumbnails](https://wordpress.org/plugins/regenerate-thumbnails/) plugin.
-    verify that the attachments are visible in your bbPress topics and then remove this script and reset your `php.ini` values
