<?php
class ubbParser extends XUBBP {
protected $parse=array(

/*
* 一次性匹配标记
* 
* 如果标记可以一次性匹配，
* 不需要分为开始标记和结束标记分别匹配，
* 则在这一段定义（加在这一段末尾）。
* 
* 注意：不要定义在code规则的前面，
* 因为[code][/code]标记里的内容（代码块）不应该进行任何UBB解析。
*/
/*code 代码高亮*/
    '!^(.*)\[code(?:=(.*?))?\](.*?)\[/code\](.*)$!is' => array(array(1,4), 'code', array(2,3)),
/*link 链接*/
    '!^(.*)\[url(?:=(.*?))?\](.*?)\[/url\](.*)$!is' => array(array(1,4), 'link', array('url',2,3)),
    '!^(.*)《(链接|外链|锚)：(.*?)》(.*)$!is' => array(array(1,4), 'link', array(2,3)),
/*img 图片*/
    '!^(.*)\[img(?:=(.*?))?\](.*?)\[/img\](.*)$!is' => array(array(1,4), 'img', array('img',2,3)),
    '!^(.*)《(图片|缩略图)：(.*?)》(.*)$!is' => array(array(1,4), 'img', array(2,3)),
/*copyright 版权*/
    '!^(.*)《版权：(.*?)》(.*)$!is' => array(array(1,3), 'copyright', array(2)),
/*battlenet 战网*/
    '!^(.*)《战网：(.*?)》(.*)$!is' => array(array(1,3), 'battlenet', array(2)),
/*newline 换行*/
    '!^(.*)\[([bh]r)\](.*)$!is' => array(array(1,3), 'newline', array(2)),
    '!^(.*)(///|＜＜＜|＞＞＞)(.*)$!is' => array(array(1,3), 'newline', array(2)),
/*time 时间*/
    '!^(.*)\[time(?:=(.*?))?\](.*)$!is' => array(array(1,3), 'time', array(2)),

/*
* 开始标记
* 
* 这一段应该只包括开始标记，
* 结束标记不应定义在这一段，
* 否则会出现代码嵌套错误。
*/
/*layoutStart 布局开始*/
    '!^(.*)\[(b|i|u|center|left|right)\](.*)$!is' => array(array(1,3), 'layoutStart', array(2)),
/*style 样式开始*/
    '!^(.*)\[(color|div|span)=(.*?)\](.*)$!is' => array(array(1,4), 'styleStart', array(2,3)),
/*
* 结束标记
* 
* 结束标记应该以与开始标记相反的顺序出现，
* 就像[b][i][/i][/b]一样排列。
* 当然这不是强制的，只是这样排比较美观。
* 
* 这一段应该只有结束标记，
* 开始标记不要放在这里，
* 否则会出现嵌套错误。
*/
/*style 样式结束*/
    '!^(.*?)\[/(color|div|span)\](.*)$!is' => array(array(1,3), 'styleEnd', array(2)),
/*layout 布局结束*/
    '!^(.*?)\[/(b|i|u|center|left|right)\](.*)$!is' => array(array(1,3), 'layoutEnd', array(2)),

/*
* 易误匹配的标记
* 
* 这里的标记最后匹配，为了防止误匹配。
* 可能会影响其他标记正常匹配的标记放在这里。
*/
/*urltxt 文本链接*/
    '!^(.*)((?:https?|ftps?|rtsp)\://[a-zA-Z0-9\.\,\?\!\(\)\@\/\:\_\;\+\&\%\*\=\~\^\#\-]+)(.*)$!is' => array(array(1,3), 'urltxt', array(2)),
    '!^(.*)([a-zA-Z0-9._-]+\.(?:asia|mobi|name|com|net|org|xxx|cc|cn|hk|me|tk|tv|uk)(?:/[a-zA-Z0-9\.\,\?\!\(\)\@\/\:\_\;\+\&\%\*\=\~\^\#\-]+)?)(.*)$!is' => array(array(1,3), 'urltxt', array(2)),
/*mailtxt 文本电子邮件地址*/
    '!^(.*)((?:mailto:)?[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.[a-zA-Z]{2,4})(.*)$!is' => array(array(1,3), 'mailtxt', array(2)),
/*at @消息*/
    '!^(.*?)[@＠][@＠#＃a-zA-Z0-9_\x{4e00}-\x{9fa5}]+(.*)$!uis' => array(array(1,3), 'at', array(2)),
/*face 表情*/
    '!^(.*)\{(ok|[\x{4e00}-\x{9fa5}]{1,2})\}(.*)$!uis' => array(array(1,3), 'face', array(2)),
    '!^(.*)《表情(?:：|:)(ok|[\x{4e00}-\x{9fa5}]{1,2})》(.*)$!uis' => array(array(1,3), 'face', array(2)),
);
  
/*link  链接*/
public function link($type,$var,$var2='') {
    if($type=='链接' || $type=='外链') {
        $arr=explode('，',$var);
        $url=$arr[0];
        $title=$arr[1];
        $type = $type=='链接' ? 'urlzh' : 'urlout';
    } else {
        $type='url';
        if($var=='') {
            $url=$var2;
            $title='';
        } else {
            $url=$var;
            $title=$var2;
        }
    }
    return array(array(
        'type'=>$type,
        'url'=>$url,
        'title'=>$title
    ));
}

/*img 图片*/
public function img($type,$var,$var2='') {
    if($type=='缩略图') {
        $var=explode('，',$var);
        $opt=$var[0]; 
        $url=$var[1];
        preg_match_all('![0-9]+!',$opt,$opt);
        return array(array(
            'type' => 'thumb',
            'src' => $url,
            'w' => $opt[0][0],
            'h' => $opt[0][1]
        ));
    } else {
        if($type=='图片') {
            $var=explode('，',$var);
            $src=$var[0];
            $alt=$var[1];
        } elseif($var=='') {
            $src=$var2;
            $alt='';
        } else {
            $src=$var;
            $alt=$var2;
        }
        return array(array(
            'type' => $type=='img' ? 'img' : 'imgzh',
            'src' => $src,
            'alt' => $alt
        ));
    }
}
  
public function code($lang, $data) {
    $lang = trim($lang);
    if ($lang == '') $lang = 'php';
    return array(array(
        'type' => 'code',
        'lang' => $lang,
        'data' => $data,
    ));
}
/*class end*/
}