<?php

namespace App\Curl;

use App\Curl\Captcha;
use App\Curl\CurlHandle;
use \App\Curl\CurlResponse;

/*
|--------------------------------------------------------------------------
| CURL HTTP/HTTPS REQUEST
|--------------------------------------------------------------------------
*/

class Curl
{
    /**
     * @var array
     */
    private array $option = [];

    /**
     * @var string
     */
    protected string $cookie;

    private string $savedCookie;
    /**
     * Formatador de post
     * @var ?\closure
     */
    private $formatter;

    public function __construct()
    {
        $this->restoreOptions();
    }

    public function __serialize()
    {
        return [
            'option' => $this->option,
            "cookie" => $this->cookie ?? "",
            "savedCookie" => $this->savedCookie ?? "",
            "options" => $this->option,
        ];
    }

    public function domain(): string
    {
        return "";
    }

    public function post(string $url, mixed $post, ?array $header = null): CurlResponse
    {
        if (isset($this->formatter)) {
            $formatter = $this->formatter;
            $post = $formatter($post);
        }
        $option = $this->option;
        $option[$this->getCurlOpt('returnTransfer')] = true;
        $option[$this->getCurlOpt('header')] = true;
        $option[$this->getCurlOpt('customRequest')] = "POST";
        $option[$this->getCurlOpt('postFields')] = $post;
        $option[$this->getCurlOpt('httpHeader')] = $header ?? [];
        $option[$this->getCurlOpt('url')] = $this->domain() . $url;

        $this->submitHttpMessage($option, $response, $headers, $curlInfo);
        return new CurlResponse(
            $headers,
            $curlInfo,
            $response
        );
    }

    public function get(string $url, ?array $header = null): CurlResponse
    {
        $option = $this->option;
        $option[$this->getCurlOpt('returnTransfer')] = true;
        $option[$this->getCurlOpt('header')] = true;
        $option[$this->getCurlOpt('customRequest')] = "GET";
        $option[$this->getCurlOpt('httpHeader')] = $header ?? [];
        $option[$this->getCurlOpt('url')] = $this->domain() . $url;

        $this->submitHttpMessage($option, $response, $headers, $curlInfo);
        return new CurlResponse(
            $headers,
            $curlInfo,
            $response
        );

    }

    private function submitHttpMessage(array $options, ?string &$response, ?array &$headers, mixed &$curlInfo): void
    {
        $curl = new CurlHandle($options);
        $curl->execute();
        $curlInfo = $curl->getInfo();
        $response = $curl->getResponse();
        $headers = $this->headersToArray(substr($response, 0, $curlInfo['header_size']));
        $response = substr($response, $curlInfo['header_size']);
    }

    /**
     * Modifica Configuraçoes customizadas nas requisicoes
     * @param string[] $options
     * @return self
     */
    public function addOption(string $option, mixed $value): self
    {
        $this->option[$this->getCurlOpt($option)] = $value;
        return $this;
    }

    public function removeOptions(?string ...$options): self
    {
        if ($options) {
            foreach ($options as $option) {
                unset($this->option[$this->getCurlOpt($option)]);
            }
            return $this;
        }
        $this->restoreOptions();
        return $this;
    }

    protected function restoreOptions(): void
    {
        $this->clearOptions();
        $this->addOption('returnTransfer', true);
        $this->addOption('followLocation', true);
        $this->addOption('encoding', "");
        $this->addOption('acceptEncoding', "");
        $this->addOption('connectTimeout', 250);
        $this->addOption('timeout', 250);
        $this->addOption('maxRedirects', 10);
        $this->addOption('sslVerifyPeer', false);
        if ($this->cookiesEnabled()) {
            $this->addOption('cookieJar', $this->getCookie());
            $this->addOption('cookieFile', $this->getCookie());
        }
    }

    private function clearOptions(): void
    {
        $this->option = [];
    }

    protected function getOption(string $option): mixed
    {
        return $this->option[$this->getCurlOpt($option)] ?? null;
    }

    private function getCurlOpt(string $option): mixed
    {
        $curlOption = config("curl.{$option}");
        if ($option === null) {
            throw new \Exception("Option {$option} not found");
        }
        return $curlOption;
    }

    public function useCookie(bool $useCookie = true): self
    {
        if ($useCookie) {
            if (!$this->cookiesEnabled()) {
                $this->setCookie($this->getTmpFile());
            }
            return $this;
        }

        $this->clearCookies();
        $this->clearSavedCookies();
        return $this;
    }

    public function setPostFormatter(\Closure $formatter): self
    {
        $this->formatter = $formatter;
        return $this;
    }

    public function removePostFormatter(): void
    {
        if (isset($this->formatter)) {
            unset($this->formatter);
        }
    }

    private function headersToArray(string $str): array
    {
        $headers = array();
        $headersTmpArray = explode("\r\n", $str);
        for ($i = 0; $i < count($headersTmpArray); ++$i) {
            if (strlen($headersTmpArray[$i]) > 0) {
                if (strpos($headersTmpArray[$i], ":")) {
                    $headerName = substr($headersTmpArray[$i], 0, strpos($headersTmpArray[$i], ":"));
                    $headerValue = substr($headersTmpArray[$i], strpos($headersTmpArray[$i], ":") + 1);
                    $headers[$headerName] = $headerValue;
                }
            }
        }
        return $headers;
    }

    public function captcha(): Captcha
    {
        return new Captcha(
            environment('API_CAPTCHA_KEY'),
            environment('API_CAPTCHA')
        );
    }

    public function recaptchaSolve(string $url, string $sitekey)
    {
        return $this->captcha()->recaptchaSolve($url, $sitekey);

    }

    public function getCookie(): string
    {
        return $this->cookie;
    }

    public function setCookie(string $pathFile)
    {
        $this->addOption('cookieJar', $pathFile);
        $this->addOption('cookieFile', $pathFile);
        $this->cookie = $pathFile;
    }

    public function saveCookie(): void
    {
        $this->savedCookie = $this->cookie;
    }

    public function restoreCookie(): void
    {
        if (isset($this->cookie)) {
            unlink($this->cookie);
        }

        $this->cookie = $this->savedCookie;
        $this->addOption('cookieJar', $this->cookie);
        $this->addOption('cookieFile', $this->cookie);
    }

    public function getSavedCookie(): string
    {
        return $this->savedCookie;
    }

    public function duplicateCookie(): void
    {
        $this->saveCookie();
        $this->setCookie($path = $this->$this->getTmpFile());
        $cookie = file_get_contents($this->getSavedCookie());
        file_put_contents($path, $cookie);
    }

    public function cookiesEnabled(): bool
    {
        return isset($this->cookie);
    }

    private function clearCookies(): void
    {
        if ($this->cookiesEnabled()) {
            if (file_exists($this->cookie)) {
                unlink($this->cookie);
            }
            unset($this->cookie);
        }
    }

    private function clearSavedCookies(): void
    {
        if (isset($this->savedCookie)) {
            if (file_exists($this->savedCookie)) {
                unlink($this->savedCookie);
            }
            unset($this->savedCookie);
        }
    }

    private function getTmpFile(): string
    {
        return tempnam(sys_get_temp_dir(), uniqId());
    }

    public function __destruct()
    {
        $this->clearCookies();
        $this->clearSavedCookies();
    }
}
