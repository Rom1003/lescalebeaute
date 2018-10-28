<?php
namespace App;

use Illuminate\Database\Capsule\Manager as Capsule;
use Symfony\Component\Yaml\Yaml;
use App\Config;

class Database {
    public function __construct()
    {
        $config = $this->getConfig();
        $capsule = new Capsule;
        $capsule->addConnection($config['bdd']);




// Make this Capsule instance available globally via static methods... (optional)
        $capsule->setAsGlobal();

// Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
        $capsule->bootEloquent();
    }

    public function getConfig() {
        $config = new Config();

        $global = $config->getGlobal();
        if ($global['MODE'] == 'prod'){
            return Yaml::parseFile($global['FILE_ROOT'].'/config/config.yml');
        } else {
            return Yaml::parseFile($global['FILE_ROOT'].'/config/config_wamp.yml');
        }

    }
}

