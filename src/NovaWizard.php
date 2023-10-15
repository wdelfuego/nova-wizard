<?php

namespace Wdelfuego\NovaWizard;

use Illuminate\Http\Request;
use Laravel\Nova\Menu\MenuSection;
use Laravel\Nova\Nova;
use Laravel\Nova\Tool;

class NovaWizard extends Tool
{
    public static function pathToWizard(string $wizardKey) : string
    {
        $wizard = new static($wizardKey);
        return $wizard->uri();
    }
  
    private $config = [];
    private $wizardKey = null;
    
    public function __construct($wizardKey)
    {
        $wizards = config('nova-wizard');
        
        if(!isset($wizards[$wizardKey]))
        {
            throw new \Exception("Missing config for wizard key '$wizardKey' in config/nova-wizard.php");
        }
        else if(!isset($wizards[$wizardKey]['class']) || !trim($wizards[$wizardKey]['class']))
        {
            throw new \Exception("Invalid config for wizard key '$wizardKey', key 'class' in config/nova-wizard.php; a class is required");
        }
        else if(!class_exists($wizards[$wizardKey]['class']))
        {
            $setting = $wizards[$wizardKey]['class'];
            throw new \Exception("Invalid config for wizard key '$wizardKey', key 'class' in config/nova-wizard.php; the supplied class '$setting' does not seem to exist");
        }
        else if(!isset($wizards[$wizardKey]['uri']) || !trim($wizards[$wizardKey]['uri']))
        {
            throw new \Exception("Invalid config for wizard key '$wizardKey', key 'uri' in config/nova-wizard.php; a uri is required");
        }
          
        $this->wizardKey = $wizardKey;
        $this->config = $wizards[$wizardKey];
    }
    
    public function boot()
    {
        Nova::script('nova-wizard', __DIR__.'/../dist/js/tool.js');
        Nova::style('nova-wizard', __DIR__.'/../dist/css/tool.css');
    }

    public function menu(Request $request)
    {
        return MenuSection::make('Nova Wizard')
            ->path('/nova-wizard')
            ->icon('server');
    }
    
    public function uri()
    {
        return $this->config['uri'] ;
    }
    
}
