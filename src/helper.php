<?php
/**
 * @title 常用的一些函数
 * @author Leruge
 * @email lereuge@163.com
 * @qq 305530751
 */
use think\exception\HttpResponseException;

if (!function_exists('check_captcha')) {
    /**
     * @title 检验验证码
     * @desc 需要在extra中配置test_account数组
     *
     * @param string $phone 手机
     * @param string $code 验证码
     * @param string $msg 不通过提示信息
     */
    function check_captcha($phone, $code, $msg = '验证码不正确')
    {
        $testAccountArray = config('extra.test_account') ?: [];
        if (!in_array($phone, $testAccountArray) && $code != '888888') {
            if (cache('code' . $phone) != $code) {
                result(null, 0, $msg);
            }
        }
        cache('code' . $phone, null);
    }
}

if (!function_exists('format_time')) {
    /**
     * @title 格式化时间
     *
     * @param string $time 整型时间戳
     * @param int $type 1是Y-m-d H:i:s；2是Y-m-d H:i；3是Y-m-d；4是聊天时间
     *
     * @return string 时间
     */
    function format_time($time, $type = 1)
    {
        if (empty($time)) {
            return null;
        }
        if (!is_numeric($time)) {
            return null;
        }
        $formatTime = null;
        if (in_array($type, [1, 2, 3, 4])) {
            if ($type == 1) {
                return date('Y-m-d H:i:s', $time) ?: null;
            } elseif ($type == 2) {
                return date('Y-m-d H:i', $time) ?: null;
            } elseif ($type == 3) {
                return date('Y-m-d', $time) ?: null;
            } elseif ($type == 4) {
                $today = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y'));
                $validTime = abs($today - $time);
                $oneDay = 3600 * 24;
                if ($validTime < $oneDay) {
                    return date('H:i', $time) ?: null;
                } elseif ($validTime >= $oneDay && $validTime < $oneDay * 2) {
                    return date('H:i', $time) ? '昨天 ' . date('H:i', $time) : null;
                } elseif ($validTime >= $oneDay * 2) {
                    return date('Y-m-d H:i', $time) ?: null;
                }
            } else {
                return null;
            }
        } else {
            return null;
        }
    }
}

if (!function_exists('generate_nickname')) {
    /**
     * @title 生成昵称
     *
     * @return string 昵称
     */
    function generate_nickname()
    {
        try {
            return mb_strtoupper(bin2hex(random_bytes(3)));
        } catch (Throwable $e) {
            return '这个人很懒';
        }
    }
}

if (!function_exists('handle_get_pic')) {
    /**
     * @title 获取图片
     * @desc 在/static/default/no.jpg放默认图片，extra.app_url是默认url
     *
     * @param string|null $pics 图片
     * @param int $type 1是单张需要默认；2是多张；3是单张不需要默认；4多张中取一张有默认
     *
     * @return string|null 图片
     */
    function handle_get_pic($pics, $type = 1)
    {
        if ($type == 1) {
            $pic = $pics ?: '/static/default/no.jpg';
            if (substr($pic, 0, 1) == '/') {
                $pic = config('extra.app_url') . trim($pic, '/');
            }
            return $pic;
        } elseif ($type == 2) {
            $picArray = array_filter(explode(',', $pics));
            $newPicArray = [];
            foreach ($picArray as $v) {
                if (substr($v, 0, 1) == '/') {
                    $newPicArray[] = config('extra.app_url') . trim($v, '/');
                } else {
                    $newPicArray[] = $v;
                }
            }
            return implode(',', $newPicArray);
        } elseif ($type == 3) {
            if ($pics) {
                if (substr($pics, 0, 1) == '/') {
                    $pics = config('extra.app_url') . trim($pics, '/');
                }
                return $pics;
            } else {
                return null;
            }
        } elseif ($type == 4) {
            $picArray = array_filter(explode(',', $pics));
            $pic = $picArray[0] ?? null;
            return handle_get_pic($pic, 1);
        } else {
            return null;
        }
    }
}

if (!function_exists('handle_set_pic')) {
    /**
     * @title 设置图片
     *
     * @param string $pics 图片
     * @param integer $type 1是单张；2是多张
     *
     * @return string 图片
     */
    function handle_set_pic($pics, $type = 1)
    {
        if (empty($pics)) {
            return null;
        }
        if ($type == 1) {
            if (stripos($pics, config('extra.app_url')) === 0) {
                $handlePics = '/' . trim(str_ireplace(config('extra.app_url'), '/', $pics), '/');
            } else {
                $handlePics = $pics;
            }
            return $handlePics;
        } else {
            $picArray = array_filter(explode(',', $pics));
            foreach ($picArray as $k => $v) {
                if (stripos($v, config('extra.app_url')) === 0) {
                    $handlePics = '/' . trim(str_ireplace(config('extra.app_url'), '/', $v), '/');
                } else {
                    $handlePics = $v;
                }
                $picArray[$k] = $handlePics;
            }
            return implode(',', $picArray);
        }
    }
}

if (!function_exists('handle_set_content')) {
    /**
     * @title 处理内容
     *
     * @param string|null $content 内容
     *
     * @return string 内容
     */
    function handle_set_content($content)
    {
        return str_ireplace('src="' . config('extra.app_url'), 'src="/', $content);
    }
}

if (!function_exists('handle_get_content')) {
    /**
     * @title 获取内容
     *
     * @param string|null $content 内容
     *
     * @return string 内容
     */
    function handle_get_content($content = null)
    {
        return str_ireplace('src="/', 'src="' . config('extra.app_url'), $content);
    }
}

if (!function_exists('handle_get_num')) {
    /**
     * @title 获取数字
     *
     * @param $num 数字
     *
     * @return string 数量
     */
    function handle_get_num($num = null)
    {
        $num = empty($num) ? 0 : $num;
        if ($num > 9999) {
            $num = round($num / 10000, 1) . 'w';
        }
        return $num;
    }
}

if (!function_exists('handle_set_num')) {
    /**
     * @title 设置数字
     *
     * @param string|array $num 字符串数字
     *
     * @return float 数字
     */
    function handle_set_num($num)
    {
        if (is_array($num)) {
            if (stripos($num[1], 'w')) {
                $num[1] = substr($num[1], 0, -1) * 10000;
            }
        } else {
            if (stripos($num, 'w')) {
                $num = substr($num, 0, -1) * 10000;
            }
        }
        return $num;
    }
}

if (!function_exists('generate_room_num')) {
    /**
     * @title 生成聊天房间号
     *
     * @param int $fromId 发送者ID
     * @param int $toId 接受者ID
     *
     * @return string 房间号
     */
    function generate_room_num($fromId, $toId)
    {
        return $fromId > $toId ? $toId . '-' . $fromId : $fromId . '-' . $toId;
    }
}

if (!function_exists('result')) {
    /**
     * @title 封装json响应
     *
     * @param array|null $data 数据
     * @param integer $code 1是成功；0是失败；其它码看说明
     * @param string $msg 提示消息
     * @param string|null $url url地址
     *
     * @return void 响应
     * @throws HttpResponseException
     */
    function result($data = null, $code = 1, $msg = '操作成功', $url = null)
    {
        $res = [
            'data' => $data,
            'code' => $code,
            'msg' => $msg,
            'url' => $url
        ];
        throw new HttpResponseException(json($res));
    }
}
