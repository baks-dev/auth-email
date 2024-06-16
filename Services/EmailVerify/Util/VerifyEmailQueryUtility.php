<?php
/*
 *  Copyright 2022.  Baks.dev <admin@baks.dev>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *   limitations under the License.
 *
 */

namespace BaksDev\Auth\Email\Services\EmailVerify\Util;

use function array_key_exists;

/**
 * Предоставляет методы для управления строкой запроса в URI.
 */
class VerifyEmailQueryUtility
{
    public function getTokenFromQuery(string $uri): string
    {
        $params = $this->getQueryParams($uri);

        return $params['token'];
    }

    public function getExpiryTimestamp(string $uri): int
    {
        $params = $this->getQueryParams($uri);

        if(empty($params['expires']))
        {
            return 0;
        }

        return (int) $params['expires'];
    }

    private function getQueryParams(string $uri): array
    {
        $params = [];
        $urlComponents = parse_url($uri);

        if(array_key_exists('query', $urlComponents))
        {
            parse_str(($urlComponents['query'] ?? ''), $params);
        }

        return $params;
    }
}
