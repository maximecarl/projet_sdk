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

        require('./Auth.php');

        $auth = new Auth();

        $auth->addSdkObject(
            'client_6070546c6aba63.16480463',
            '38201ad253c323a79d9108f4588bbc62d2e1a5c6',
            'oauth',
            [
                [
                    'link' => 'http://localhost:8081/auth',
                    'params' => [
                        'scope' => 'basic'
                    ]
                ],
                [
                    'link' => 'http://oauth-server:8081/token',
                    'method' => 'GET',
                    'params' => []

                ],
                [
                    'link' => 'http://oauth-server:8081/me',
                    'params' => []
                ]
            ]
        );
        $auth->addSdkObject(
            '1220507708383867',
            '387d25fd1c6240e83dbfdb51f8e2697f',
            'facebook',
            [
                [
                    'link' => 'https://www.facebook.com/v2.10/dialog/oauth',
                    'params' => [
                        'scope' => 'email'
                    ]
                ],
                [
                    'link' => 'https://graph.facebook.com/oauth/access_token',
                    'method' => 'GET',
                    'params' => []
                ],
                [
                    'link' => 'https://graph.facebook.com/me',
                    'params' => []
                ]
            ]
        );
        $auth->addSdkObject(
            '859424765038690304',
            'aTYS5dzTzdnnVIQAM3zpILEg7xW2TXi7',
            'discord',
            [
                [
                    'link' => 'https://discord.com/api/oauth2/authorize',
                    'params' => [
                        'scope' => 'identify guilds',
                    ]
                ],
                [
                    'link' => 'https://discord.com/api/oauth2/token',
                    'method' => 'POST',
                    'params' => []
                ],
                [
                    'link' => 'https://discord.com/api/users/@me',
                    'params' => []
                ]
            ]
        );
        $auth->addSdkObject(
            '593042b861f98bce0c7b',
            '7e091195df4e2a8a37c562339da35f08c407e954',
            'github',
            [
                [
                    'link' => 'https://github.com/login/oauth/authorize',
                    'params' => [
                        'scope' => 'user',
                    ]
                ],
                [
                    'link' => 'https://github.com/login/oauth/access_token',
                    'method' => 'POST',
                    'params' => []
                ],
                [
                    'link' => 'https://api.github.com/user',
                    'params' => []
                ]
            ]
        );

        $route = strtok($_SERVER["REQUEST_URI"], '?');
        switch ($route) {
            case '/login':
                $auth->handleLogin();
                break;
            case '/auth-success':
                $auth->handleSuccess();
                break;
            case '/auth-error':
                $auth->handleError();
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