<?php
declare(strict_types = 1);

namespace WapplerSystems\ZabbixClient\Controller;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory;
use WapplerSystems\ZabbixClient\Utility\Configuration;

class ConfigurationController
{
    /** @var ResponseFactoryInterface */
    private $responseFactory;

    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    public function hashApiKeyAction(ServerRequestInterface $request): Response
    {
        $input = $request->getQueryParams()['input'] ?? null;
        if ($input === null) {
            throw new \InvalidArgumentException('Please provide a api-key', 1580585107);
        }

        if(!preg_match('/^([a-zA-Z0-9_-]){0,100}$/', $input)) {
            throw new \InvalidArgumentException('Input does not correspond to the required format', 1580585107);
        }

        $hashInstance = GeneralUtility::makeInstance(PasswordHashFactory::class)->getDefaultHashInstance('FE');
        $hashedPassword = $hashInstance->getHashedPassword($input);

        Configuration::setExtConfiguration('apiKeyHashed', true);
        Configuration::setExtConfiguration('apiKey', $hashedPassword);

        $data = ['result' => $hashedPassword];
        $response = $this->responseFactory->createResponse()
            ->withHeader('Content-Type', 'application/json; charset=utf-8');
        $response->getBody()->write(json_encode($data));

        return $response;
    }
}
