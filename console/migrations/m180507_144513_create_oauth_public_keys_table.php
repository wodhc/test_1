<?php

use yii\db\Migration;

/**
 * Handles the creation of table `oauth_public_keys`.
 */
class m180507_144513_create_oauth_public_keys_table extends Migration
{
    public $tableName = '{{%oauth_public_keys}}';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'client_id' => $this->string(255)->notNull()->comment('client_id'),
            'public_key' => $this->string(2000)->notNull()->comment('公钥'),
            'private_key' => $this->string(2000)->notNull()->comment('私钥'),
            'encryption_algorithm' => $this->string(100)->notNull()->comment('加密方式'),
            'PRIMARY KEY(client_id)'
        ]);

        $public_key = "-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAvqLkG1oRxwNSs6DkTiIs
vrbYrq8SU+Zkr0JJg19yXDqjcTYM0zCvJaq6UbPjLIECjjFdraSGTJRDG7ENthBm
BjInVTLo5JeeA5nDAE37VHR+T1fvzBMbdPUw+Ie6RVjMTFIUreMA0Sj5uc0LUhxH
jDxpymdqmWPl+tXo3d6TSxhL2Lv8XM5WYviCX+MmiAMdXyQspie8w1yadRCoEqIa
u7pQdwwb9B3tcPqoC05tlhmWu8oltOe23kI3qucO/GeCbOdOE5RvF5IifhAb6V2a
SLGVhK9zvpTcRWF8Zola4XH5S6y/dsGzTPzxoU4oXtE/YnRlBt/F5kQQgafwBJgq
GQIDAQAB
-----END PUBLIC KEY-----
";

        $private_key = "-----BEGIN RSA PRIVATE KEY-----
MIIEogIBAAKCAQEAvqLkG1oRxwNSs6DkTiIsvrbYrq8SU+Zkr0JJg19yXDqjcTYM
0zCvJaq6UbPjLIECjjFdraSGTJRDG7ENthBmBjInVTLo5JeeA5nDAE37VHR+T1fv
zBMbdPUw+Ie6RVjMTFIUreMA0Sj5uc0LUhxHjDxpymdqmWPl+tXo3d6TSxhL2Lv8
XM5WYviCX+MmiAMdXyQspie8w1yadRCoEqIau7pQdwwb9B3tcPqoC05tlhmWu8ol
tOe23kI3qucO/GeCbOdOE5RvF5IifhAb6V2aSLGVhK9zvpTcRWF8Zola4XH5S6y/
dsGzTPzxoU4oXtE/YnRlBt/F5kQQgafwBJgqGQIDAQABAoIBAHlLpX3twi/xYlae
wYUhY3z5VEdZZ3zwtBF7SNEJEvex67ql3GUnrdl2ltRTc/EmKN0Wdfd9HmJtV4Gw
FvxH1NKOaM5h9SM1gTnjRNrVw1uKP6/2UMJ1SdwSAlWv7oofJOZOt4+oRyXOP47D
8zcMfU2yRtGdGYt9r5/5KRC6hNebBbVb9EWxMvslfRY6YGakTXyKlj6DZLbAr2MH
qPiU31/Scunw6rHKFStetoVDGHSdJqVkJxzty8wl5P00Xkmnkval4M+5usMfiUqU
NywEzPvmenBON82ZI73HCqQWFF5LtJofNvZ9iocqO/yN1ZtWzoX3DmQbwJSrai8L
cD+Ls4ECgYEA4vRXLt95sLZLMlSHAyNmE0j+Ly/uFEoPz6cJXcb+DESgKjf8Tcvk
XMfCTfH22DL8xSBBRkyeXrpGHZyfd84CZLRTOi9DYFFetFXiRfeBeNAC5GijYfQx
kvXI+0y3xgCFPtM3k1/6jBmxe2RPIPkGaomVevBC24exueskqVbn1lECgYEA1wiq
m6promy75eelJ4sEJBS8ZYPea+jMXu2Kd562OQfK8U4ovr/b9d0REuPgvF3win/l
pU4dkSymLRrvwnZzuUNpfj6JF/pTyzb72G+UTsyaWCWwFz5MBdERQR3wmByXbKHZ
fKASndMR4YwZ8s45bKpsg4G/dO3sPfqj0YFO/UkCgYBYKvxDpWWtFOSZpOTefz4E
d/LnegNPtoX18bpdyBShx3dBb4aA4pjQwnnltf5jd0tjeWhiWxmbS1o53sLE1C9x
1+wNSpcuL+5OuJ6y97hAqu60tUHjp+4qXXm6xs8OCN26zmtkwYCgNMsSWKG0+YlY
kiKUJAqVJl4REByp/K0MQQKBgBuWaQz/mD+GY7TjOzUPiMB4SJNdj7OYcl/baeBe
5FH8xfSfamrOgpHQgthBlBuWkb2zGutSUkjFyawPwKLaP46NL4Oa0kvZOdbgfv8N
kaFqboLQkv7oZyh34hbQmIVrZaHyQczTXJAS/EBLkSitfICMfM+CXeXKgHyTI2FF
jbTBAoGAD5T3EQX+zSkPZBcb4OmsUe2JStXRcSclB80zmb37cb821RySknHf0rbX
8KK4tCHeqDgDdoylzeh1eCAxlEjBGj2vhKmUFri5Q1BLytrGeD5oH6lwkk19bM1k
94eOIAw7UK4vPdaB0RF6U6t6tMx6cM95meiRXpMTa7nm1t44ffw=
-----END RSA PRIVATE KEY-----
";


        $this->getDb()->createCommand()->insert($this->tableName, [
            'client_id' => 'tubangzhu_web',
            'public_key' => $public_key,
            'private_key' => $private_key,
            'encryption_algorithm' => 'RS256'
        ])->execute();

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }
}
