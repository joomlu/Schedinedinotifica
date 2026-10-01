<?php

namespace Tests\Support;

/** No application bootstrap or connection is allowed before infrastructure acceptance. */
final class TestingEnvironment
{
    public static function requireIsolatedRuntime(): never
    {
        // Intentionally no environment-variable bypass. A database name, URL,
        // marker file or APP_ENV=testing does not prove an isolated instance.
        throw new \RuntimeException(
            'TEST_ISOLATION_REQUIRED: functional tests are locked until a disposable '
            .'database and HTTP runtime have been provisioned and independently verified. '
            .'Do not use the development .env or a pre-existing server.'
        );
    }
}
