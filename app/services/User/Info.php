<?php

namespace App\services\User;

use Orange\Message\Service;

/**
 * @name 获取用户信息
 * @service UserSvr.Info.Get
 * @protocol json
 */
class Info extends Service
{
    public function invoke()
    {
        $req = $this->request;
        print_r($req);
        $this->response->setData(['uid' => 2222]);

        $this->send();
    }
}