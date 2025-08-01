<?php
mb_internal_encoding("UTF-8");
use App\Kernel;
use App\CacheKernel;

use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;

require dirname(__DIR__).'/config/bootstrap.php';

if ($_SERVER['APP_DEBUG']) {
    umask(0000);

    Debug::enable();
}

if ($trustedProxies = $_SERVER['TRUSTED_PROXIES'] ?? $_ENV['TRUSTED_PROXIES'] ?? false) {
    Request::setTrustedProxies(explode(',', $trustedProxies), Request::HEADER_X_FORWARDED_ALL ^ Request::HEADER_X_FORWARDED_HOST);
}

if ($trustedHosts = $_SERVER['TRUSTED_HOSTS'] ?? $_ENV['TRUSTED_HOSTS'] ?? false) {
    Request::setTrustedHosts([$trustedHosts]);
}

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
// Wrap the default Kernel with the CacheKernel one in 'prod' environment
if ('prod' === $kernel->getEnvironment()) {
     //$kernel = new CacheKernel($kernel);
}
$request = Request::createFromGlobals();
//print_r($request);
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
