<?php
error_reporting(0);
$data = file_get_contents('php://input');
if(empty($data)){
    return jerr('请输入抖音复制的链接后操作');
}
if (preg_match('/v.douyin.com\/(.*?)\//',$data,$match)){
	$url="https://v.douyin.com/".$match[1]."/";
	$html = curlHelper($url);
	$url = $html['detail']['redirect_url'];
	if(preg_match('/video\/(.*?)\//',$url,$match_vid)){
	    $video_id = $match_vid[1];
    	$html = curlHelper("https://www.douyin.com/web/api/v2/aweme/iteminfo/?item_ids=".$video_id,null,['']);
    	$videoObj = json_decode($html['body'],true);
    	if($videoObj['status_code']==0){
    	    $videoUri = $videoObj['item_list'][0]['video']['play_addr']['url_list'][0];
    	    if(!$videoUri){
	            return jerr('解析JSON数据出现问题，获取失败');
    	    }
			$videoUri = str_replace('aweme.snssdk.com/aweme/v1/playwm','aweme.snssdk.com/aweme/v1/play',$videoUri);

			$videlResultUrl = false;
			for($i=0;$i<20;$i++){
				//最多尝试20次获取
				$html = curlHelper($videoUri);
				if(!empty($html['body'])){
					$html = $html['body'];
					if(preg_match('/<a href="(.*?)">/',$html,$match_url)){
						$videlResultUrl = $match_url[1];
					}
					break;
				}
			}
			if(!$videlResultUrl){
				return jerr('请复制到浏览器打开,如果白屏,请多次刷新尝试!',301,$videoUri);
			}
        	return jok('无水印视频获取成功!',$videlResultUrl);
    	}else{
    	    return jerr('没有查找到视频地址，请查看该抖音是否公开');
    	}
	}else{
	    return jerr('没有查找到视频地址，请查看该抖音是否公开');
	}
}else {
	return jerr ('你输入的链接有误，没有识别到抖音链接');
}
/**
 * 输出正常JSON
@param string 提示信息
@param array  输出数据
@return json
 */
function  jok ($msg='success',$data=null){
	header("content:application/json;chartset=uft-8");
	if ($data){
		echo json_encode (["code"=>200,"msg"=>$msg,'data'=>$data]);
	}else {
		echo json_encode (["code"=>200,"msg"=>$msg]);
	}
	die ;
}
/**
 * 输出错误JSON
@param string 错误信息
@param int 错误代码
@return json
 */
function  jerr ($msg='error',$code=500,$data=false){
	header("content:application/json;chartset=uft-8");
	echo json_encode (["code"=>$code,"msg"=>$msg,"data"=>$data??[]]);
	die ;
}
function  curlHelper ($url,$data=null,$header=[],$cookies="",$method='GET'){
	$ch=curl_init();
	curl_setopt($ch,CURLOPT_URL ,$url);
	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER ,false);
	curl_setopt($ch,CURLOPT_SSL_VERIFYHOST ,false);
	$header[] = 'user-agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 11_1_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.141 Safari/537.36';
	curl_setopt($ch,CURLOPT_HTTPHEADER ,$header);
	curl_setopt($ch,CURLOPT_COOKIE ,$cookies);
	switch ($method){
		case  "GET":
			curl_setopt($ch,CURLOPT_HTTPGET ,true);
			break ;
		case  "POST":
			curl_setopt($ch,CURLOPT_POST ,true);
			curl_setopt($ch,CURLOPT_POSTFIELDS ,$data);
			break ;
		case  "PUT":
			curl_setopt($ch,CURLOPT_CUSTOMREQUEST ,"PUT");
			curl_setopt($ch,CURLOPT_POSTFIELDS ,$data);
			break ;
		case  "DELETE":
			curl_setopt($ch,CURLOPT_CUSTOMREQUEST ,"DELETE");
			curl_setopt($ch,CURLOPT_POSTFIELDS ,$data);
			break ;
		case  "PATCH":
			curl_setopt($ch,CURLOPT_CUSTOMREQUEST ,"PATCH");
			curl_setopt($ch,CURLOPT_POSTFIELDS ,$data);
			break ;
		case  "TRACE":
			curl_setopt($ch,CURLOPT_CUSTOMREQUEST ,"TRACE");
			curl_setopt($ch,CURLOPT_POSTFIELDS ,$data);
			break ;
		case  "OPTIONS":
			curl_setopt($ch,CURLOPT_CUSTOMREQUEST ,"OPTIONS");
			curl_setopt($ch,CURLOPT_POSTFIELDS ,$data);
			break ;
		case  "HEAD":
			curl_setopt($ch,CURLOPT_CUSTOMREQUEST ,"HEAD");
			curl_setopt($ch,CURLOPT_POSTFIELDS ,$data);
			break ;
		default :
	}
	curl_setopt($ch,CURLOPT_RETURNTRANSFER ,1);
	curl_setopt($ch,CURLOPT_HEADER ,1);
	$response=curl_exec($ch);
	$output=[];
	$headerSize=curl_getinfo($ch,CURLINFO_HEADER_SIZE );
	// 根据头大小去获取头信息内容
	$output['header']=substr($response,0,$headerSize);
	$output['body']=substr($response,$headerSize,strlen($response)-$headerSize);
	$output['detail']=curl_getinfo($ch);
	curl_close($ch);
	return $output;
}