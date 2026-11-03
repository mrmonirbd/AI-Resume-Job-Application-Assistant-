<?php

namespace Tests {
    use CurlHandle;
    use Illuminate\Http\Client\ConnectionException;
    use Illuminate\Support\Facades\Http;
    use WeakMap;

    final class CmpCurlFake
    {
        private static ?WeakMap $handles = null;

        public static function open(?string $url = null): CurlHandle
        {
            self::$handles ??= new WeakMap;
            $handle = \curl_init();
            self::$handles[$handle] = ['options' => [CURLOPT_URL => $url ?? ''], 'error' => ''];

            return $handle;
        }

        public static function option(CurlHandle $handle, int $option, mixed $value): bool
        {
            $state = self::$handles[$handle];
            $state['options'][$option] = $value;
            self::$handles[$handle] = $state;

            return true;
        }

        public static function execute(CurlHandle $handle): string|false
        {
            $state = self::$handles[$handle];
            $options = $state['options'];
            $url = $options[CURLOPT_URL] ?? '';
            if (! str_starts_with($url, 'http')) {
                $state['error'] = 'CMP URL is not configured.';
                self::$handles[$handle] = $state;

                return false;
            }
            $headers = [];
            foreach ($options[CURLOPT_HTTPHEADER] ?? [] as $header) {
                [$name, $value] = explode(':', $header, 2);
                $headers[$name] = trim($value);
            }
            $method = $options[CURLOPT_CUSTOMREQUEST] ?? (! empty($options[CURLOPT_POST]) ? 'POST' : 'GET');
            $requestOptions = isset($options[CURLOPT_POSTFIELDS]) ? ['body' => $options[CURLOPT_POSTFIELDS]] : [];
            try {
                return Http::withHeaders($headers)->send($method, $url, $requestOptions)->body();
            } catch (ConnectionException $exception) {
                $state['error'] = $exception->getMessage();
                self::$handles[$handle] = $state;

                return false;
            }
        }

        public static function error(CurlHandle $handle): string
        {
            return self::$handles[$handle]['error'];
        }
    }
}

namespace App\Services {
    use CurlHandle;
    use Tests\CmpCurlFake;

    function curl_init(?string $url = null): CurlHandle
    {
        return CmpCurlFake::open($url);
    }

    function curl_setopt(CurlHandle $handle, int $option, mixed $value): bool
    {
        return CmpCurlFake::option($handle, $option, $value);
    }

    function curl_exec(CurlHandle $handle): string|false
    {
        return CmpCurlFake::execute($handle);
    }

    function curl_errno(CurlHandle $handle): int
    {
        return CmpCurlFake::error($handle) === '' ? 0 : CURLE_COULDNT_CONNECT;
    }

    function curl_error(CurlHandle $handle): string
    {
        return CmpCurlFake::error($handle);
    }

    function curl_close(CurlHandle $handle): void {}
}
