<?php

namespace Lartisan\PloiDashboard\Services\Ploi;

use Illuminate\Support\Collection;
use Lartisan\PloiDashboard\Concerns\Resolvable;
use Lartisan\PloiDashboard\Services\Ploi\Client\PloiClient;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\DaemonResponseData;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\DatabaseResponseData;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\NetworkRuleResponseData;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\RedirectData;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\RepositoryResponseData;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\ServerResponseData;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\SiteResponseData;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\SshKeyResponseData;

class Ploi
{
    use Resolvable;

    public function __construct(
        protected PloiClient $client,
    ) {}

    /*
     |--------------------------------------------------------------------------
     | Servers
     |--------------------------------------------------------------------------
     */
    public function listServers(): Collection
    {
        return $this->client->listServers();
    }

    public function getServer(): ServerResponseData
    {
        return $this->client->getServer();
    }

    public function rebootServer(int $serverId): string
    {
        return $this->client->rebootServer($serverId);
    }

    /*
     |--------------------------------------------------------------------------
     | Servers - PHP
     |--------------------------------------------------------------------------
     */
    public function enableOpCache(int $serverId)
    {
        return $this->client->enableOpCache($serverId);
    }

    public function disableOpCache(int $serverId)
    {
        return $this->client->disableOpCache($serverId);
    }

    public function getPhpVersions(int $serverId)
    {
        return $this->client->getPhpVersions($serverId);
    }

    public function installPhpVersion(int $serverId, string $phpVersion)
    {
        return $this->client->installPhpVersion($serverId, $phpVersion);
    }

    /*
     |--------------------------------------------------------------------------
     | Servers - Databases
     |--------------------------------------------------------------------------
     */
    public function getDatabases(): Collection
    {
        return $this->client->getDatabases();
    }

    public function createDatabase(array $data): DatabaseResponseData
    {
        return $this->client->createDatabase($data);
    }

    public function cloneDatabase(int $database, array $data): DatabaseResponseData
    {
        return $this->client->cloneDatabase($database, $data);
    }

    public function deleteDatabase(int $database)
    {
        return $this->client->deleteDatabase($database);
    }

    /*
     |--------------------------------------------------------------------------
     | Server - Network Rules
     |--------------------------------------------------------------------------
     */
    public function listNetworkRules(): Collection
    {
        return $this->client->listNetworkRules();
    }

    public function createNetworkRule(array $data): NetworkRuleResponseData
    {
        return $this->client->createNetworkRule($data);
    }

    public function deleteNetworkRule(int $id)
    {
        return $this->client->deleteNetworkRule($id);
    }

    /*
     |--------------------------------------------------------------------------
     | Server - SSH Keys
     |--------------------------------------------------------------------------
     */
    public function listSshKeys(): Collection
    {
        return $this->client->listSshKeys();
    }

    public function createSshKey(array $data): SshKeyResponseData
    {
        return $this->client->createSshKey($data);
    }

    public function deleteSshKey(int $id)
    {
        return $this->client->deleteSshKey($id);
    }

    /*
     |--------------------------------------------------------------------------
     | Server - Daemons
     |--------------------------------------------------------------------------
     */
    public function listDaemons(): Collection
    {
        return $this->client->listDaemons();
    }

    public function createDaemon(array $data): DaemonResponseData
    {
        return $this->client->createDaemon($data);
    }

    public function pauseDaemon(int $id)
    {
        return $this->client->pauseDaemon($id);
    }

    public function restartDaemon(int $id)
    {
        return $this->client->restartDaemon($id);
    }

    public function deleteDaemon(int $id)
    {
        return $this->client->deleteDaemon($id);
    }

    /*
     |--------------------------------------------------------------------------
     | Server - Logs
     |--------------------------------------------------------------------------
     */
    public function listLogs(): Collection
    {
        return $this->client->listLogs();
    }

    /*
     |--------------------------------------------------------------------------
     | Server - Settings
     |--------------------------------------------------------------------------
     */
    public function updateServer(array $data)
    {
        return $this->client->updateServer($data);
    }

    /*
     |--------------------------------------------------------------------------
     | Sites
     |--------------------------------------------------------------------------
     */
    public function listSites(): Collection
    {
        return $this->client->listSites();
    }

    /*
     |--------------------------------------------------------------------------
     | General
     |--------------------------------------------------------------------------
     */
    public function getEnvironment()
    {
        return $this->client->getEnvironment();
    }

    public function updateEnvironment(array $data)
    {
        return $this->client->updateEnvironment($data);
    }

    public function deploySite()
    {
        return $this->client->deploySite();
    }

    public function updateDeployScript(string $deployScript)
    {
        return $this->client->updateDeployScript($deployScript);
    }

    /*
     |--------------------------------------------------------------------------
     | Queue
     |--------------------------------------------------------------------------
     */
    public function getQueueWorkers()
    {
        return $this->client->getQueueWorkers();
    }

    public function createQueueWorker(array $data)
    {
        return $this->client->createQueueWorker($data);
    }

    public function pauseQueueWorker(int $id)
    {
        return $this->client->pauseQueueWorker($id);
    }

    public function restartQueueWorker(mixed $id)
    {
        return $this->client->restartQueueWorker($id);
    }

    public function deleteQueueWorker(mixed $id)
    {
        return $this->client->deleteQueueWorker($id);
    }

    /*
     |--------------------------------------------------------------------------
     | Certificates
     |--------------------------------------------------------------------------
     */
    public function getCertificates()
    {
        return $this->client->getCertificates();
    }

    public function createCertificate(array $data)
    {
        return $this->client->createCertificate($data);
    }

    public function deleteCertificate(mixed $id)
    {
        return $this->client->deleteCertificate($id);
    }

    /*
     |--------------------------------------------------------------------------
     | Cronjobs
     |--------------------------------------------------------------------------
     */
    public function getCronjobs()
    {
        return $this->client->getCronjobs();
    }

    public function createCronjob(array $data)
    {
        return $this->client->createCronjob($data);
    }

    public function deleteCronjob(mixed $id)
    {
        return $this->client->deleteCronjob($id);
    }

    /*
     |--------------------------------------------------------------------------
     | Repository
     |--------------------------------------------------------------------------
     */
    public function getRepository(): RepositoryResponseData
    {
        return $this->client->getRepository();
    }

    public function installRepository(array $data)
    {
        return $this->client->installRepository($data);
    }

    public function toggleQuickDeploy()
    {
        return $this->client->toggleQuickDeploy();
    }

    public function deleteRepository()
    {
        return $this->client->deleteRepository();
    }

    /*
     |--------------------------------------------------------------------------
     | Redirects
     |--------------------------------------------------------------------------
     */
    public function getRedirects(): Collection
    {
        return $this->client->getRedirects();
    }

    public function createRedirect(array $data): RedirectData
    {
        return $this->client->createRedirect($data);
    }

    public function deleteRedirect(int $id)
    {
        return $this->client->deleteRedirect($id);
    }

    /*
     |--------------------------------------------------------------------------
     | Settings
     |--------------------------------------------------------------------------
     */
    public function getSite(): SiteResponseData
    {
        return $this->client->getSite();
    }

    public function updateSite(array $data): SiteResponseData
    {
        return $this->client->updateSite($data);
    }

    public function robotAccess(array $data)
    {
        return $this->client->robotAccess($data);
    }

    public function deleteSite()
    {
        return $this->client->deleteSite();
    }

    public function getTestDomain()
    {
        return $this->client->getTestDomain();
    }

    public function enableTestDomain()
    {
        return $this->client->enableTestDomain();
    }

    public function disableTestDomain()
    {
        return $this->client->disableTestDomain();
    }

    public function changePhpVersion(string $version)
    {
        return $this->client->changePhpVersion($version);
    }
}
