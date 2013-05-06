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
    	$sharedManager->attach('DragonJsonServer\Service\Server', 'Request', 
    		function (\DragonJsonServer\Event\Request $eventRequest) {
    			$request = $eventRequest->getRequest();
    			$method = $request->getMethod();
    			$securitytokens = $this->getServiceManager()->get('Config')['dragonjsonserversecuritytoken']['securitytokens'];
    			foreach ($securitytokens as $namespace => $securitytoken) {
    				if (substr($method, 0, strlen($namespace . '.')) != $namespace . '.') {
    					continue;
    				}
    				if ($request->getParam('securitytoken') != $securitytoken) {
    					throw new \DragonJsonServer\Exception('invalid securitytoken', ['namespace' => $namespace]);
    				}
    			}
    		}		
    	);
    	$sharedManager->attach('DragonJsonServer\Service\Server', 'Servicemap', 
    		function (\DragonJsonServer\Event\Servicemap $eventServicemap) {
	    		$securitytokens = $this->getServiceManager()->get('Config')['dragonjsonserversecuritytoken']['securitytokens'];
		        foreach ($eventServicemap->getServicemap()->getServices() as $method => $service) {
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
