<?php
declare(strict_types=1);

namespace WapplerSystems\ZabbixClient\Middleware;

/**
 * This file is part of the "zabbix_client" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

// use \TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Http\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use WapplerSystems\ZabbixClient\ManagerFactory;
use WapplerSystems\ZabbixClient\Exception\InvalidOperationException;
use WapplerSystems\ZabbixClient\Authorization\IpAuthorizationProvider;
use WapplerSystems\ZabbixClient\Authorization\HttpMethodAuthorizationProvider;
use WapplerSystems\ZabbixClient\Authorization\OperationAuthorizationProvider;
use WapplerSystems\ZabbixClient\Authentication\KeyAuthenticationProvider;

class ZabbixClient implements MiddlewareInterface
{

    /**
     * Calls the "unavailableAction" of the error controller if the system is in maintenance mode.
     * This only applies if the REMOTE_ADDR does not match the devIpMask
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var \Psr\Http\Message\UriInterface $requestedUri */
        $requestedUri = $request->getUri();
        $requestedPath = $requestedUri->getPath();

        if ($requestedPath === '/zabbixclient/' || $requestedPath === '/zabbixclient') {
            return $this->processRequest($request);
        }

        return $handler->handle($request);
    }

    private function processRequest(ServerRequestInterface $request)
    {
        /** @var Response $response */
        $response = GeneralUtility::makeInstance(Response::class);
        /** @var $logger Logger */
        $logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);

        // Check allowed HTTP-Method
        $httpMethodAuthorizationProvider = new HttpMethodAuthorizationProvider($request);
        if(!$httpMethodAuthorizationProvider->isAuthorized()) {
            $logger->error('Not allowed HTTP-method', ['ip' => $_SERVER['REMOTE_ADDR']]);
            return $response->withStatus(405, 'Not allowed HTTP-method');
        }

        // Check if ip is allowed
        // $ip = GeneralUtility::getIndpEnv('REMOTE_ADDR');
        $ipAuthorizationProvider = new IpAuthorizationProvider($request);
        if ($ipAuthorizationProvider->isAuthorized() === false) {
            return $response->withStatus(403, 'Not allowed');
        }

        // Check if API-Key is allowed
        $keyAuthenticationProvider = new KeyAuthenticationProvider($request);
        if ($keyAuthenticationProvider->hasValidKey() === false) {
            // TODO:
            // lock this IP address

            $logger->error('API key wrong', ['ip' => $_SERVER['REMOTE_ADDR']]);
            return $response->withStatus(403, 'API key wrong');
        }

        // Check if operation is allowed
        // $operation has to be allowed at: Typo3 extension manager gearwheel icon (ext_conf_template.txt)
        $operationAuthorizationProvider = new OperationAuthorizationProvider($request);
        if($operationAuthorizationProvider->isAuthorized() === false) {
            return $response->withStatus(403, 'operation not allowed');
        }

        $operation = $operationAuthorizationProvider->getOperation();

        try {
            $managerFactory = ManagerFactory::getInstance();
            $operationManager = $managerFactory->getOperationManager();

            $params = array_merge($request->getParsedBody() ?? [], $request->getQueryParams() ?? []);
            $result = $operationManager->executeOperation($request, $operation, $params);
        } catch (InvalidOperationException $ex) {
            return $response->withStatus(404,  $ex->getMessage());
        } catch (\Exception $ex) {
            return $response->withStatus(500,  substr(strrchr(get_class($ex), "\\"), 1) . ': '. $ex->getMessage());
        }

        if ($result !== null) {
            return new JsonResponse([$result->toArray()]);
        }

        return $response->withStatus(404, 'operation or service parameter not set');
    }
}
