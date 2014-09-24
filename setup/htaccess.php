RewriteEngine on
RewriteCond %{REQUEST_URI} !<?=$sSitePath?>/index.php$
RewriteCond %{REMOTE_HOST} !^000\.000\.000\.000
RewriteRule $ <?=$sSitePath?>/index.php$1

AddType application/vnd.ms-fontobject .eot
AddType font/ttf .ttf
AddType font/otf .otf
AddType application/x-font-woff .woff