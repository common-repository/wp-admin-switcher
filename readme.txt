=== WP Admin Switcher ===
Contributors: Sabeen Malik
Tags: administration , admin
Requires at least: 2.1.2
Tested up to: 2.3.1
Stable tag: 1.2

This plugin will help you quickly switch between wordpress admins.For details please see http://www.decodephp.com

== Description ==
It is probably a good idea to download from this link http://www.decodephp.com  
as the wordpress plugins download sections seems to miss the tempfiles directory or the file in it for some reason , 
this directory is very important for the plugin to work.

This plugin will help you quickly switch between wordpress admins by letting you select from a list of blogs you have entered in the 
WP Admin Switcher administration section. Assuming you are on the posts management page , and you select a different blog from the drop down
you will be immediately switched to that blogs posts management page. The plugin attempts to keep all functionality intact. When you are on the
other blogs page , you continue to see the drop down , you can select "Parent Site" from the list to jump back to the parent blog.

The plugin will also work with free blogs hosted at wordpress.com.


== Installation ==

CURL compiled with PHP is required.

Drop the folder extracted from the zip in your wordpress plugins directory.
Then go inside that folder and change permission of 
the tempfiles folder to 777. Then go inside the tempfiles folder and rename the file remove.htaccess to .htaccess

Activate the plugin then goto the "WP Switcher" link in your admin 
and add the blogs you want to manage.

Thats it!.

== Frequently Asked Questions ==

= Why do i keep getting redirected to the login page of the other blog? =
make sure you have entered the correct username , password and wp-admin URL. Sometimes http://www.abc.com/wp-admin/ will redirect to http://abc.com/wp-admin/ 
this means that the correct URL is http://abc.com/wp-admin/  and NOT http://www.abc.com/wp-admin/ 

== Screenshots ==

1. screenshot-1.jpeg