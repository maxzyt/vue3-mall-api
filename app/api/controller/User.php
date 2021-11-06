<?php

namespace app\api\controller;

use fast\Random;
use think\facade\Validate;
use app\common\library\Ems;
use app\common\library\Sms;
use app\common\controller\Api;
use app\common\model\UserAddress;
use app\common\model\UserCollect;
use app\common\model\UserHistory;

/**
 * 会员接口.
 */
class User extends Api
{
    protected $noNeedLogin = ['login', 'mobilelogin', 'register', 'resetpwd', 'changeemail', 'changemobile', 'third'];
    protected $noNeedRight = '*';

    public function _initialize()
    {
        parent::_initialize();
    }
    /**
     * 用户信息
     */
    public function userinfo()
    {
        $data=$this->auth->getUserinfo();
        $data['version']=config('site.version');
        $this->success('',$data);
    }
    /**
     * 删除浏览历史
     */
    public function delHistory()
    {
        $id=$this->request->param('id');
        $goodId=$this->request->param('goodId');
        return json(UserHistory::delHistory($this->auth->id,$id,$goodId));
    }
    /**
     * 浏览历史
     */
    public function history()
    {
        $page  = $this->request->param('page');
        $limit = $this->request->param('limit');
        $data  = UserHistory::getHistory($this->auth->id, $page, $limit);
        $this->success('', $data);
    }

    /**
     * 我的收藏
     */
    public function collect()
    {
        $page  = $this->request->param('page');
        $limit = $this->request->param('limit');
        $data  = UserCollect::getCollect($this->auth->id, $page, $limit);
        $this->success('', $data);
    }

    /**
     * 删除地址
     */
    public function delAddress()
    {
        $id  = $this->request->param('id');
        $row = UserAddress::find($id);
        if (empty($row) || $row->user_id != $this->auth->id) {
            $this->error(__('Invalid parameters'));
        }
        if ($row->default) {
            $this->error(__('默认地址不能删除'));
        }
        if ($row->delete()) {
            $this->success(__('删除成功'));
        } else {
            $this->error(__('删除失败'));
        }
    }

    /**
     * 编辑地址
     */
    public function editAddress()
    {
        $data = input('post.');
        $data['address']=$data['addressDetail'];
        $data['postal_code']=$data['postalCode'];
        $data['area']=$data['county'];
        $data['mobile']=$data['tel'];
        $data['areacode']=$data['areaCode'];
        $data['default']=$data['isDefault']?1:0;
        unset($data['addressDetail']);
        unset($data['postalCode']);
        unset($data['country']);
        unset($data['county']);
        unset($data['tel']);
        unset($data['isDefault']);
        unset($data['areaCode']);
        try {
            $row = UserAddress::find($data['id']);
            if (empty($row) || $row->user_id != $this->auth->id) {
                throw new \think\Exception(__('Invalid parameters'));
            }
            $row->save($data);
            if ($row->default != $data['default'] && !$row->default) {
                UserAddress::where('id', '<>', $data['id'])->update(['default' => 0]);
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('操作成功');
    }
    public function oneAddress()
    {
        $id=$this->request->param('id');
        $row=UserAddress::where('user_id',$this->auth->id)->where('id',$id)->find();
        empty($row)&&$this->error('参数错误');
        $this->success('',$row);
    }

    /**
     * 添加地址
     * addressDetail: "1234"
    areaCode: "130102"
    city: "石家庄市"
    country: ""
    county: "长安区"
    isDefault: true
    name: "张三"
    postalCode: "789012"
    province: "河北省"
    tel: "15290938565
     */
    public function addAddress()
    {
        $data            = input('post.');
        $data['address']=$data['addressDetail'];
        $data['postal_code']=$data['postalCode'];
        $data['area']=$data['county'];
        $data['mobile']=$data['tel'];
        $data['areacode']=$data['areaCode'];
        $data['default']=$data['isDefault']?1:0;
        unset($data['addressDetail']);
        unset($data['postalCode']);
        unset($data['country']);
        unset($data['county']);
        unset($data['tel']);
        unset($data['isDefault']);
        unset($data['areaCode']);
        $data['user_id'] = $this->auth->id;
        try {
            $rs = UserAddress::create($data);
            if ($data['default']) {
                UserAddress::where('user_id', $this->auth->id)->where('id', '<>', $rs->id)->update(['default' => 0]);
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('操作成功');
    }

    /**
     * 地址详情
     */
    public function addressDetail()
    {
        $id  = $this->request->param('id');
        $row = UserAddress::find($id);
        $this->success('', $row);
    }

    /**
     * 地址列表
     */
    public function address()
    {
        $data = UserAddress::getAddress($this->auth->id);
        $this->success('', $data);
    }

    /**
     * 会员中心.
     */
    public function index()
    {
        $this->success('', ['welcome' => $this->auth->nickname]);
    }

    /**
     * 会员登录.
     *
     * @param  string  $account  账号
     * @param  string  $password  密码
     */
    public function login()
    {
        $account  = $this->request->param('account');
        $password = $this->request->param('password');
        if (!$account || !$password) {
            $this->error(__('Invalid parameters'));
        }
        $ret = $this->auth->login($account, $password);
        if ($ret) {
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Logged in successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 手机验证码登录.
     *
     * @param  string  $mobile  手机号
     * @param  string  $captcha  验证码
     */
    public function mobilelogin()
    {
        $mobile  = $this->request->request('mobile');
        $captcha = $this->request->request('captcha');
        if (!$mobile || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        if (!Sms::check($mobile, $captcha, 'mobilelogin')) {
            $this->error(__('Captcha is incorrect'));
        }
        $user = \app\common\model\User::where('mobile', $mobile)->find();
        if ($user) {
            if ($user->status != 'normal') {
                $this->error(__('Account is locked'));
            }
            //如果已经有账号则直接登录
            $ret = $this->auth->direct($user->id);
        } else {
            $ret = $this->auth->register($mobile, Random::alnum(), '', $mobile, []);
        }
        if ($ret) {
            Sms::flush($mobile, 'mobilelogin');
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Logged in successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 注册会员.
     *
     * @param  string  $username  用户名
     * @param  string  $password  密码
     * @param  string  $email  邮箱
     * @param  string  $mobile  手机号
     * @param  string  $code  验证码
     */
    public function register()
    {
        $username = $this->request->param('username');
        $password = $this->request->param('password');
        $email    = $this->request->param('email');
        $mobile   = $this->request->param('mobile');
        $code     = $this->request->param('code');//var_dump($mobile.$password);
        if (!$mobile || !$password) {
            $this->error(__('Invalid parameters'));
        }
        if ($email && !Validate::is($email, 'email')) {
            $this->error(__('Email is incorrect'));
        }
        if ($mobile && !Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        $ret = Sms::check($mobile, $code, 'register');
        if (!$ret) {
            //$this->error(__('Captcha is incorrect'));
        }
        if (empty($email)) {
            $email = $username;
        }
        if (empty($mobile)) {
            $mobile = $username;
        }
        $ret = $this->auth->register($username, $password, $email, $mobile, []);
        if ($ret) {
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Sign up successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 注销登录.
     */
    public function logout()
    {
        $this->auth->logout();
        $this->success(__('Logout successful'));
    }

    /**
     * 修改会员个人信息.
     *
     * @param  string  $avatar  头像地址
     * @param  string  $username  用户名
     * @param  string  $nickname  昵称
     * @param  string  $bio  个人简介
     */
    public function profile()
    {
        $user     = $this->auth->getUser();
        $username = $this->request->request('username');
        $nickname = $this->request->request('nickname');
        $bio      = $this->request->request('bio');
        $avatar   = $this->request->request('avatar', '', 'trim,strip_tags,htmlspecialchars');
        if ($username) {
            $exists = \app\common\model\User::where('username', $username)->where('id', '<>', $this->auth->id)->find();
            if ($exists) {
                $this->error(__('Username already exists'));
            }
            $user->username = $username;
        }
        $user->nickname = $nickname;
        $user->bio      = $bio;
        $user->avatar   = $avatar;
        $user->save();
        $this->success();
    }

    /**
     * 修改邮箱.
     *
     * @param  string  $email  邮箱
     * @param  string  $captcha  验证码
     */
    public function changeemail()
    {
        $user    = $this->auth->getUser();
        $email   = $this->request->post('email');
        $captcha = $this->request->request('captcha');
        if (!$email || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::is($email, 'email')) {
            $this->error(__('Email is incorrect'));
        }
        if (\app\common\model\User::where('email', $email)->where('id', '<>', $user->id)->find()) {
            $this->error(__('Email already exists'));
        }
        $result = Ems::check($email, $captcha, 'changeemail');
        if (!$result) {
            $this->error(__('Captcha is incorrect'));
        }
        $verification        = $user->verification;
        $verification->email = 1;
        $user->verification  = $verification;
        $user->email         = $email;
        $user->save();

        Ems::flush($email, 'changeemail');
        $this->success();
    }

    /**
     * 修改手机号.
     *
     * @param  string  $mobile  手机号
     * @param  string  $captcha  验证码
     */
    public function changemobile()
    {
        $user    = $this->auth->getUser();
        $mobile  = $this->request->request('mobile');
        $captcha = $this->request->request('captcha');
        if (!$mobile || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        if (\app\common\model\User::where('mobile', $mobile)->where('id', '<>', $user->id)->find()) {
            $this->error(__('Mobile already exists'));
        }
        $result = Sms::check($mobile, $captcha, 'changemobile');
        if (!$result) {
            $this->error(__('Captcha is incorrect'));
        }
        $verification         = $user->verification;
        $verification->mobile = 1;
        $user->verification   = $verification;
        $user->mobile         = $mobile;
        $user->save();

        Sms::flush($mobile, 'changemobile');
        $this->success();
    }

    /**
     * 第三方登录.
     *
     * @param  string  $platform  平台名称
     * @param  string  $code  Code码
     */
    public function third()
    {
        $url      = url('user/index');
        $platform = $this->request->request('platform');
        $code     = $this->request->request('code');
        $config   = get_addon_config('third');
        if (!$config || !isset($config[$platform])) {
            $this->error(__('Invalid parameters'));
        }
        $app = new \addons\third\library\Application($config);
        //通过code换access_token和绑定会员
        $result = $app->{$platform}->getUserInfo(['code' => $code]);
        if ($result) {
            $loginret = \addons\third\library\Service::connect($platform, $result);
            if ($loginret) {
                $data = [
                    'userinfo'  => $this->auth->getUserinfo(),
                    'thirdinfo' => $result,
                ];
                $this->success(__('Logged in successful'), $data);
            }
        }
        $this->error(__('Operation failed'), $url);
    }

    /**
     * 重置密码
     *
     * @param  string  $mobile  手机号
     * @param  string  $newpassword  新密码
     * @param  string  $captcha  验证码
     */
    public function resetpwd()
    {
        $type        = $this->request->request('type');
        $mobile      = $this->request->request('mobile');
        $email       = $this->request->request('email');
        $newpassword = $this->request->request('newpassword');
        $captcha     = $this->request->request('captcha');
        if (!$newpassword || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if ($type == 'mobile') {
            if (!Validate::regex($mobile, "^1\d{10}$")) {
                $this->error(__('Mobile is incorrect'));
            }
            $user = \app\common\model\User::where('mobile', $mobile)->find();
            if (!$user) {
                $this->error(__('User not found'));
            }
            $ret = Sms::check($mobile, $captcha, 'resetpwd');
            if (!$ret) {
                $this->error(__('Captcha is incorrect'));
            }
            Sms::flush($mobile, 'resetpwd');
        } else {
            if (!Validate::is($email, 'email')) {
                $this->error(__('Email is incorrect'));
            }
            $user = \app\common\model\User::where('email', $email)->find();
            if (!$user) {
                $this->error(__('User not found'));
            }
            $ret = Ems::check($email, $captcha, 'resetpwd');
            if (!$ret) {
                $this->error(__('Captcha is incorrect'));
            }
            Ems::flush($email, 'resetpwd');
        }
        //模拟一次登录
        $this->auth->direct($user->id);
        $ret = $this->auth->changepwd($newpassword, '', true);
        if ($ret) {
            $this->success(__('Reset password successful'));
        } else {
            $this->error($this->auth->getError());
        }
    }
}
