<?php
return [
    'components' => [
        // wechat 配置
        'wechat' => [
            'app_id' => 'wxc532cc9a793ef689',
            'secret' => 'd4624c36b6795d1d99dcf0547af5443d',
            'token' => 'dHVhYm5nemh1',
        ],
        // Oss 配置
        'oss' => [
            'accessKeyId' => 'LTAIv4N83CO6YfFy',
            'accessKeySecret' => 'H1nVZLptjrcxFdVmiXRJEXpAXUHck9',
            'endpoint' => 'oss-cn-beijing.aliyuncs.com',
            'bucket' => 'tubangzhu-dev',
            'callbackUrl' => 'http://xia.tubangzhu.site/oss/callback'
        ],
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=rm-2ze1pn92s109h67lx3o.mysql.rds.aliyuncs.com;dbname=tubangzhudev',
            'username' => 'tubangzhudev',
            'password' => 'jpEHu2JEXessDyUv',
            'charset' => 'utf8',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'sms' => [
            'app_key' => '23376936',
            'app_secret' => '811551fb042fdbb50a40fb3c134dac0d',
        ],
        'alipay' => [
            'app_id' => '2016091500515260',
            'ali_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAv0mV7K9M+Xqj5tL8UliRGks4SOGXptCWfUVYDjsK0FQAKZHZyFbnz2trUMW7AtvI//k2+oTjyT2XUch+iKfuHyfJwgOALhNSSTEYAXu8SZ92hCgUQwnQ2XrGFywoJf4Niu6mzg04LVm7n8FGhEOpG1rjmy+ti6K9UHOxqllcnYf3HIsKzAmPxKelR4O6anpJ8+kbyl9I6lZWOEV8k6ryWXL8l4krajPYIIk526NIJRx67Os2gE+l3pBI6KPcINBC23HA5WgJ7Dbhewpf3SBM5jIBjyMBYtRsNxevTnXQxxo5i9XEG5v3keqfK1QjldXT3vEA03qcy2mTk1YkbnABQwIDAQAB',
            'private_key' => 'MIIEpAIBAAKCAQEAv0mV7K9M+Xqj5tL8UliRGks4SOGXptCWfUVYDjsK0FQAKZHZyFbnz2trUMW7AtvI//k2+oTjyT2XUch+iKfuHyfJwgOALhNSSTEYAXu8SZ92hCgUQwnQ2XrGFywoJf4Niu6mzg04LVm7n8FGhEOpG1rjmy+ti6K9UHOxqllcnYf3HIsKzAmPxKelR4O6anpJ8+kbyl9I6lZWOEV8k6ryWXL8l4krajPYIIk526NIJRx67Os2gE+l3pBI6KPcINBC23HA5WgJ7Dbhewpf3SBM5jIBjyMBYtRsNxevTnXQxxo5i9XEG5v3keqfK1QjldXT3vEA03qcy2mTk1YkbnABQwIDAQABAoIBAQCBmKgzd7zt1RIQQ2dhntGH/+g9MGHfSbh7XKzAz22PISoO/9qNqZtZ4swNKDmAQUmXas+9wKTW5ZyMcwqPKT7h6sH9aQPBs6NvJQy/jIZPVvMjrEe194OApHZqoqb8vneMZg2q0jf6Oa4tGPPejjyW5OgJbq7kSLh8NjXrVKmzq4qxgo+jrXTvVyeRmTsQmJQ8pbnES7Q7v7Y2vWtpJ79SxRqqDvlE/8CPokj5bqvysGBZPMvHgS36cBMc34N+zqVrofgLQ5Ib9YPw6mZKArAtkXjq/kWghwKz3b6W/UHQp8gaIKLxyvN04z7rYtURg0W20oAkKL6DYf4pIpFTFbqpAoGBAO3+5PDb/eFKnlHIyXyGDKTXtT1I+zQdlF8nZZ9q04RaoW8vD525t6dEVCshr8//u4/9sPBrp3nS005hjiTiurTOveuOtDqIKok3D8L2g2VcHsSj3Y1BSsNF6mXvFCnWIWZRZVZZfTSnSrjcrvW82pMexu6L38zeOa3pjPIA8Q/PAoGBAM3CH6hIufBH9KJBSkCCyhA0nsvXidHu4oGuRFRJKar+KiIS932Iw/m+htKACwUOWsKCEOZChdW8ioawyjIDnTOhnXVy9MreFH97HR/zjRaV4SG8tPmJliQmEAAU06+dAmRGYCPxsuQDHmYX1qm1cKs2apJgdgeDJIHBa7u+fcBNAoGAUW6dtywOuj9l3GXvSwQy31RPVyDZkwNr+QLp6poKtYatJfXcSgN5q2ndwsRL+8dszd/n4tty+mQAmRkYIdbSO3th0G+Li1t78mc8pWDjpJLYlD/dVq+4fQg3I5miNI5n7zJ6kgkcph93mbkoxDxSLXSibIy/jsGayr7W0xcvZFECgYBxfUVg2NxWQBoa9NAzzPAPCDceUABgntaomKpvovssqYwrxzJjN3CA7CoJKT7qxwQgjQUtPDv9rETsDc84zu2CcP4crg9/ZgcAWbOyz+9eKcVHioJ3sP/zaFsi8FniX0PWc0rMCKCXS3EU9skcSkue5CDcJAB2HEuilkEKCQPrcQKBgQDJr385OlETJVac4tVVDB4Uzq9Fu6l90qGFYuNi0JgF3vcsPwfJY0PP5zCWu72NktSS2SaV9hfi2wGN08OdakpAy2yaWxBGkFupzxqTq0B6XgUE5GSdtbJqV649rplyzx2gFfkb4m5uWvPbaLcNCafVPNagS/rqhCRCVvqENWynHQ==',
            'mode' => 'dev'
        ]
    ],
];
