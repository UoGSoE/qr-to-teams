<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Testing\TestResponse;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();
        TestResponse::macro('assertRedirectQueryParamNotNull', function ($parameterName) {
            \Illuminate\Testing\Assert::assertTrue(
                $this->isRedirect(),
                $this->statusMessageWithDetails('201, 301, 302, 303, 307, 308', $this->getStatusCode()),
            );

            $location = $this->headers->get('location');
            $queryString = parse_url($location, PHP_URL_QUERY);
            $parameters = [];
            parse_str($queryString, $parameters);

            \Illuminate\Testing\Assert::assertArrayHasKey($parameterName, $parameters, "Query string does not contain {$parameterName} : {$location}");
            \Illuminate\Testing\Assert::assertNotNull($parameters[$parameterName], "Query string {$parameterName} is null : {$location}");

            return $this;
        });
    }

    public function assertRedirectQueryParamNotNull($parameterName)
    {
    }
}
