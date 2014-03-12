<?php
/**
 * 微信验证Action
 */
class Sher_App_Action_Wechat extends Sher_App_Action_Authorize {
	
	public $stash = array(
		'signature'=>'',
		'timestamp'=>'',
		'nonce'=>'',
		'echostr'=>'',
		'page'=>1,
		'ref'=>'',
	);
	
	// 一个月时间
	protected $month =  2592000;
	
	protected $page_tab = 'page_index';
	protected $page_html = 'page/index.html';
	
	protected $exclude_method_list = array('execute','verify');

	/**
	 * 入口
	 */
	public function execute() {
		return $this->verify();
	}
	
    /**
     * 验证Token
	 *
     * @return string
     */
    public function verify() {
        $echoStr = $this->stash["echostr"];
		
        //valid signature , option
        if($this->checkSignature()){
			return $this->to_raw($echoStr);
        }
		
		return $this->to_raw('Erorr: not match!');
    }

    public function responseMsg() {
		//get post data, May be due to the different environments
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

      	//extract post data
		if (!empty($postStr)){
                
              	$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                $fromUsername = $postObj->FromUserName;
                $toUsername = $postObj->ToUserName;
                $keyword = trim($postObj->Content);
                $time = time();
                $textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Content><![CDATA[%s]]></Content>
							<FuncFlag>0</FuncFlag>
							</xml>";             
				if(!empty( $keyword ))
                {
              		$msgType = "text";
                	$contentStr = "Welcome to wechat world!";
                	$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                	echo $resultStr;
                }else{
                	echo "Input something...";
                }

        }else {
        	echo "";
        	exit;
        }
    }
		
	private function checkSignature() {		
		$signature = $this->stash['signature'];
		$timestamp = $this->stash['timestamp'];
		$nonce = $this->stash['nonce'];
        		
		$token = Doggy_Config::get('app.wechat.token');
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		
		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}
	
}
?>