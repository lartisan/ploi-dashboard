<?php

namespace Lartisan\PloiDashboard\Services\Ploi\Client;

use Exception;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\CertificateResponseData;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\CronjobResponseData;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\DaemonResponseData;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\DatabaseResponseData;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\LogResponseData;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\NetworkRuleResponseData;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\QueueResponseData;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\RedirectData;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\RepositoryResponseData;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\ServerResponseData;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\SiteResponseData;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\SshKeyResponseData;
use Lartisan\PloiDashboard\Services\Ploi\ValueObjects\Responses\TestDomainData;

class PloiClient
{
    public function __construct(
        protected ClientConfig $config,
    ) {}

    /*
     |--------------------------------------------------------------------------
     | Servers
     |--------------------------------------------------------------------------
     */
    public function listServers(): Collection
    {
        $response = $this->get('servers');

        return rescue(fn () => ServerResponseData::toCollection($response->json('data')));
    }

    public function getServer(): ServerResponseData
    {
        $response = $this->get("servers/{$this->config->serverId}");

        return rescue(fn () => ServerResponseData::fromLivewire($response->json('data')));
    }

    public function rebootServer(int $serverId): string
    {
        $response = $this->post("servers/{$serverId}/restart");

        return rescue(fn () => $response->json('message'));
    }

    /*
     |--------------------------------------------------------------------------
     | Servers - PHP
     |--------------------------------------------------------------------------
     */
    public function enableOpCache(int $serverId)
    {
        $response = $this->post("servers/{$serverId}/enable-opcache");

        return $response->json();
    }

    public function disableOpCache(int $serverId)
    {
        $response = $this->delete("servers/{$serverId}/disable-opcache");

        return $response->json();
    }

    public function getPhpVersions(int $serverId)
    {
        $response = $this->get("servers/{$serverId}/php/versions");

        return rescue(fn () => $response->json('data.versions'));
    }

    public function installPhpVersion(int $serverId, string $phpVersion)
    {
        $response = $this->post("servers/{$serverId}/php/install", [
            'version' => $phpVersion,
        ]);

        return $response->json();
    }

    /*
     |--------------------------------------------------------------------------
     | Servers - Databases
     |--------------------------------------------------------------------------
     */
    public function getDatabases(): Collection
    {
        $response = $this->get("servers/{$this->config->serverId}/databases");

        return rescue(fn () => DatabaseResponseData::toCollection($response->json('data')));
    }

    public function createDatabase(array $data): DatabaseResponseData
    {
        $response = $this->post("servers/{$this->config->serverId}/databases", $data);

        return rescue(fn () => DatabaseResponseData::fromLivewire($response->json('data')));
    }

    public function cloneDatabase(int $database, array $data): DatabaseResponseData
    {
        $response = $this->post("servers/{$this->config->serverId}/databases/{$database}/duplicate", $data);

        return rescue(fn () => DatabaseResponseData::fromLivewire($response->json('data')));
    }

    public function deleteDatabase(int $database)
    {
        $response = $this->delete("servers/{$this->config->serverId}/databases/{$database}");

        return $response->json();
    }

    /*
     |--------------------------------------------------------------------------
     | Server - Network Rules
     |--------------------------------------------------------------------------
     */
    public function listNetworkRules(): Collection
    {
        $response = $this->get("servers/{$this->config->serverId}/network-rules");

        return rescue(fn () => NetworkRuleResponseData::toCollection($response->json('data')));
    }

    public function createNetworkRule(array $data): NetworkRuleResponseData
    {
        $response = $this->post("servers/{$this->config->serverId}/network-rules", $data);

        return rescue(fn () => NetworkRuleResponseData::fromLivewire($response->json('data')));
    }

    public function deleteNetworkRule(int $id)
    {
        $response = $this->delete("servers/{$this->config->serverId}/network-rules/{$id}");

        return $response->json();
    }

    /*
     |--------------------------------------------------------------------------
     | Server - SSH Keys
     |--------------------------------------------------------------------------
     */
    public function listSshKeys(): Collection
    {
        $response = $this->get("servers/{$this->config->serverId}/ssh-keys");

        return rescue(fn () => SshKeyResponseData::toCollection($response->json('data')));
    }

    public function createSshKey(array $data): SshKeyResponseData
    {
        $response = $this->post("servers/{$this->config->serverId}/ssh-keys", $data);

        return rescue(fn () => SshKeyResponseData::fromLivewire($response->json('data')));
    }

    public function deleteSshKey(int $id)
    {
        $response = $this->delete("servers/{$this->config->serverId}/ssh-keys/{$id}");

        return $response->json();
    }

    /*
     |--------------------------------------------------------------------------
     | Server - Daemons
     |--------------------------------------------------------------------------
     */
    public function listDaemons(): Collection
    {
        $response = $this->get("servers/{$this->config->serverId}/daemons");

        return rescue(fn () => DaemonResponseData::toCollection($response->json('data')));
    }

    public function createDaemon(array $data): DaemonResponseData
    {
        $response = $this->post("servers/{$this->config->serverId}/daemons", $data);

        return rescue(fn () => DaemonResponseData::fromLivewire($response->json('data')));
    }

    public function pauseDaemon(int $id)
    {
        $response = $this->post("servers/{$this->config->serverId}/daemons/{$id}/toggle-pause");

        return $response->json();
    }

    public function restartDaemon(int $id)
    {
        $response = $this->post("servers/{$this->config->serverId}/daemons/{$id}/restart");

        return $response->json();
    }

    public function deleteDaemon(int $id)
    {
        $response = $this->delete("servers/{$this->config->serverId}/daemons/{$id}");

        return $response->json();
    }

    /*
     |--------------------------------------------------------------------------
     | Server - Logs
     |--------------------------------------------------------------------------
     */
    public function listLogs(): Collection
    {
        $response = $this->get("servers/{$this->config->serverId}/logs");

        return rescue(fn () => LogResponseData::toCollection($response->json('data')));
    }

    /*
     |--------------------------------------------------------------------------
     | Server - Settings
     |--------------------------------------------------------------------------
     */
    public function updateServer(array $data)
    {
        $response = $this->patch("servers/{$this->config->serverId}", $data);

        return rescue(fn () => ServerResponseData::toCollection($response->json('data')));
    }

    /*
     |--------------------------------------------------------------------------
     | Sites
     |--------------------------------------------------------------------------
     */
    public function listSites(): Collection
    {
        $response = $this->get("servers/{$this->config->serverId}/sites");

        return rescue(fn () => SiteResponseData::toCollection($response->json('data')));
    }

    /*
     |--------------------------------------------------------------------------
     | General
     |--------------------------------------------------------------------------
     */
    public function getEnvironment()
    {
        $response = $this->get("servers/{$this->config->serverId}/sites/{$this->config->websiteId}/env");

        return $response->json('data');
    }

    public function updateEnvironment(array $data)
    {
        $response = $this->patch("servers/{$this->config->serverId}/sites/{$this->config->websiteId}/env", $data);

        return $response->json('data');
    }

    public function deploySite()
    {
        $response = $this->post("servers/{$this->config->serverId}/sites/{$this->config->websiteId}/deploy");

        return $response->json();
    }

    public function updateDeployScript(string $deployScript)
    {
        $response = $this->patch("servers/{$this->config->serverId}/sites/{$this->config->websiteId}/deploy/script", [
            'deploy_script' => $deployScript,
        ]);

        return $response->json();
    }

    /*
     |--------------------------------------------------------------------------
     | Queue
     |--------------------------------------------------------------------------
     */
    public function getQueueWorkers()
    {
        $response = $this->get("servers/{$this->config->serverId}/sites/{$this->config->websiteId}/queues");

        return rescue(fn () => QueueResponseData::toCollection($response->json('data')));
    }

    public function createQueueWorker(array $data)
    {
        $response = $this->post("servers/{$this->config->serverId}/sites/{$this->config->websiteId}/queues", $data);

        return rescue(fn () => QueueResponseData::fromLivewire($response->json('data')));
    }

    public function pauseQueueWorker(int $id)
    {
        $response = $this->post("servers/{$this->config->serverId}/sites/{$this->config->websiteId}/queues/{$id}/toggle-pause");

        return rescue(fn () => QueueResponseData::fromLivewire($response->json('data')));
    }

    public function restartQueueWorker(int $id)
    {
        $response = $this->post("servers/{$this->config->serverId}/sites/{$this->config->websiteId}/queues/{$id}/restart");

        return rescue(fn () => QueueResponseData::fromLivewire($response->json('data')));
    }

    public function deleteQueueWorker(mixed $id)
    {
        $response = $this->delete("servers/{$this->config->serverId}/sites/{$this->config->websiteId}/queues/{$id}");

        return $response->json();
    }

    /*
     |--------------------------------------------------------------------------
     | Certificates
     |--------------------------------------------------------------------------
     */
    public function getCertificates()
    {
        $response = $this->get("servers/{$this->config->serverId}/sites/{$this->config->websiteId}/certificates");

        return rescue(fn () => CertificateResponseData::toCollection($response->json('data')));
    }

    public function createCertificate(array $data)
    {
        $response = $this->post("servers/{$this->config->serverId}/sites/{$this->config->websiteId}/certificates", $data);

        return rescue(fn () => CertificateResponseData::fromLivewire($response->json('data')));
    }

    public function deleteCertificate(mixed $id)
    {
        $response = $this->delete("servers/{$this->config->serverId}/sites/{$this->config->websiteId}/certificates/{$id}");

        return $response->json();
    }

    /*
     |--------------------------------------------------------------------------
     | Cronjobs
     |--------------------------------------------------------------------------
     */
    public function getCronjobs()
    {
        $response = $this->get("servers/{$this->config->serverId}/crontabs");

        return rescue(fn () => CronjobResponseData::toCollection($response->json('data')));
    }

    public function createCronjob(array $data): CronjobResponseData
    {
        $response = $this->post("servers/{$this->config->serverId}/crontabs", $data);

        return rescue(fn () => CronjobResponseData::fromLivewire($response->json('data')));
    }

    public function deleteCronjob(mixed $id)
    {
        $response = $this->delete("servers/{$this->config->serverId}/crontabs/{$id}");

        return $response->json();
    }

    /*
     |--------------------------------------------------------------------------
     | Repository
     |--------------------------------------------------------------------------
     */
    public function getRepository(): RepositoryResponseData
    {
        $response = $this->get("servers/{$this->config->serverId}/sites/{$this->config->websiteId}/repository");

        return rescue(fn () => RepositoryResponseData::fromLivewire($response->json('data')));
    }

    public function installRepository(array $data)
    {
        $response = $this->post("servers/{$this->config->serverId}/sites/{$this->config->websiteId}/repository", $data);

        return rescue(fn () => RepositoryResponseData::fromLivewire($response->json('data')));
    }

    public function toggleQuickDeploy()
    {
        $response = $this->post("servers/{$this->config->serverId}/sites/{$this->config->websiteId}/quick-deploy");

        return rescue(fn () => SiteResponseData::fromLivewire($response->json('data')));
    }

    public function deleteRepository()
    {
        $response = $this->delete("servers/{$this->config->serverId}/sites/{$this->config->websiteId}/repository");

        return rescue(fn () => RepositoryResponseData::fromLivewire($response->json('data')));
    }

    /*
     |--------------------------------------------------------------------------
     | Redirects
     |--------------------------------------------------------------------------
     */
    public function getRedirects(): Collection
    {
        $response = $this->get("servers/{$this->config->serverId}/sites/{$this->config->websiteId}/redirects");

        return rescue(fn () => RedirectData::toCollection($response->json('data')));
    }

    public function createRedirect(array $data): RedirectData
    {
        $response = $this->post("servers/{$this->config->serverId}/sites/{$this->config->websiteId}/redirects", $data);

        return rescue(fn () => RedirectData::fromLivewire($response->json('data')));
    }

    public function deleteRedirect(int $id)
    {
        $response = $this->delete("servers/{$this->config->serverId}/sites/{$this->config->websiteId}/redirects/{$id}");

        return $response->json();
    }

    /*
     |--------------------------------------------------------------------------
     | Settings
     |--------------------------------------------------------------------------
     */
    public function getSite(): SiteResponseData
    {
        $response = $this->get("servers/{$this->config->serverId}/sites/{$this->config->websiteId}");

        return rescue(fn () => SiteResponseData::fromLivewire($response->json('data')));
    }

    public function updateSite(array $data): SiteResponseData
    {
        $response = $this->patch("servers/{$this->config->serverId}/sites/{$this->config->websiteId}", $data);

        return rescue(fn () => SiteResponseData::fromLivewire($response->json('data')));
    }

    public function robotAccess(array $data)
    {
        $response = $this->patch("servers/{$this->config->serverId}/sites/{$this->config->websiteId}", $data);

        return rescue(fn () => SiteResponseData::fromLivewire($response->json('data')));
    }

    public function deleteSite()
    {
        $response = $this->delete("servers/{$this->config->serverId}/sites/{$this->config->websiteId}");

        return $response->json();
    }

    public function getTestDomain()
    {
        $response = $this->get("servers/{$this->config->serverId}/sites/{$this->config->websiteId}/test-domain");

        return rescue(fn () => TestDomainData::fromResponse($response->json('data')));
    }

    public function enableTestDomain()
    {
        $response = $this->post("servers/{$this->config->serverId}/sites/{$this->config->websiteId}/test-domain");

        return rescue(fn () => TestDomainData::fromResponse($response->json('data')));
    }

    public function disableTestDomain()
    {
        $response = $this->delete("servers/{$this->config->serverId}/sites/{$this->config->websiteId}/test-domain");

        return rescue(fn () => TestDomainData::fromResponse($response->json('data')));
    }

    public function changePhpVersion(string $version)
    {
        $response = $this->post("servers/{$this->config->serverId}/sites/{$this->config->websiteId}/php-version", [
            'php_version' => $version,
        ]);

        return rescue(fn () => SiteResponseData::fromLivewire($response->json('data')));
    }

    /*
     |--------------------------------------------------------------------------
     | HTTP Methods
     |--------------------------------------------------------------------------
     */
    protected function http(): PendingRequest
    {
        return Http::baseUrl($this->config->apiUrl)
            ->withToken($this->config->apiKey)
            ->acceptJson();
    }

    protected function get(string $endpoint, array $query = []): PromiseInterface | Response
    {
        $response = $this->http()->get($endpoint, $query);

        $this->handleErrors($response);

        logger('Ploi API Request', [
            'method' => 'GET',
            'endpoint' => $endpoint,
            'query' => $query,
            'response' => $response->json(),
            'status' => $response->status(),
        ]);

        return $response;
    }

    protected function post(string $endpoint, array $query = []): PromiseInterface | Response
    {
        $response = $this->http()->post($endpoint, $query);

        $this->handleErrors($response);

        logger('Ploi API Request', [
            'method' => 'POST',
            'endpoint' => $endpoint,
            'query' => $query,
            'response' => $response->json(),
            'status' => $response->status(),
        ]);

        return $response;
    }

    protected function patch(string $endpoint, array $query = []): PromiseInterface | Response
    {
        $response = $this->http()->patch($endpoint, $query);

        $this->handleErrors($response);

        logger('Ploi API Request', [
            'method' => 'PATCH',
            'endpoint' => $endpoint,
            'query' => $query,
            'response' => $response->json(),
            'status' => $response->status(),
        ]);

        return $response;
    }

    protected function delete(string $endpoint): PromiseInterface | Response
    {
        $response = $this->http()->delete($endpoint);

        $this->handleErrors($response);

        logger('Ploi API Request', [
            'method' => 'DELETE',
            'endpoint' => $endpoint,
            'response' => $response->json(),
            'status' => $response->status(),
        ]);

        return $response;
    }

    /*
     |--------------------------------------------------------------------------
     | Handle Client & Server Error Messages
     |--------------------------------------------------------------------------
     */
    private function handleErrors(PromiseInterface | Response $response): void
    {
        if ($response->clientError()) {
            throw new Exception(
                $this->setErrorMessage($response->json())
            );
        }

        if ($response->serverError()) {
            throw new Exception('The server responded with an error.');
        }
    }

    private function setErrorMessage(mixed $json): string
    {
        return json_encode([
            'message' => data_get($json, 'message'),
            'errors' => $this->formatErrorMessage(data_get($json, 'errors')),
        ]);
    }

    private function formatErrorMessage(mixed $errors): string
    {
        return collect($errors ?? [])
            ->map(function ($error) {
                if (is_array($error)) {
                    return collect($error)->implode(PHP_EOL);
                }

                return $error;
            })
            ->implode(PHP_EOL);
    }
}
