<?php
/**
 * @link http://dragonjsonserver.de/
 * @copyright Copyright (c) 2012-2013 DragonProjects (http://dragonprojects.de/)
 * @license http://license.dragonprojects.de/dragonjsonserver.txt New BSD License
 * @author Christoph Herrmann <developer@dragonprojects.de>
 * @package DragonJsonServerSecuritytoken
 */

namespace DragonJsonServerSecuritytoken;

/**
 * Klasse zur Initialisierung des Moduls
 */
class Module
{
	use \DragonJsonServer\ServiceManagerTrait;
	
    /**
     * Gibt die Konfiguration des Moduls zurück
     * @return array
     */
    public function getConfig()
    {
        return require __DIR__ . '/config/module.config.php';
    }

    /**
     * Gibt die Autoloaderkonfiguration des Moduls zurück
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ],
            ],
        ];
    }
    
    /**
     * Wird bei der Initialisierung des Moduls aufgerufen
     * @param \Zend\ModuleManager\ModuleManager $moduleManager
     */
    public function init(\Zend\ModuleManager\ModuleManager $moduleManager)
    {
    	$sharedManager = $moduleManager->getEventManager()->getSharedManager();
    	$sharedManager->attach('DragonJsonServer\Service\Server', 'request', 
    		function (\DragonJsonServer\Event\Request $request) {
    			$method = $request->getRequest()->getMethod();
    			foreach ($this->getServiceManager()->get('Config')['securitytokens'] as $namespace => $securitytoken) {
    				$namespace .= '.';
    				if (substr($method, 0, strlen($namespace)) != $namespace) {
    					continue;
    				}
    				if ($request->getRequest()->getParam('securitytoken') != $securitytoken) {
    					throw new \DragonJsonServer\Exception('incorrect securitytoken', ['namespace' => $namespace]);
    				}
    			}
    		}		
    	);
    	$sharedManager->attach('DragonJsonServer\Service\Server', 'servicemap', 
    		function (\DragonJsonServer\Event\Servicemap $servicemap) {
	    		$securitytokens = $this->getServiceManager()->get('Config')['securitytokens'];
		        foreach ($servicemap->getServicemap()->getServices() as $method => $service) {
		        	foreach ($securitytokens as $namespace => $securitytoken) {
		    			$namespace .= '.';
		        		if (substr($method, 0, strlen($namespace)) != $namespace) {
		        			continue;
		        		}
			            $service->addParams([
			                [
			                    'type' => 'string',
			                    'name' => 'securitytoken',
			                    'optional' => false,
			                ],
			            ]);
		        	}
		        }
    		}
	    );
    }
}
