<?php
$data = file_get_contents('php://input');
if(empty($data)){
    return jerr('请输入抖音复制的链接后操作');
}
if (preg_match('/v.douyin.com\/(.*?)\//',$data,$match)){
	$url="https://v.douyin.com/".$match[1]."/";
	$html = curlHelper($url);
	$url = $html['detail']['redirect_url'];
	$html = curlHelper($url,null,['user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4044.138 Safari/537.36']);
	if(preg_match('/playAddr: "(.*?)"/',$html['body'],$match)){
	    $url = str_replace('aweme.snssdk.com/aweme/v1/playwm','aweme.snssdk.com/aweme/v1/play',$match[1]);
	    $html = curlHelper($url,null,['user-agent:Aweme/26006 CFNetwork/902.2 Darwin/17.7.0']);
	    return jok('获取无水印视频成功，是否立即打开查看？',str_replace('http://','https://',$html['detail']['redirect_url']));
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
function  jerr ($msg='error',$code=500){
	header("content:application/json;chartset=uft-8");
	echo json_encode (["code"=>$code,"msg"=>$msg]);
	die ;
}
function  curlHelper ($url,$data=null,$header=[],$cookies="",$method='GET'){
	$ch=curl_init();
	curl_setopt($ch,CURLOPT_URL ,$url);
	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER ,false);
	curl_setopt($ch,CURLOPT_SSL_VERIFYHOST ,false);
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