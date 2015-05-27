RewriteEngine on
RewriteCond %{REQUEST_URI} !<?=self::$SITE_PATH?>/index.php$
RewriteCond %{REMOTE_HOST} !^000\.000\.000\.000
RewriteRule $ <?=self::$SITE_PATH?>/index.php$1

AddType application/vnd.ms-fontobject .eot
AddType font/ttf .ttf
AddType font/otf .otf
AddType application/x-font-woff .woff

Header add Access-Control-Allow-Origin "*"
Header set X-XSS-Protection 0