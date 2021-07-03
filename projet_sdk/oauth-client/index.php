<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Développement SDK</title>

    <style>
        section {
            display: flex;
            flex-direction: column;
            max-width: 600px;
            padding: 40px;
            border-radius: 10px;
            text-align: center;
            margin: auto;
            background-color: #F4F4F4;
        }

        a {
            padding: 20px 30px;
            margin: 10px;
            border-radius: 30px;
            color: white;
            text-decoration: none;
            text-align: center;
            background-color: blue;
        }
        a:hover {
            background-color: darkblue;
        }

    </style>
</head>
<body>
    <section>
        <?php


        /**
        * "client_id":"client_6070546c6aba63.16480463"
        * "client_secret":"38201ad253c323a79d9108f4588bbc62d2e1a5c6"
        */
        const CLIENT_ID = "client_6070546c6aba63.16480463";
        const CLIENT_SECRET = "38201ad253c323a79d9108f4588bbc62d2e1a5c6";
        const CLIENT_FBID = "1220507708383867";
        const CLIENT_FBSECRET = "387d25fd1c6240e83dbfdb51f8e2697f";
        const CLIENT_GHID = "593042b861f98bce0c7b";
        const CLIENT_GHSECRET = "1aa085fb5e31a8cfe1d6c8d4ba8e7c152fc49b76";
        const CLIENT_DISCORDID = "859424765038690304";
        const CLIENT_DISCORDSECRET = "aTYS5dzTzdnnVIQAM3zpILEg7xW2TXi7";

        function getUser($params)
        {
            var_dump($params);
            //Used to get the prefix
            $stateExploaded = explode("_", $params["state"]);
            
            //Build the first part of the url
            $link = '';
            switch ($stateExploaded[0]) {
                case 'oauth':
                    $link = "http://oauth-server:8081/token?"
                        . "client_id=" . CLIENT_ID
                        . "&client_secret=" . CLIENT_SECRET;
                        $result = file_get_contents($link
                            . "&" . http_build_query($params));
                    break;
                case 'facebook':
                    $link = "https://graph.facebook.com/oauth/access_token?"
                        . "client_id=" . CLIENT_FBID
                        . "&client_secret=" . CLIENT_FBSECRET
                        . "&redirect_uri=https://localhost/fbauth-success";
                        $result = file_get_contents($link
                            . "&" . http_build_query($params));

                        break;

                case 'github':
                    $link = "https://github.com/login/oauth/access_token";
                    $data = [ 
                        "client_id" => CLIENT_GHID,
                        "client_secret" => CLIENT_GHSECRET,
                        "redirect_uri" => "https://localhost/ghauth-success"
                    ];
                    $data = array_merge($data, $params);
                    $data = http_build_query($data);

                    $contextDiscord = stream_context_create([
                        'http' => [
                            'method' => "POST",
                            'header'=> "Content-type: application/x-www-form-urlencoded\r\n"
                                . "Content-Length: " . strlen($data) . "\r\n",
                            'content' => $data
                        ]
                    ]);
                    $result = file_get_contents($link, false, $contextDiscord);
                    $result = json_encode(sdk_urlDecode($result));
                    var_dump($result);
                    break;  

                case 'discord':
                    $link = "https://discord.com/api/oauth2/token";
                    $data = [ 
                        "client_id" => CLIENT_DISCORDID,
                        "client_secret" => CLIENT_DISCORDSECRET,
                        "redirect_uri" => "https://localhost/discordauth-success"
                    ];
                    $data = array_merge($data, $params);
                    $data = http_build_query($data);

                    $contextDiscord = stream_context_create([
                        'http' => [
                            'method' => "POST",
                            'header'=> "Content-type: application/x-www-form-urlencoded\r\n"
                                . "Content-Length: " . strlen($data) . "\r\n",
                            'content' => $data
                        ]
                    ]);
                    $result = file_get_contents($link, false, $contextDiscord);
                    break;
                default:
                    break;
            }

            $token = json_decode($result, true)["access_token"];
            var_dump($token);
            // GET USER by TOKEN
            $context = stream_context_create([
                'http' => [
                    'method' => "GET",
                    'header' => "Accept: application/json\r\nAuthorization: Bearer " . $token ,
                    'user_agent' => 'request'
                ]
            ]);

            //Get the user token
            switch ($stateExploaded[0]) {
                case 'oauth':
                    $result = file_get_contents("http://oauth-server:8081/me", false, $context);
                    break;
                case 'facebook':
                    $result = file_get_contents("https://graph.facebook.com/me", false, $context);
                    break;
                case 'discord':
                    $result = file_get_contents("https://discord.com/api/users/@me", false, $context);
                    break;
                case 'github':
                    $result = file_get_contents("https://api.github.com/user", false, $context);
                    break;
                default:
                    break;
            }
            $user = json_decode($result, true);
            echo '<h1>Utilisateur</h1>';
            var_dump($user);
        }

        function sdk_urlDecode($result) {
            $resultExploded = explode('&', $result);
            $resultToReturn = [];
            foreach($resultExploded as $key => $value) {
                $explodedValue = explode('=', $value);
                $resultToReturn[$explodedValue[0]] = $explodedValue[1];
            }
            return $resultToReturn;
        }

        function handleLogin()
        {
            $paramsFB = [
                'client_id' => CLIENT_FBID,
                'state' => uniqid('facebook_'),
                'response_type' => 'code',
                'redirect_uri' => 'https://localhost/fbauth-success',
                'scope' => 'email'
            ];

            $paramsGH = [
                'client_id' => CLIENT_GHID,
                'state' => uniqid('github_'),
                'response_type' => 'code',
                'redirect_uri' => 'https://localhost/ghauth-success',
                'scope' => 'user'
            ];

            $paramsDiscord = [
                'client_id' => CLIENT_DISCORDID,
                'state' => uniqid('discord_'),
                'response_type' => 'code',
                'redirect_uri' => 'https://localhost/discordauth-success',
                'scope' => 'identify guilds'
            ];

        
            echo '<h1>Login</h1>';
            echo "<a href='http://localhost:8081/auth?"
                . "response_type=code"
                . "&client_id=" . CLIENT_ID
                . "&scope=basic"
                . "&state=".uniqid("oauth_")."'>Using oauth-server</a>";
        
            echo "<a href='https://www.facebook.com/v2.10/dialog/oauth?" . http_build_query($paramsFB, null, '&') . "'>Using Facebook</a>";
            echo "<a href='https://discord.com/api/oauth2/authorize?" . http_build_query($paramsDiscord, null, '&') . "'>Using Discord</a>";
            echo "<a href='https://github.com/login/oauth/authorize?" . http_build_query($paramsGH, null, '&') . "'>Using github</a>";
        }

        function handleSuccess()
        {
            ["code" => $code, "state" => $state] = $_GET;
            // ECHANGE CODE => TOKEN
            getUser([
                "grant_type" => "authorization_code",
                "code" => $code,
                "state" => $state
            ]);
        }

        function handleFBSuccess()
        {
            ["code" => $code, "state" => $state] = $_GET;

            getUser([
                "grant_type" => "authorization_code",
                "state" => $state,
                "code" => $code
            ]);
        }

        function handleGHSuccess()
        {
            ["code" => $code, "state" => $state] = $_GET;

            getUser([
                "grant_type" => "authorization_code",
                "state" => $state,
                "code" => $code
            ]);
        }

        function handleDISCORDSuccess()
        {
            ["code" => $code, "state" => $state] = $_GET;

            getUser([
                "grant_type" => "authorization_code",
                "state" => $state,
                "code" => $code
            ]);
        }

        function handleError()
        {
            echo "refusé";
        }

        /**
        * AUTH_CODE WORKFLOW
        * => GET Code <- Générer le lien /auth (login)
        * => EXCHANGE Code <> Token (auth-success)
        * => GET USER by Token (auth-success)
        */
        $route = strtok($_SERVER["REQUEST_URI"], '?');
        switch ($route) {
            case '/login':
                handleLogin();
                break;
            case '/auth-success':
                handleSuccess();
                break;
            case '/fbauth-success':
                handleFBSuccess();
                break;
            case '/ghauth-success':
                handleGHSuccess();
                break;
            case '/discordauth-success':
                handleDISCORDSuccess();
                break;
            case '/auth-error':
                handleError();
                break;
            case '/password':
                if ($_SERVER['REQUEST_METHOD'] === "GET") {
                    echo "<form method='POST'>";
                    echo "<input name='username'>";
                    echo "<input name='password'>";
                    echo "<input type='submit' value='Log with oauth'>";
                    echo "</form>";
                } else {
                    ['username' => $username, 'password' => $password] = $_POST;
                    getUser([
                        'grant_type' => "password",
                        'username' => $username,
                        'password' => $password
                    ]);
                }
                break;
            default:
                http_response_code(404);
        }

        ?>
    </section>
</body>
</html>