<?php

/**
 * UUID缓存类
 *
 * @author tianweimin
 */

namespace Cache;

class UUID extends \Base\Model\Redis {
    
    //有效期
    const EXPIRE = 604800;
    
    //前缀
    const PREFIX_UUID = 'CACHE:UUID:%s';
    
    const PREFIX_UUID_MAP = 'CACHE:UUID-MAP:%s';
    
    //保存uuid的一个
    public function save($uuid, $data, $ckey = NULL, $out = NULL)
    {
        if (!empty($ckey) && !empty($out)) {
            $ekey = sprintf(self::PREFIX_UUID_MAP, $ckey);
            $this->redis->setex($ekey, 600, serialize($out));
        }
        $key = sprintf(self::PREFIX_UUID, $uuid);
        $res = $this->redis->setex($key, self::EXPIRE, serialize($data));
        return $res;
    }
    
    //清除
    public function clear($uuid) {
        $key = sprintf(self::PREFIX_UUID, $uuid);
        return $this->redis->del($key);
    }
    
    //查询
    public function query($uuid) {
        $key = sprintf(self::PREFIX_UUID, $uuid);
        if (!$this->redis->exists($key)) {
            return false;
        }
        return unserialize($this->redis->get($key));
    }
    
    //验证是否重复请求
    public function virifyRepeat($ckey) {
        $ekey = sprintf(self::PREFIX_UUID_MAP, $ckey);
        if ($this->redis->exists($ekey)) {
            return unserialize($this->redis->get($ekey));
        }
        return false;
    }
    
}
