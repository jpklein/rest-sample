<?php
/**
 * @author    Philippe Klein <jpklein@gmail.com>
 * @copyright Copyright (c) 2017 Philippe Klein
 * @version   0.4
 */
declare(strict_types=1);

namespace RestSample\SlimMiddleware;

// Aliases psr-7 objects
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class JsonApiResponsibilitiesMiddleware
{
    public function __invoke(Request $request, Response $response, callable $next)
    {
        // Checks for the JSON API Content-Type header
        if (($headers = $request->getHeaders()) && isset($headers['Content-Type'])) {
            foreach ($headers['Content-Type'] as $value) {
                if ($value === 'application/vnd.api+json') {
                    // Sets JSON API header in responses
                    return $next($request, $response->withHeader('Content-Type', 'application/vnd.api+json'));
                }
            }
        }

        throw new \Exception('Bad Request', 400); // JsonApiStatusesTrait::BAD_REQUEST
    }
}