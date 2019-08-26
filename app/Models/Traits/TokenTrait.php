<?php
/**
 * @author ihipop@gmail.com @ 17-11-3 上午9:52 For xinling-server.
 */

namespace App\Models\Traits;

use App\Services\TokenService;
use Illuminate\Support\Str;

trait TokenTrait
{

    public $lastToken;//方便observer知道最后一次set/get 的Token是哪个 这样可以在模型改变的时候自动更新对应的Token里面的信息

    public static function getTableName()
    {
        return str_replace('\\', '', Str::snake(Str::plural(class_basename(self::class))));
    }

    public function tokenSet($token = null)
    {
        if ($token) {
            $this->lastToken = $token;
        }
        if (!$this->lastToken) {
            throw  new \InvalidArgumentException('$token is needed');
        }

        return TokenService::set($this->lastToken, self::getTableName(), $this);
    }

    public static function tokenGet($token)
    {
        /** @var  $instance $this */
        $instance = TokenService::get($token, self::getTableName());
        if ($instance instanceof self) {
            $instance->lastToken = $token;
        }

        return $instance;
    }
}
