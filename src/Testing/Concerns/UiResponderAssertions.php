<?php

namespace SameOldNick\BackupManager\Testing\Concerns;

use Illuminate\Support\Arr;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Testing\TestResponse;

trait UiResponderAssertions
{
    /**
     * Asserts that the expected responder was used in the response.
     */
    protected function assertResponderUsed(TestResponse $response, string $responder): void
    {
        $this->assertEquals(
            $response->json('responder'),
            $responder,
            sprintf('Expected responder [%s] was not used.', $responder)
        );
    }

    /**
     * Asserts that the expected ID was used in the response.
     */
    protected function assertResponseId(TestResponse $response, string $id): void
    {
        $this->assertEquals(
            $response->json('id'),
            $id,
            sprintf('Expected ID [%s] was not used.', $id)
        );
    }

    /**
     * Asserts that the expected data was used in the response.
     *
     * The expected value can be an array, which will be compared to the response data using assertJson.
     * Alternatively, it can be a callable that receives an AssertableJson instance for more complex assertions.
     */
    protected function assertResponseData(TestResponse $response, $value, $strict = false): void
    {
        $data = $response->json('data');

        if (! is_array($data)) {
            $this->fail('Expected response data to be an array.');
        }

        if (is_callable($value)) {
            $assert = AssertableJson::fromArray($data);

            $value($assert);

            if (Arr::isAssoc($assert->toArray())) {
                $assert->interacted();
            }
        } elseif (is_array($value)) {
            $response->assertJson(['data' => $value], $strict);
        } else {
            $this->fail('Expected value must be an array or a callable.');
        }
    }
}
