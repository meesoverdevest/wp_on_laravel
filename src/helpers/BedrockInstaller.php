<?php

namespace meesoverdevest\wp_on_laravel\helpers;

use meesoverdevest\wp_on_laravel\helpers\WPUtilities;
use App;
use DB;

class BedrockInstaller {
  
  protected $DBName = "";
  private $utils;
  private $wpDir = "";

  public function __construct() {
    $this->DBName = DB::connection()->getDatabaseName() . 'WP';
    $this->utils = new WPUtilities();
    // Set WordPress DB Name
    $this->utils->setEnvironmentValue('WPDB_DATABASE', $this->DBName);
    $this->wpDir = public_path('bedrock');
  }

  public function installation($pass, $mail) {    
    if( !$this->isInstalled() ) {
      // wp core download
      $this->createBedrock();

      // wp core config
      $this->installDotenv();
      $this->createEnvFile();

      // wp core install 
      $this->installWP($pass, $mail);

      // wp update siteurl
      // $this->updateWPUrl();

      // Add wp-relinquish VCS to bedrock composer
      $this->updateComposer();

      // Add WP-Relinquish plugin
      $this->installPlugin('hookpress');

      // Add WP-Relinquish webhook constant
      // $this->appendToApplication("define( 'RELINQUISH_TO', '". env('APP_URL') ."/wp/sync' );");
      // $this->appendToApplication("define( 'WPLANG', 'en_US' );");

      // $this->installRelinquishTheme();

      // Set right permalink structure for 'read more' link generation
      $this->checkPermalinkStructure();
      
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

    // Check / Create wp target dir
    if( ! file_exists( $this->wpDir ) ) {
      return false;
    }

    if( $this->utils->executeCommand($command) !== true ) {
      // Install WP CLI
      return false;
    } else {
      return true;
    }
  }

  private function createEnvFile() {

    if( ! file_exists( public_path('bedrock/.env') ) ) {

      $locale = App::getLocale() . '_' . strtoupper(App::getLocale());

      // Init .env file
      $command1 = "cd ". $this->wpDir ." && wp dotenv init --template=.env.example --with-salts --force";

      if( $this->utils->executeCommand($command1) !== true ) {
        // Install WP CLI
        echo ".Env template creation failed \n";
      } else {
        echo ".Env template created \n";
      }

      // Set DB_NAME, DB_USER, DB_PASS, WP_HOME
      $this->setDotenvValue('DB_NAME', $this->DBName);
      $this->setDotenvValue('DB_PASSWORD', env('DB_PASSWORD'));
      $this->setDotenvValue('DB_USER', env('DB_USERNAME'));
      $this->setDotenvValue('WP_HOME', env('APP_URL'));
      $this->setDotenvValue('WP_SITEURL', env('APP_URL') . '/bedrock/web/wp');

      // Set wp config file
      // $command = "cd ". $this->wpDir ." && wp core config --dbname=". $this->DBName ." --dbuser=".env('DB_USERNAME')." --dbpass=".env('DB_PASSWORD')." --locale=" . $locale;
      // if( $this->utils->executeCommand($command) !== true ) {
      //   // Install WP CLI
      //   echo "Config generation failed \n";
      // } else {
      //   echo "Config generated \n";
      // }

    }
  }

  private function installWP($pass, $mail) {
    $blogUrl = url('/') . '/wp';

    $command ="cd ". $this->wpDir ." && wp core install --url=".$blogUrl." --title=Blog --admin_user=admin --admin_password=".$pass." --admin_email=" . $mail;
    if( $this->utils->executeCommand($command) !== true ) {
      // Install WP 
      echo "Wordpress Installation failed \n";
    } else {
      echo "Wordpress Installation completed \n";
    }
  }

  private function createBedrock() {

    $command ="cd ". public_path() ." && composer create-project roots/bedrock";
    if( $this->utils->executeCommand($command) !== true ) {
      // Download WP
      echo "Download Failed \n";
    } else {
      echo "Download completed \n";
    }

  }

  private function checkPermalinkStructure() {

    $blogUrl = url('/') . '/wp';

    $command = "cd " . $this->wpDir . " && wp option update permalink_structure '/%postname%/'";
    if( $this->utils->executeCommand($command) !== true ) {
        // Install WP CLI
      echo "Permalink settings update failed \n";
    } else {
      echo "Permalink settings update succeeded \n";
    }  

  }

  private function updateWPUrl() {
    $blogUrl = url('/') . '/wp';
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
    // $command = 'cd '. $this->wpDir .' &&  composer require "wponrails/wp-relinquish:0.4.1"';
    $command = 'cd '. $this->wpDir .' &&  wp plugin install '. $plugin;

    if( $this->utils->executeCommand($command) !== true ) {
      // Install WP-Relinquish
      echo "Wordpress Plugin: ".$plugin." installation failed";
    } else {
      echo "Wordpress Plugin: ".$plugin." succesfully installed \n";

      $this->activatePlugin($plugin);
    }  
  }

  private function installDotenv() {
    $command = "cd ". $this->wpDir ." && wp package install aaemnnosttv/wp-cli-dotenv-command:^1.0";
    
    if( $this->utils->executeCommand($command) !== true ) {
      // Install WP CLI
      echo "WP Package: wp-cli-dotenv-command installation failed";
    } else {
      echo "WP Package: wp-cli-dotenv-command succesfully installed \n";
    }  
  }

  private function setDotenvValue($key, $value) {
    // Init .env file
    $command = "cd ". $this->wpDir ." && wp dotenv set " . $key ." " . $value;

    if( $this->utils->executeCommand($command) !== true ) {
      // Install WP CLI
      echo ".Env key: ". $key ." creation failed \n";
    } else {
      echo ".Env key: ". $key ." set with value: ".$value." \n";
    }
  }

  private function activatePlugin($plugin) {
    $command = "cd ". $this->wpDir ." && wp plugin activate " . $plugin;

    // Activate WP Plugin
    if( $this->utils->executeCommand($command) !== true ) {
      echo "Wordpress Plugin: ".$plugin." activation failed";
    } else {
      echo "Wordpress Plugin: ".$plugin." succesfully activated \n";
    }  
  }

  private function updateComposer() {
    $composer = file_get_contents(public_path('bedrock/composer.json'));
    $json = json_decode($composer);

    $new_vcs = (object)[];
    $new_vcs->type = "vcs";
    $new_vcs->url = "https://github.com/wponrails/wp-relinquish";

    $json->repositories[] = $new_vcs;

    file_put_contents(public_path('bedrock/composer.json'), json_encode($json));
  }
	
  private function appendToApplication($string) {
    file_put_contents(public_path('bedrock/config/application.php'), $string, FILE_APPEND);
  }

  private function installRelinquishTheme() {
    // https://github.com/wponrails/wp-relinquish-theme/archive/master.zip
    $command = "cd ". $this->wpDir ." && wp theme install https://github.com/wponrails/wp-relinquish-theme/archive/master.zip --activate";

    if( $this->utils->executeCommand($command) !== true ) {
      // Install WP CLI
      echo "Theme installation failed \n";
    } else {
      echo "Theme installed succesfully \n";
    }
  }
}
