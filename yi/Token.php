<?php

namespace yi;

use support\Db;

class Token
{
    protected $config = [
        'table' => 'user_token',
        'scene' => 'user',
        'key'      => 'i3d6o32wo8fvs1fvdpwens',
        'hashalgo' => 'ripemd160',
        'expire' => 7 * 24 * 60 * 60
    ];

    protected $error_msg = null;

    public static function init($config = [])
    {
        return new static($config);
    }

    public function __construct($config = [])
    {
        $this->config = array_merge($this->config, $config);
        $this->handler = Db::table($this->config['table']);
    }

    public function get($token)
    {
        $data = $this->handler->where('token', $this->entryToken($token))->where('scene', $this->config['scene'])->first();
        if ($data) {
            $data = collect($data)->toArray();
            if (!$data['expiretime'] || $data['expiretime'] > time()) {
                $data['token'] = $token;
                $data['expires_in'] = $this->getExpiredIn($data['expiretime']);
                return $data;
            } else {
                $this->delete($token);
            }
        }
        return [];
    }

    public function set($token, $id, $expire = null)
    {
        $data = [
            'user_id' => $id,
            'scene' => $this->config['scene'],
            'token' => $this->entryToken($token),
            'created_at' => time(),
            'expiretime' => time() + ($expire ?: $this->config['expire'])
        ];
        $this->handler->insert($data);
        return $this;
    }

    public function check($token, $id)
    {
        $token = $this->get($token);
    }

    public function fresh($token, $expire = null)
    {
        $expiretime = time() + ($expire ?: $this->config['expire']);
        $this->handler->where('token', $this->entryToken($token))->where('scene', $this->config['scene'])->update(['expiretime' => $expiretime]);
    }

    public function delete($token)
    {
        $this->handler->where('token', $this->entryToken($token))->where('scene', $this->config['scene'])->delete();
        return true;
    }

    protected function entryToken($token)
    {
        return hash_hmac($this->config['hashalgo'], $token, $this->config['key']);
    }

    public function setError($msg)
    {
        $this->error_msg = $msg;
        return $this;
    }

    public function getError()
    {
        return $this->error_msg;
    }
    
    protected function getExpiredIn($expiretime)
    {
        return $expiretime ? max(0, $expiretime - time()) : 365 * 86400;
    }
}