<?php

require('./SdkObjectBuilder.php');

class Auth
{
    private $listSdkObject = [];

    public function addSdkObject(
        $clientId,
        $clientSecret,
        $statePrefix,
        $sdkLinks
    ) {
        $this->listSdkObject[$statePrefix] = new SdkObjectBuilder(
            $clientId,
            $clientSecret,
            $statePrefix,
            $sdkLinks
        );
    }

    public function handleLogin() {
        echo '<h1>Login</h1>';

        foreach ($this->listSdkObject as $key => $sdkObject) {
            echo "<a href='" . $sdkObject->buildLink(0) . "'>Using " . $sdkObject->getStatePrefix() . "</a>";
        }
    }

    public function handleSuccess() {
        ["code" => $code, "state" => $state] = $_GET;

        $sdkObject = $this->getSdkObject(explode('_', $state)[0]);

        $user = $sdkObject->getUser([
            "grant_type" => "authorization_code",
            "state" => $state,
            "code" => $code
        ]);
    }

    public function getSdkObject($statePrefix) {
        foreach ($this->listSdkObject as $key => $sdkObject) {
            if ($sdkObject->getStatePrefix() === $statePrefix) {
                return $sdkObject;
            }
        }
    }

    public function handleError() {
        die('Error');
    }
}