<?php declare(strict_types=1);

include __DIR__ . '/../vendor/autoload.php';

/**
 * load Environment Variable for testing
 */
function loadTestEnv(): array
{
    if (! empty(getenv('API_KEY'))) {
        return [
            'API_KEY'=>getenv('API_KEY')
        ];
    }

    $envPath = realpath(__DIR__ . '/.env');

    if (file_exists($envPath)) {
        $env = array_reduce(
            array_filter(
                explode(
                    "\n",
                    file_get_contents($envPath)
                )
            ),
            function ($carry, $item) {
                [$key, $value] = explode('=', $item, 2);
                $carry[$key] = $value;
                return $carry;
            },
            []
        );

        return $env;
    }

    return [];
}
