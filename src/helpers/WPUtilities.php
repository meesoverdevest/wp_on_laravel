<?php

namespace meesoverdevest\wp_on_laravel\helpers;
use App;

class WPUtilities {

  // Function to remove folders and files 
  public function rrmdir($dir) {
    if (is_dir($dir)) {
      $files = scandir($dir);
      foreach ($files as $file)
        if ($file != "." && $file != "..") $this->rrmdir("$dir/$file");
   		rmdir($dir);
    }
    else if (file_exists($dir)) unlink($dir);
  }

  // Function to Copy folders and files       
  public function rcopy($src, $dst) {
    if (file_exists ( $dst ))
      $this->rrmdir ( $dst );
    if (is_dir ( $src )) {
      mkdir ( $dst );
      $files = scandir ( $src );
      foreach ( $files as $file )
        if ($file != "." && $file != "..")
          $this->rcopy ( "$src/$file", "$dst/$file" );
    } else if (file_exists ( $src ))
      copy ( $src, $dst );
  }

  public function executeCommand($cmd) {
  	$output = array();
    $return_var = -1;

    $last_line = exec($cmd, $output, $return_var);

    // Success
    if($return_var === 0) {
    	return true;
    } else {
    	return false;
    }
  }

  public function setEnvironmentValue($environmentName, $newValue) {
    $envFile = file_get_contents(App::environmentFilePath());
    // WPDB_DATABASE=wp_on_laravelWP
    if( strpos( $envFile, $environmentName ) !== false ) {
      file_put_contents(
        App::environmentFilePath(), 
        str_replace(
          $environmentName . '=' . env($environmentName),
          $environmentName . '=' . $newValue,
          file_get_contents(App::environmentFilePath())
        ),
        '1'
      );
    } else {
    //   // dd($newValue);
      $newEnvVar = $environmentName . '=' . $newValue;
      $envFile .= "\r\n";
      $envFile .= $newEnvVar;
      file_put_contents(App::environmentFilePath(), $envFile, '1');
    }

    // // Reload the cached config       
    if (file_exists(App::getCachedConfigPath())) {
        Artisan::call("config:cache");
    }
  }

  public function command_exists ($command) {
    $whereIsCommand = (PHP_OS == 'WINNT') ? 'where' : 'which';

    $process = proc_open(
      "$whereIsCommand $command",
      array(
        0 => array("pipe", "r"), //STDIN
        1 => array("pipe", "w"), //STDOUT
        2 => array("pipe", "w"), //STDERR
      ),
      $pipes
    );
    if ($process !== false) {
      $stdout = stream_get_contents($pipes[1]);
      $stderr = stream_get_contents($pipes[2]);
      fclose($pipes[1]);
      fclose($pipes[2]);
      proc_close($process);

      return $stdout != '';
    }

    return false;
  }
}
