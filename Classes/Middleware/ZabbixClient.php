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
use WapplerSystems\ZabbixClient\Authentication\KeyAuthenticationProvider;
use WapplerSystems\ZabbixClient\Utility\Configuration;

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
        if (strpos($requestedUri->getPath(), '/zabbixclient/') === 0) {
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
        $config = Configuration::getExtConfiguration();

        // Check allowed HTTP-Method
        $allowedHttpMethods = explode('-', $config['httpMethod']);
        if(!in_array($request->getMethod(), $allowedHttpMethods)) {
            $logger->error('Not allowed HTTP-method', ['ip' => $_SERVER['REMOTE_ADDR']]);
            return $response->withStatus(405, 'Not allowed HTTP-method');
        }

        $ip = GeneralUtility::getIndpEnv('REMOTE_ADDR');
        $ipAuthorizationProvider = new IpAuthorizationProvider();
        if (!$ipAuthorizationProvider->isAuthorized($ip)) {
            if($ipAuthorizationProvider->blockedIp($ip)) {
                $logger->error('Too many wrong requests', ['ip' => $_SERVER['REMOTE_ADDR']]);
                return $response->withStatus(429, 'Too many wrong requests');
            }

            return $response->withStatus(403, 'Not allowed');
        }

        $accessMethod = $config['accessMethod'];
        switch (intval($accessMethod)) {
            case 1:
                $key = $request->getHeaders()['api-key'][0];
                $returnType = $request->getHeaders()['return-type'][0];
                break;

            default:
                $key = $request->getParsedBody()['key'] ?? $request->getQueryParams()['key'] ?? null;
                $returnType = $request->getParsedBody()['return-type'] ?? $request->getQueryParams()['return-type'] ?? null;
                break;
        }

        $keyAuthenticationProvider = new KeyAuthenticationProvider();
        if (!$keyAuthenticationProvider->hasValidKey($key)) {
            if($ipAuthorizationProvider->blockedIp($ip)) {
                $logger->error('Too many wrong requests', ['ip' => $_SERVER['REMOTE_ADDR']]);
                return $response->withStatus(429, 'Too many wrong requests');
            }

            $logger->error('API key wrong', ['ip' => $_SERVER['REMOTE_ADDR']]);
            return $response->withStatus(403, 'API key wrong');
        }

        $operation = $request->getParsedBody()['operation'] ?? $request->getQueryParams()['operation'] ?? null;
        // $operation has to be allowed at: Typo3 extension manager gearwheel icon (ext_conf_template.txt)
        if(!in_array($operation, $config['operations']) && $config['operations'][$operation] !== "1") {
            return $response->withStatus(403, 'operation not allowed');
        }

        $params = array_merge($request->getParsedBody() ?? [], $request->getQueryParams() ?? []);

        $managerFactory = ManagerFactory::getInstance();

        if ($operation !== null && $operation !== '') {
            $operationManager = $managerFactory->getOperationManager();
            try {
                $result = $operationManager->executeOperation($operation, $params);
            } catch (InvalidOperationException $ex){
                return $response->withStatus(404,  $ex->getMessage());
            } catch (\Exception $ex) {
                return $response->withStatus(500,  substr(strrchr(get_class($ex), "\\"), 1) . ': '. $ex->getMessage());
            }
        }

        if ($result !== null) {
            switch ($returnType) {
                case 'jsonArray':
                    return new JsonResponse([$result->toArray()]);
                    break;

                default:
                    return new JsonResponse($result->toArray());
                    break;
            }
        }

        return $response->withStatus(404, 'operation or service parameter not set');
    }
}
