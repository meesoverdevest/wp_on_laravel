<?php

namespace meesoverdevest\wp_on_laravel\Commands;

use Illuminate\Console\Command;
use meesoverdevest\wp_on_laravel\helpers\WOLInstaller;

class InstallWordPress extends Command
{

    protected $WOLInstaller;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wol:install {password} {mail}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Installation command for WordPress: checks database existence, generates configuration and install the latest version of WordPress into your Laravel project.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $password = $this->argument('password');
        $mail = $this->argument('mail');

        if(empty($password) || empty($mail)) {
          $this->error("You need to add password and mail parameters to the installation command \n");
          die();
        }

        $this->runInstallation([$password, $mail]);
    }

    private function runInstallation($auth) {
        $this->WOLInstaller = new WOLInstaller();
        if( $this->WOLInstaller->WPCLI_exists() === true) {
            $this->line("WP CLI installation detected");
        } else {
            $this->error("A global installation of WP CLI is required for this package! \n Visit http://wp-cli.org/#installing for installation instructions for WP CLI.");
            die();
        }
        $this->WOLInstaller->WPDB_exists();

        if( $this->WOLInstaller->installation($password, $mail) === true) {
            $this->line("Installation of Wordpress is completed! \n We hope you enjoy this package! \nVisit " . url('/') . "/blog/wp-login.php to login using your given credentials: \n (Mail: ".$auth[1]." || Password: " .$auth[0] . " || Username: admin)\n");
        } else {
            $this->line("WordPress is already installed :D \n");
        }

    }
}
