<?php
/**
 * @link http://dragonjsonserver.de/
 * @copyright Copyright (c) 2012-2013 DragonProjects (http://dragonprojects.de/)
 * @license http://license.dragonprojects.de/dragonjsonserver.txt New BSD License
 * @author Christoph Herrmann <developer@dragonprojects.de>
 * @package DragonJsonServerSecuritytoken
 */

/**
 * @return array
 */
return [
    'securitytokens' => [],
    'eventlisteners' => [
    	['DragonJsonServer\Service\Server', 'request', function (\DragonJsonServer\Event\Request $request) {
    		$method = $request->getRequest()->getMethod();
    		foreach ($request->getServiceManager()->get('Config')['securitytokens'] as $namespace => $securitytoken) {
    			$namespace .= '.';
	    		if (substr($method, 0, strlen($namespace)) != $namespace) {
	    			continue;
	    		}
	    		if ($request->getRequest()->getParam('securitytoken') != $securitytoken) {
	    			throw new \DragonJsonServer\Exception('incorrect securitytoken', ['namespace' => $namespace]);
	    		} 
    		}
    	}],
    	['DragonJsonServer\Service\Server', 'servicemap', function (\DragonJsonServer\Event\Servicemap $servicemap) {
    		$securitytokens = $servicemap->getServiceManager()->get('Config')['securitytokens'];
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
    	}],
    ],
];
