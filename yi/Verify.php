<?php

namespace yi;

use support\exception\Exception;
use Illuminate\Support\Str;
use app\system\model\admin\VerifyModel;

class Verify
{

    /**
     * 验证码有效时长
     */
    protected static $expire = 5 * 60;

    /**
     * 允许失败次数
     */
    protected static $limitTimes = 3;

    /**
     * 生成验证码
     */
    public static function get($name, $type, $code, $event)
    {
        $now = time();
        $verify = VerifyModel::where([
            ['name', '=', $name],
            ['event', '=', $event],
            ['type', '=', $type],
        ])->orderByRaw('id DESC')->first();
        if ($verify && $verify->created_at->timestamp + static::$expire > $now) {
            return $verify;
        }
        $code = $code ?: mt_rand(1000, 9999);
        $data = [
            'name' => $name,
            'type' => $type,
            'code' => $code,
            'event' => $event,
            'created_at' => $now,
            'ip' => get_ip()
        ];
        return VerifyModel::create($data);
    }

    /**
     * 发送验证码
     * @param string $type 验证类型：email|sms
     */
    public static function send($name, $type = 'email', $code = null, $event = 'default')
    {
        $verify = static::get($name, $type, $code, $event);
        $payload = (object)[
            'verify' => $verify,
            'result' => false
        ];
        event(Str::studly($type . '_send'), $payload);
        if (!$payload->result) {
            $verify->delete();
            return false;
        }
        return true;
    }

    public static function check($name, $code, $type = 'email', $event = 'default')
    {
        $verify = VerifyModel::where([
            ['name', '=', $name],
            ['event', '=', $event],
            ['type', '=', $type],
        ])->orderByRaw('id DESC')->first();
        if (empty($verify)) throw new Exception("验证码不存在");
        $now = time();
        if ($verify->created_at->timestamp + static::$expire < $now) {
            static::clear($name, $type, $event);
            throw new Exception("验证码已过期");
        }
        if ($code != $verify['code']) {
            $verify->times += 1;
            $verify->save();
            throw new Exception("验证码错误");
        }

        if ($verify['times'] > self::$limitTimes) {
            $verify->delete();
            throw new Exception("重试次数过多，请重新发送验证码");
        }

        event(Str::studly($type . '_check'), $verify);
        $verify->delete();
        return true;
        
    }

    public static function clear($name, $type = 'email', $event = 'default')
    {
        $where = [
            ['name', '=', $name],
            ['type', '=', $type],
            ['event', '=', $event]
        ];
        VerifyModel::where($where)->delete();
        event(Str::studly($type . '_clear'), $data);
        return true;
    }
}