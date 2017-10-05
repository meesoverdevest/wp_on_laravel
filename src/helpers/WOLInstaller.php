<?php

namespace meesoverdevest\wp_on_laravel\helpers;

use meesoverdevest\wp_on_laravel\helpers\WPUtilities;
use App;
use DB;

class WOLInstaller {
  
  protected $DBName = "";
  private $utils;
  private $wpDir = "";

  public function __construct() {
    $this->DBName = DB::connection()->getDatabaseName() . 'WP';
    $this->utils = new WPUtilities();
    // Set WordPress DB Name
    $this->utils->setEnvironmentValue('WPDB_DATABASE', $this->DBName);
    $this->wpDir = public_path('blog');
  }

  public function installation($pass, $mail) {
    $this->createWPFolder();
    
    if( !$this->isInstalled() ) {
      // wp core download
      $this->downloadWP();

      // wp core config
      $this->createConfigFile();

      // wp core install 
      $this->installWP($pass, $mail);
      // wp update siteurl
      $this->updateWPUrl();

      // Set right permalink structure for 'read more' link generation
      $this->checkPermalinkStructure();

      // Database sync hooks to wp-includes/functions.php
      $this->appendHooksToFunctions();
      
      return true;
      
    }
    // Installation exists
    return false;
  }

  public function checkSiteUrl() {
    // wp update siteurl
    $this->updateWPUrl();

    // Set right permalink structure for 'read more' link generation
    $this->checkPermalinkStructure();
  }
  
  public function WPCLI_exists() {
    // Check WP CLI Install
    $command = "wp";
    if( !$this->utils->command_exists($command) ) {
      // Install WP CLI
      return false;
    } else {
      return true;
    }
  }

  public function WPDB_exists() {
    // Create WordPress DB
    DB::connection()->statement('CREATE DATABASE IF NOT EXISTS '. $this->DBName); 
  }

  private function isInstalled() {
    // Check if wp is installed
    $command = "cd ". $this->wpDir ." && wp core is-installed";
    if( $this->utils->executeCommand($command) !== true ) {
      // Install WP CLI
      return false;
    } else {
      return true;
    }
  }

  private function createWPFolder() {
    // Check / Create wp target dir
    if( ! file_exists( public_path('blog') ) ) {
      mkdir( public_path('blog') );
    }

  }

  private function createConfigFile() {

    if( ! file_exists( public_path('blog/wp-config.php') ) ) {

      $locale = App::getLocale() . '_' . strtoupper(App::getLocale());

      // Set wp config file
      $command = "cd ". $this->wpDir ." && wp core config --dbname=". $this->DBName ." --dbuser=".env('DB_USERNAME')." --dbpass=".env('DB_PASSWORD')." --locale=" . $locale;
      if( $this->utils->executeCommand($command) !== true ) {
        // Install WP CLI
        echo "Config generation failed \n";
      } else {
        echo "Config generated \n";
      }

    }
  }

  private function installWP($pass, $mail) {
    $blogUrl = url('/') . 'blog';

    $command ="cd ". $this->wpDir ." && wp core install --url=".$blogUrl." --title=Blog --admin_user=admin --admin_password=".$pass." --admin_email=" . $mail;
    if( $this->utils->executeCommand($command) !== true ) {
      // Install WP 
      echo "Wordpress Installation failed \n";
    } else {
      echo "Wordpress Installation completed \n";
    }
  }

  private function downloadWP() {

    $command ="cd ". $this->wpDir ." && wp core download --version=4.8.1 --force";
    if( $this->utils->executeCommand($command) !== true ) {
      // Download WP
      echo "Download Failed \n";
    } else {
      echo "Download completed \n";
    }

  }

  private function checkPermalinkStructure() {

    $blogUrl = url('/') . '/blog';

    $command = "cd " . $this->wpDir . " && wp option update permalink_structure '/%postname%/'";
    if( $this->utils->executeCommand($command) !== true ) {
        // Install WP CLI
      echo "Permalink settings update failed \n";
    } else {
      echo "Permalink settings update succeeded \n";
    }  

  }

  private function updateWPUrl() {
    $blogUrl = url('/') . '/blog';
    $fields = ['home', 'siteurl'];

    foreach ($fields as $field) {
      $command = "cd " . $this->wpDir . " && wp option update ".$field." " . $blogUrl;
      if( $this->utils->executeCommand($command) !== true ) {
          // Install WP CLI
        echo "Wordpress Option field: ".$field." update failed \n When developing locally, make sure your APP_URL .env variable is set to the right development url!";
      } else {
        echo "Wordpress Option field: ".$field." updated \n";
      }  
    }
  }

  private function installPlugin($plugin) {
    $command = "cd ". $this->wpDir ." && wp plugin install " . $plugin . " --activate";

    if( $this->utils->executeCommand($command) !== true ) {
      // Install WP CLI
      echo "Wordpress Plugin: ".$plugin." installation failed";
    } else {
      echo "Wordpress Plugin: ".$plugin." succesfully installed \n";
    }  
  }

  private function appendHooksToFunctions() {
    $string = 'function publish_post_webhook($post_ID) {
  
  $url = "'. env('APP_URL') .'/wp/sync";
  $data = array("wp_id" => $post_ID);

  // use key "http" even if you send the request to https://...
  $options = array(
      "http" => array(
          "header"  => "Content-type: application/x-www-form-urlencoded\r\n",
          "method"  => "POST",
          "content" => http_build_query($data)
      )
  );
  $context  = stream_context_create($options);
  $result = file_get_contents($url, false, $context);
  if ($result === FALSE) { /* Handle error */ }

  // Code to send a tweet with post info

  return;
}

function delete_category_webhook($category_ID) {
  $url = "'. env('APP_URL') .'/wp/delete";
  $data = array("info" => array("wp_id" => $category_ID), "type" => "category");

  // use key "http" even if you send the request to https://...
  $options = array(
      "http" => array(
          "header"  => "Content-type: application/x-www-form-urlencoded\r\n",
          "method"  => "POST",
          "content" => http_build_query($data)
      )
  );
  $context  = stream_context_create($options);
  $result = file_get_contents($url, false, $context);
  if ($result === FALSE) { /* Handle error */ }

  // Code to send a tweet with post info

  return;
}

// Change to other webhooks
function delete_tag_webhook($tag_ID) {
  $url = "'. env('APP_URL') .'/wp/delete";
  $data = array("info" => array("wp_id" => $tag_ID ), "type" => "tag");

  // use key "http" even if you send the request to https://...
  $options = array(
      "http" => array(
          "header"  => "Content-type: application/x-www-form-urlencoded\r\n",
          "method"  => "POST",
          "content" => http_build_query($data)
      )
  );
  $context  = stream_context_create($options);
  $result = file_get_contents($url, false, $context);
  if ($result === FALSE) { /* Handle error */ }

  // Code to send a tweet with post info

  return;
}

function delete_post_webhook($post_ID) {
  $url = "'. env('APP_URL') .'/wp/delete";
  $data = array("info" => array("wp_id" => $post_ID), "type" => "post");

  // use key "http" even if you send the request to https://...
  $options = array(
      "http" => array(
          "header"  => "Content-type: application/x-www-form-urlencoded\r\n",
          "method"  => "POST",
          "content" => http_build_query($data)
      )
  );
  $context  = stream_context_create($options);
  $result = file_get_contents($url, false, $context);
  if ($result === FALSE) { /* Handle error */ }

  // Code to send a tweet with post info

  return;
}


add_action("delete_category", "delete_category_webhook");
add_action("delete_post", "delete_post_webhook");
add_action("pre_delete_term", "delete_tag_webhook");
add_action("publish_post", "publish_post_webhook");
add_action("save_post", "publish_post_webhook");

';

    // file_get_contents(__DIR__ . '/WPHooks.txt');

    file_put_contents(public_path('blog/wp-includes/functions.php'), $string, FILE_APPEND);
  }
	
}
