<?php

namespace App\Curl;

class Captcha
{
    public function __construct(
        private string $apiKey,
        private string $apiCaptcha

    ) {
    }

    public function recaptchaSolve($url, $sitekey)
    {
        denovo:
        [

            $teste = array(
                "clientKey" => $this->apiKey,
                "task" => array(
                    "type" => "NoCaptchaTaskProxyless",
                    "websiteURL" => $url,
                    "websiteKey" => $sitekey,
                    "minScore" => 0.3,
                    "pageAction" => "myverify"
                ),
                "softId" => "0",
                "languagePool" => "en"

            )

        ];

        $task_encode = json_encode($teste);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.anti-captcha.com/createTask');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $task_encode);
        $result = curl_exec($ch);
        $decode_json_task_captcha = json_decode($result, 2);
        if (!isset($decode_json_task_captcha['taskId'])) {
            goto denovo;

        }

        $task_id = $decode_json_task_captcha['taskId'];
        sleep(5);
        verificar_task: {
            $ch = curl_init($this->apiCaptcha);
            $data = array(
                'clientKey' => $this->apiKey,
                'taskId' => $task_id

            );

            $task_encode = json_encode($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $task_encode);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);
        }

        $captcha = json_decode($result, 2);
        $status = $captcha['status'];

        if ($status == 'processing') {
            sleep(3);
            goto verificar_task;
        }

        return $captcha['solution']['gRecaptchaResponse'];

    }

    public function captchaSolve($imagem_base64)
    {
        $base64 = base64_encode($imagem_base64);
        $teste = array(
            "clientKey" => $this->apiCaptcha,
            "task" => array(
                "type" => "ImageToTextTask",
                "body" => $base64,
                "phrase" => false,
                "case" => false,
                "numeric" => false,
                "math" => 0,
                "minLength" => 0,
                "maxLength" => 0
            )
        );

        $task_encode = json_encode($teste);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.anti-captcha.com/createTask');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $task_encode);
        $result = curl_exec($ch);
        $decode_json_task_captcha = json_decode($result, 2);
        $task_id = $decode_json_task_captcha['taskId'];

        do {

            $ch = curl_init($this->apiCaptcha);
            $data = array(
                'clientKey' => $this->apiKey,
                'taskId' => $task_id
            );

            $task_encode = json_encode($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $task_encode);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);
            $captcha = json_decode($result, 2);
            $status = $captcha['status'] ?? null;

        } while ($status == 'processing');

        return $captcha['solution']['text'];

    }

}