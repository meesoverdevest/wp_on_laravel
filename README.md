
## WP-on-Laravel

A Laravel Package which makes use of WordPress to easily manage blog content, but also makes it possible to access all WordPress' posts, categories and tags through Eloquent Models.
____

### Requirements
- WP CLI globally installed
- A valid ```APP_URL``` in your ```.env```-file

### 1 Run ```composer require meesoverdevest/wp_on_laravel```

### 2 Add service provider to config
Add the service provider in ```config/app.php```:
```php
 'providers' = [
   ...
   meesoverdevest\wp_on_laravel\WPServiceProvider::class
 ];
```
### 3 Run the installer
Run the following from your project folder:
```php artisan wol:install $password $email```

Replace $password and $email with your wished credentials to use in WordPress.

