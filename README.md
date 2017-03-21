
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

### 4 Run migration
After running the installation command (```php artisan wol:install $password $email```)

Run ```php artisan migrate``` to migrate the new migrations

### 5 Edit NGINX config

To enable outer access to the public WordPress installation you have to add the following to your website's nginx config:
```
server {
 ...
  location = /blog {
   return 301 /blog/wp-login.php;
  }

  location /blog/(wp-admin|wp-json) {
    try_files $uri $uri/ /blog/index.php?$query_string;
  }	

  location / {
    // standard settings
  }
 ...
}
```

Don't forget to run ```(sudo) service nginx restart```

### 6 Syncing Wordpress Posts, Categories and Tags 
```php
 use meesoverdevest\wp_on_laravel\helpers\WPAPI;
 
 function syncWP(){
   $WPAPI = new WPAPI();
   $WPAPI->syncWP();
 }
```

### 7 Using Wordpress Posts, Categories and Tags 

Use them freely inside your project. You can use the models like:
```php
 // WPPost 
 use meesoverdevest\wp_on_laravel\models\WPPost;
 
 // WPCategory 
 use meesoverdevest\wp_on_laravel\models\WPCategory;
 
 // Tag (managed by Cartalyst/Tags) 
 use meesoverdevest\wp_on_laravel\models\Tag;
 
 // Show WPCategories for post
 $posts = WPPost::where('parent', 0)->first()->categories();
 
 // Show WPosts for tag
 $tag = Tag::first()->posts();
 
 // Show children WPCategories for parent WPCategory
 $tag = WPCategory::first()->children();
 
```


====== TODO ======

1. Extend content retrieval methods
2. Create php artisan sync command
3. Add apache config example
