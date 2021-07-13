<?php

class SdkObjectBuilder
{
    protected string $clientId;
    protected string $clientSecret;
    protected string $statePrefix;
    protected $sdkLinks;
    protected $redirectUri = ['https://localhost/auth-success', 'https://localhost/auth-success'];

    public function __construct(
        $clientId,
        $clientSecret,
        $statePrefix,
        $sdkLinks
    ) {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->statePrefix = $statePrefix;
        $this->sdkLinks = $sdkLinks;
    }

    public function buildLink($index) {
        $sdkLink = $this->sdkLinks[$index];

        switch ($index) {
            case 0:
                return $sdkLink['link'] . '?' . http_build_query(
                    array_merge([
                        'client_id' => $this->clientId,
                        'state' => uniqid($this->statePrefix . '_'),
                        'response_type' => 'code',
                        'redirect_uri' => $this->redirectUri[$index],
                    ], $sdkLink['params'])
                , null, '&');
            case 1:
                return $link = $sdkLink['link'] . '?' . http_build_query([
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'redirect_uri' => $this->redirectUri[$index],
                ]);
            default:
                # code...
                break;
        }
    }

    public function getStatePrefix() {
        return $this->statePrefix;
    }

    public function getUser($params) {
        $token = $this->getToken($params);

        $context = stream_context_create([
            'http' => [
                'method' => "GET",
                'header' => "Accept: application/json\r\nAuthorization: Bearer " . $token ,
                'user_agent' => 'request'
            ]
        ]);
        $result = file_get_contents($this->sdkLinks[2]['link'], false, $context);
        $user = json_decode($result, true);

        echo '<h1>Utilisateur avec ' . $this->getStatePrefix() . '</h1>';
        echo '<pre>';
        var_dump($user);
        echo '</pre>';
    }

    public function getToken($params) {
        if ($this->sdkLinks[1]['method'] === 'GET') {
            $link = $this->buildLink(1, $params);
            $result = file_get_contents($link . "&" . http_build_query($params));
        }else {
            $link = $this->sdkLinks[1]['link'];
            $data = http_build_query(
                array_merge(
                    [ 
                        "client_id" => $this->clientId,
                        "client_secret" => $this->clientSecret,
                        "redirect_uri" => $this->redirectUri[1]
                    ], 
                    $params
                )
            );

            $context = stream_context_create([
                'http' => [
                    'method' => "POST",
                    'header'=> "Content-type: application/x-www-form-urlencoded\r\n"
                        . "Content-Length: " . strlen($data) . "\r\n",
                    'content' => $data
                ]
            ]);
            $result = file_get_contents($link, false, $context);
            if (is_string($result)){
                $result = json_encode($this->sdk_urlDecode($result));
            }
        }
        
        return json_decode($result, true)["access_token"];
    }


    public function sdk_urlDecode($result) {
        $resultExploded = explode('&', $result);
        $resultToReturn = [];
        foreach($resultExploded as $key => $value) {
            $explodedValue = explode('=', $value);
            $resultToReturn[$explodedValue[0]] = $explodedValue[1];
        }
        return $resultToReturn;
    }
}