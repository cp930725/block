<?php


namespace app\mobile\controller;

use app\index\controller\Account;
use app\index\controller\Base;
use app\index\controller\Configure;
use app\index\controller\Wallet;
use think\Db;
use think\Request;

class Index extends Base
{
    /**
     * 首页
     */
    public function index(Request $req)
    {

        $news = Db::table('article')->field('id, image, title, date')->where('type', '<>', 8)->where('date', '<=', date('Y-m-d H:i:s'))->order('top DESC, sort DESC, date DESC')->limit(5)->select();
        $this->assign('news', $news);

        $pool = $this->pool($req);

        $this->assign('config', $pool['config']);
        $this->assign('collect', $pool['collect']);
        $this->assign('props', $pool['props']);
        return $this->fetch();
    }

    /**
     * @param Request $req
     * @return mixed|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function pool(Request $req)
    {
        // 获取配置
        $config = Configure::get('hello.event.pool');
        if (empty($config) || empty($config['enable'])) {
            $this->error('很抱歉、该模块尚未开启！');
            exit;
        }
        $this->assign('config', $config);
        // 用户账号
        $username = session('user.account.username');
        // 已领总矿
        $collect = Db::table('pool')->where('action', '=', 1)->sum('reward');
        $this->assign('collect', $collect);
        // 用户账号
        $user = (new Account())->instance($username, null, null, true);
        if (empty($user)) {
            return json([
                'code'      =>  500,
                'message'   =>  '很抱歉、请重新登录！',
            ]);
        }
        // 请求处理
        if ($req->isPost()) {
            // 最终返回的数据
            $data = [];
            try {
                // 开启事务
                Db::startTrans();
                // 行为判断
                switch ($req->param('action')) {
                    // 倒计时
                    case 'countdown':
                        // 查询上次领取或加入的时间
                        $last_at = Db::table('pool')->where('username', '=', $username)->where('action', '<>', 2)->order('create_at DESC')->value('create_at');
                        if (empty($last_at)) {
                            // 第一次加入，那么上次的时间就是当前时间
                            $last_at = $this->timestamp;
                            // 如果他拥有算力的话，那就开始计算
                            if ($user['dashboard']['power'] > 0) {
                                $bool = Db::table('pool')->insert([
                                    'username'      =>  $username,
                                    'action'        =>  3,
                                    'power'         =>  0,
                                    'prop'          =>  null,
                                    'spend'         =>  0,
                                    'reward'        =>  0,
                                    'create_at'     =>  $last_at,
                                ]);
                                if (empty($bool)) {
                                    return json([
                                        'code'      =>  501,
                                        'message'   =>  '很抱歉、请刷新页面再试！',
                                    ]);
                                }
                            }
                        }
                        // 保存时间
                        $data['duration'] = $config['interval'];
                        $data['last_at'] = $last_at;
                        break;
                    // 领取收益
                    case 'profit':
                        // 账号判断
                        if (empty($user['account']['status'])) {
                            return json([
                                'code'      =>  500,
                                'message'   =>  '很抱歉、您的账号已被冻结！',
                            ]);
                        }
                        if ($user['account']['authen'] != 1) {
                            return json([
                                'code'      =>  500,
                                'message'   =>  '很抱歉、请先完成实名认证！',
                            ]);
                        }
                        if (empty($user['dashboard']['power']) || $user['dashboard']['power'] <= 0) {
                            return json([
                                'code'      =>  500,
                                'message'   =>  '很抱歉、您的能量不足！',
                            ]);
                        }
                        // 查询上次领取或加入的时间
                        $last_at = Db::table('pool')->where('username', '=', $username)->where('action', '<>', 2)->order('create_at DESC')->value('create_at');
                        if (empty($last_at)) {
                            return json([
                                'code'      =>  501,
                                'message'   =>  '很抱歉、请刷新页面再试！',
                            ]);
                        }
                        // 对比时间
                        $second = time() - strtotime($last_at);
                        if ($second < $config['interval']) {
                            return json([
                                'code'      =>  502,
                                'message'   =>  '很抱歉、请过一段时间再来！',
                            ]);
                        }
                        $second = $second > $config['interval'] ? $config['interval'] : $second;
                        // 剩余的比例
                        $collectPercent = money(1 - ($collect / $config['volume']));
                        // 计算收益
                        $profit = money($user['dashboard']['power'] * $second * $config['percent'] * $collectPercent);
                        if ($profit <= 0) {
                            return json([
                                'code'      =>  503,
                                'message'   =>  '很抱歉、您来晚了！',
                                'data'      =>  [
                                    $config['volume'], $collect, $collectPercent,
                                    $user['dashboard']['power'], $second, $config['percent']
                                ]
                            ]);
                        }
                        // 保存记录
                        $bool = Db::table('pool')->insert([
                            'username'      =>  $username,
                            'action'        =>  1,
                            'power'         =>  $user['dashboard']['power'],
                            'prop'          =>  null,
                            'spend'         =>  0,
                            'reward'        =>  $profit,
                            'create_at'     =>  $this->timestamp,
                        ]);
                        if (empty($bool)) {
                            return json([
                                'code'      =>  504,
                                'message'   =>  '很抱歉、服务器繁忙请稍后再试！',
                            ]);
                        }
                        // 给用户加钱
                        (new Wallet())->change($username, 18, [
                            1       =>  [
                                $user['wallet']['money'],
                                $profit,
                                $user['wallet']['money'] + $profit,
                            ],
                        ]);
                        // 操作记录
                        $this->log(66);
                        break;
                    default:
                        # code...
                        break;
                }
                // 提交事务
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                return json([
                    'code'      =>  555,
                    'message'   =>  $e->getMessage()
                ]);
            }
            // 操作成功
            return json([
                'code'      =>  200,
                'message'   =>  '恭喜您、操作成功！',
                'data'      =>  $data
            ]);
        }
        // 查询道具
        $props = Db::table('store')->where('catalog', '=', 2)->order('sort DESC')->select();
        $this->assign('props', $props);
        // 显示页面
        $pool = [
            'config'    =>  $config,
            'collect'   =>  $collect,
            'props'     =>  $props
        ];
        return $pool;
        return $this->fetch();
    }
}