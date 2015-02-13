<? 
namespace Itbiz\Parser;
class ParserActions{
function OnBeforePrologHandler(){
    global $USER_FIELD_MANAGER;
    if (isset($_REQUEST['action_button']) && !isset($_REQUEST['action'])) {
			$_REQUEST['action'] = $_REQUEST['action_button'];
		}
		if (!isset($_REQUEST['action'])) {
			return;
		}
    $BID = (isset($_REQUEST['ID']) ? (int)$_REQUEST['ID'] : 0);
    if ($_REQUEST['action']=='parsing_in_list' && check_bitrix_sessid() &&
			\CModule::IncludeModule('iblock')) {
        ParserActions::doParsing($BID,$_REQUEST['IBLOCK_ID']);
    }
}
    private function doParsing($sectID,$ibID){
        $sect=new \CIblockSection;
        $sData=$sect->getList(array("SORT"=>"ASC"),array("ID"=>$sectID,"IBLOCK_ID"=>$ibID),false,array("ID","UF_PROV_LINK"))->fetch();
        $html=ParserActions::getHTML('ANTips','251348',$sData['UF_PROV_LINK'][0],'enter=1');
        PR(1);
        $data=ParserActions::getData($html);
        PR($data);
    }

    private function getHTML($login,$pass,$parentLink,$params=''){
        $ua = 'User-Agent: Mozilla/4.0 (compatible; MSIE 5.01; Widows NT)'; // ну, или что больше нравится 
 
$ch=curl_init ("http://wht.ru/"); 
curl_setopt ($ch, CURLOPT_HEADER, 1); // чтобы выводил заголовки 
curl_setopt ($ch, CURLOPT_NOBODY, 1); // чтобы не выводил саму страницу (она пока не нужна) 
curl_setopt($ch, CURLOPT_USERAGENT, $ua); // чтобы сказать что мы броузер, а не так себе... 
 
ob_start(); // первый раз ничего не нужно выводить (можно для отладки скрипта убрать) 
 
curl_exec ($ch); 
curl_close ($ch); 
 
$headers = explode("\n", ob_get_contents()); // так можно получить массив всех заголовков от сервера 
ob_end_clean(); 
 
for ($i=0; $i<sizeof($headers); $i++) 
{ 
  if (strpos($headers[$i], 'Set-Cookie:') !== FALSE) 
  { 
    list($field, $cookie[]) = explode(' ', $headers[$i]); // так можно получить куку (куки) 
  } 
} 
 
$ref = 'http://wht.ru/'; // обычно адрес на котором сама форма 
$ch=curl_init ("http://wht.ru/"); 
 
$header  = array
(
'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
'Accept-Encoding: gzip, deflate', // указиваем серверу, что ответ надо сжимать
'Content-type: application/x-www-form-urlencoded'
);
curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
curl_setopt($ch, CURLOPT_USERAGENT, $ua); 
curl_setopt($ch, CURLOPT_REFERER, $ref); // некоторые проверяют 
curl_setopt($ch, CURLOPT_POST, 1); // метод POST 
curl_setopt($ch, CURLOPT_POSTFIELDS, 'login='.$login.'&password='.$pass.($params!=""?"&".$params:"")); 
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // это может понадобиться если будет редирект 
 
for ($i=0; $i<sizeof($cookie); $i++) 
{ 
  curl_setopt($ch, CURLOPT_COOKIE, $cookie[$i]); // шлём cookie 
} 

 //далее идём на нужную нам страницу
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, '1');
 curl_setopt($ch, CURLOPT_URL,$parentLink);
       $html = curl_exec($ch);
       $html=iconv('cp1251', 'utf8', $html);
       return $html;
    }
    private function getData($html){
        $str1="<!-- tovars -->";
        $str2="<!-- end of tovars -->";
        $start=strripos($html,$str1);
        $end=strripos($html, $str2);
        $html=substr($html,$start+strlen($str1),$end-$start-strlen($str1));
        $html=strip_tags($html,"<a><div>");
        $arData=array();
        $arUrls=array();
        $cmh = curl_multi_init();
        $tasks = array();
        /* Получаем данные*/
        preg_match_all('/class="headerinc"\><a.href="\/shop\/(.*?)<\/a>/',$html, $matches);
        
        foreach($matches[1] as $key=>$data){
            $tmp = trim($data);
            $nameCat = preg_replace('/^(.*?)">/is', "", $tmp);
            $urlCat = preg_replace('/">(.*?)$/is', "", $tmp );
            $arData[$key]=array("URL"=>"/shop/".$urlCat,"NAME"=>$nameCat);
            $arUrls[$key]="wht.ru/shop/".$urlCat;
        }
        /* Получаем цены*/
        preg_match_all('/class="saletxt"\>(.*?)<\/div\>/is',$html, $matches);
        foreach($matches[1] as $key=>$data){
             $tmp = trim($data);
             preg_match_all('/(\d+)\-\sRUB/',$data,$arPrice);
             if(count($arPrice[1])==2){
                $arData[$key]['PRICE']=$arPrice[1][0];
                $arData[$key]['PRICE2']=$arPrice[1][1];
                $url="wht.ru".$arData[$key]['URL'];
                 // инициализируем отдельное соединение (поток)
                $ch = curl_init('http://'.$url);
                // если будет редирект - перейти по нему
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                // возвращать результат
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                // не возвращать http-заголовок
                curl_setopt($ch, CURLOPT_HEADER, 0);
                // таймаут соединения
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                // таймаут ожидания
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                // добавляем дескриптор потока в массив заданий
                $tasks[$url] = $ch;
                // добавляем дескриптор потока в мультикурл
                curl_multi_add_handle($cmh, $ch);
             }else{
                 unset($arData[$key]);
                 unset($arUrls[$key]);
             }
        }
        // количество активных потоков
        $active = null;
        // запускаем выполнение потоков
        do {
            $mrc = curl_multi_exec($cmh, $active);
        }
        while ($mrc == CURLM_CALL_MULTI_PERFORM);

        // выполняем, пока есть активные потоки
        while ($active && ($mrc == CURLM_OK)) {
            // если какой-либо поток готов к действиям
            if (curl_multi_select($cmh) != -1) {
                // ждем, пока что-нибудь изменится
                do {
                    $mrc = curl_multi_exec($cmh, $active);
                    // получаем информацию о потоке
                    $info = curl_multi_info_read($cmh);
                    // если поток завершился
                    if ($info['msg'] == CURLMSG_DONE) {
                        $ch = $info['handle'];
                        // ищем урл страницы по дескриптору потока в массиве заданий
                        $url = array_search($ch, $tasks);
                        // забираем содержимое
                        $content=iconv('cp1251', 'utf8', curl_multi_getcontent($ch));
                        $str1="<!-- central part -->";
                        $str2="<!-- end of central part -->";
                        $start=strripos($content,$str1);
                        $end=strripos($content, $str2);
                        $content=substr($content,$start+strlen($str1),$end-$start-strlen($str1));
                        $content=strip_tags($content,"<h2><img>");
                        preg_match('/class="headertovar"(.*?)\>(.+)<\/h2\>/si',$content,$matches);
                        $head=$matches[2];  
                        preg_match('/<\/h2>(.*?)<img src="(.*?)"\s(.*?)>/si',$content,$matches);
                        $src=$matches[2];
                        $key=array_search($url,$arUrls);  
                        if($key>0){
                            $arData[$key]['URL']="http://".$url;
                            $arData[$key]['NAME']=$head;
                            $arData[$key]['IMAGE']="http://wht.ru".$src;
                        }
                        //$tasks[$url] =$content;
                        // удаляем поток из мультикурла
                        curl_multi_remove_handle($cmh, $ch);
                        // закрываем отдельное соединение (поток)
                        curl_close($ch);
                    }
                }
                while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
        }

        // закрываем мультикурл
        curl_multi_close($cmh);
        return $arData;
    }
}
?>