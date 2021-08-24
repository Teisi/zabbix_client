<?php
declare(strict_types=1);

namespace WapplerSystems\ZabbixClient\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Log404 implements MiddlewareInterface
{

    /**
     * @var \WapplerSystems\ZabbixClient\Domain\Repository\FeLogRepository
     */
    protected $feLogRepository;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     */
    protected $persistenceManager;

    /**
     * @param \WapplerSystems\ZabbixClient\Domain\Repository\FeLogRepository
     */
    public function injectFeLogRepository(\WapplerSystems\ZabbixClient\Domain\Repository\FeLogRepository $feLogRepository) {
        $this->feLogRepository = $feLogRepository;
    }

    /**
     * @param \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     */
    public function injectPersistenceManager(\TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager $persistenceManager) {
        $this->persistenceManager = $persistenceManager;
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $response = $handler->handle($request);
        $statusCode = $response->getStatusCode();

        if($statusCode === 404) {
            $requestedUrl = $request->getAttributes()['normalizedParams']->getrequestUrl();
            $reasonPhrase = $response->getReasonPhrase();
            $logData = [
                'statusCode' => $statusCode,
                'reasonPhrase' => $reasonPhrase,
                'url' => $requestedUrl
            ];
            $logDataJson = json_encode($logData, JSON_UNESCAPED_SLASHES);

            // write to database
            $newLog = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\WapplerSystems\ZabbixClient\Domain\Model\FeLog::class);
            $newLog->setError(404);
            $newLog->setLogMessage($statusCode . ' ' .  $reasonPhrase);
            $newLog->setLogData($logDataJson);

            $this->feLogRepository->add($newLog);

            $this->persistenceManager->persistAll();
        }

        return $response;
    }
}
