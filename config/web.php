<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    // 'modules'=>[
    //     'oauth2' => [
    //         'class' => 'filsh\yii2\oauth2server\Module',            
    //         'tokenParamName' => 'accessToken',
    //         'tokenAccessLifetime' => 3600 * 24,
    //         'storageMap' => [
    //             'user_credentials' => 'app\models\User',
    //         ],
    //         'grantTypes' => [
    //             'user_credentials' => [
    //                 'class' => 'OAuth2\GrantType\UserCredentials',
    //             ],
    //             'refresh_token' => [
    //                 'class' => 'OAuth2\GrantType\RefreshToken',
    //                 'always_issue_new_refresh_token' => true
    //             ]
    //         ]
    //     ]
    // ],
    'modules' => [
        'api' => [
            'class' => 'app\modules\api\ApiModule',
        ],
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'Kg3znSoywEdPZbnwQJ6qvt34hEnkKH0s',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
            'enableCsrfCookie' => false
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            //'enableAutoLogin' => true,
            //'loginUrl' => ['site/login'],
            // 'enableSession' => false,
            'loginUrl' => null,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'useFileTransport' => false,
            'viewPath' => '@app/mail',
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.gmail.com',
                'username' => 'silverinteraction@gmail.com',
                'password' => 'Kyouma1400',
                'port' => '465',
                'encryption' => 'ssl',
                'streamOptions' => [
                    'ssl' => [
                        'allow_self_signed' => true,
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                    ],
                ],
            ],
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            //'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [
                ['class' => 'yii\rest\UrlRule', 'pluralize' => false, 'controller' => 'user'],
                ['class' => 'yii\rest\UrlRule', 'controller' => ['api/token']],
            ],
            // 'enablePrettyUrl' => true,
            // //'enableStrictParsing' => true,
            // 'showScriptName' => false,
            // 'rules' => [
            // ],
            // 'rules' => [
            //     ['class' => 'yii\rest\UrlRule', 'controller' => 'user'],
            // ],
            // 'rules' => [
            //     'POST oauth2/<action:\w+>' => 'oauth2/rest/<action>',
            // ]
        ],
    ],
    // 'as access' => [
    //     'class' => 'mdm\admin\components\AccessControl',
    //     'allowActions' => [
    //         "api/*",
    //     ],
    // ],
    'as beforeRequest' => [
        'class' => 'yii\filters\AccessControl',
        'rules' => [
            [
                'actions' => ['login', 'error', 'api'],
                'allow' => true,
            ],
            [
                'allow' => true,
                'roles' => ['@'],
            ]
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
