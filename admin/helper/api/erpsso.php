<?php
/**
 * Created by Sublime.
 * User: leonchou
 * Date: 2018/1/16
 * Time: 09:55
 */
namespace admin\helper\api;
use GuzzleHttp\Client;
use lib\register;

/**
 * ERPsso单点登录
 */
class erpsso extends erpbase {
  private $_register;
    private $ssoRequestDomain;
    private $ssoBackDomain; 
    private $ssoBackUrl    = "/index.php";
    private $ssoUrl        = "/admin/login?backUrl=";
    private $ssoTicketUrl  = "/admin/getUserByTicket/";
    private $ssoLogoutUrl = "/admin/logout?backUrl=";

    private $domain = null;
    private $logoutDomain = null;
    private $ticketUrl = null;

    function __construct(register $register, $type = ''){
        $this->register = $register;
        $this->ssoRequestDomain = \lib\register::getInstance('config')->get('apiUrl.sso');
        $this->ssoBackDomain = \lib\register::getInstance('config')->get('apiUrl.ssoBackDomain');
        $this->combineUrl();
        
    }

    /**
     * [setExperimentEnvironment 设置测试环境参数]
     * @param [type] $requestHost [description]
     */
    public function setExperimentEnvironment($requestHost)
    {
        if ($requestHost) {
            $this->ssoBackDomain = $requestHost;
            $this->combineUrl();
        } else {
            return false;
        }
        
    }

   
    private function combineUrl(){
         $this->domain = $this->ssoRequestDomain . 
                            $this->ssoUrl . 
                            $this->ssoBackDomain . 
                            $this->ssoBackUrl;
        $this->ticketUrl = $this->ssoRequestDomain . $this->ssoTicketUrl;
        $this->logoutDomain = $this->ssoRequestDomain . 
                              $this->ssoLogoutUrl . 
                              $this->ssoBackDomain . 
                              $this->ssoBackUrl;
    }


    /**
     * [setIDCEnvironmentServerPort 设置正式环境下的端口号]
     * @param integer $idcEnvironmentServerPort [获取当前访问地址的端口号]
     */
    public function setIDCEnvironmentServerPort($idcEnvironmentServerPort = 80)
    {
        if (strpos($this->ssoBackDomain, ".com:") === false) {
            $this->ssoBackDomain .= ":" . $idcEnvironmentServerPort;
            $this->combineUrl();
        }
       
    }

    /**
     * [getUserByTiket 根据ticket获取用户， 判断用户是否登录]
     * @param  [type] $ticket [获取到客户端传过来的ticket]
     * @return [type]         [通过api获取当前的用户信息]
     */
    public function getUserByTiket($ticket)
    {
        $client = new Client();
        $url = $this->ticketUrl . $ticket;
        $response  = $client->request('GET', $url);
        $statusCode = $response->getStatusCode();
        $contentType = substr($response->getHeaderLine('content-type'), 0, 9);
        $result = []; 
        $ret = false;
        if ($statusCode == 200 && $contentType == "text/html") {
            $body = (string)$response->getBody();
            $resJsonArray = json_decode($body, true);
            if (strtoupper($resJsonArray['code'])== "OK") {
               $result = $resJsonArray['item'];
            }
        } 
        if ($result) {
            $ret = $this->updateOrNewUser($result);
        } 
        unset($resJsonArray);
        unset($result);
        return $ret;
      
    }

    /**
     * [getRequestDomain 获取用户登录的Erp地址]
     * @return [type] [description]
     */
    public function getRequestDomain()
    {
        return $this->domain;
    }

     /**
     * [getRequestLogoutDomain 获取用户退出的Erp地址]
     * @return [type] [description]
     */
    public function getRequestLogoutDomain()
    {
        return $this->logoutDomain;
    }

     /**
     * [giveUpOrNew 如果数据库已经存在就更新， 否则新增一条用户记录]
     * @param  [type] $userInfo [description]
     * 参数：  loginID是唯一的
     * @return [type]           [description]
     */
    private function updateOrNewUser($userInfo)
    {

        $newUser = [];
        if (!empty($userInfo['email'])) {
            $newUser['email']  = $userInfo['email'];
        }
        if (!empty($userInfo['mobile'])) {
            $newUser['mobile']  = $userInfo['mobile'];
        }
        if (!empty($userInfo['deptId'])) {
            $newUser['id_department']  = $userInfo['deptId'];
        }
        if (!empty($userInfo['deptName'])) {
            $newUser['department']  = $userInfo['deptName'];
        }

        if (!empty($userInfo['department'])) {
            $newUser['name_cn']  = $userInfo['deptName'];
        }

        if (!empty($userInfo['managerId'])) {
            $newUser['manager_id']  = $userInfo['managerId'];
        }
        if (!empty($userInfo['lastName'])) {
            $newUser['name_cn']  = $userInfo['lastName'];
        }
        if (!empty($userInfo['companyId'])) {
            $newUser['company_id']  = $userInfo['companyId'];
        }
        $newUser['update_at']  =  date('Y-m-d H:i:s', time());
        
        $map = ['username'=>$userInfo['loginid']];
        $user = $this->register->get('db')->get('oa_users', "*", $map);
        if ($user) {
            $this->register->get('db')->update('oa_users',$newUser, ['AND'=>$map]);
            return array_merge($user, $newUser);
        }

        if (!empty($userInfo['loginid'])) {
            $newUser['username']  = $userInfo['loginid'];
        }

        $newUser['create_at']  =  date('Y-m-d H:i:s', time());
        $newUser['uid']  =  $userInfo['id'];
        $uid = $this->register->get('db')->insert('oa_users',$newUser);
        unset($newUser);
        $user =$this->register->get('db')->get('oa_users', "*", ["uid" => $uid]);
        return $user;
    }
}