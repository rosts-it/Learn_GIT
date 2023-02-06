<?
session_name('USERID');
session_start();

header ("Content-Type: text/html; charset=utf-8");
define('USE_AJAX', false);
$GLOBALS['ADMIN_MODE'] = false;

$mem_cfg['host'] = 'localhost';
$mem_cfg['port'] = 3306;

define('MEM_PREFIX','shop_');
define('THUMBPATH','thumb/');
define('THUMBQUALITY',100);
define('DEFAULT_PERPAGE',20);
define('DEFAULT_ADM_PERPAGE',50);
define('PAGED',false);
define('PAGED_ADM',false);
define('TITLE','');
define('ADMINTITLE','Панель управления');
define('USERCOPY','');
define('FIRST_ADMINCOPY','©&nbsp;2010&nbsp;<a href="">Создание сайта - ed developers</a>');
define('ADMINCOPY','<noindex>©&nbsp;2010&nbsp;<a href="">Создание сайта - ed developers</a></noindex>');
define('FIRST_ADMINCOPY_EN','©&nbsp;2010&nbsp;<a href="">ed developers</a>');
define('ADMINCOPY_EN','<noindex>©&nbsp;2010&nbsp;<a href="">ed developers</a></noindex>');

// define('ADMINCOPY','');

define('ATPAGE',50);
define('ENTRY','index.php');
define('ADMINENTRY','login.php');
define('ADMINADMIN','adm.php');
define('ADMINADMINTITLE','Администраторы');
define('ADMINMAINTITLE','Главная');
define('ADMINATPAGE',20);

// общие константы сайта
if (!defined('DOMAIN')) define('DOMAIN','http://'.$_SERVER['HTTP_HOST'].'/');
define ('ROOTDIR', $_SERVER['DOCUMENT_ROOT'].'/');



if (file_exists(ROOTDIR.'../local/local.php')) {
    include_once(ROOTDIR.'../local/local.php');
    define ('LOCAL', true);
}

define ('DATATABLE', 'v_data');
define('USERDIR','user/');
define("SEARCHTABLE","v_search");

// Константы путей
define('INC',ROOTDIR.'inc/');
define('IMG','img/');

define('TEMPLATE','templates/');
define('ADMINIMG',IMG.'admin/');
define('ADMINTEMPLATE',TEMPLATE.'admin/');
define('NOTIMG','img/notimg.gif');

if (!defined('DEBUG_MODE')) {
    define('DEBUG_MODE',false); // режим вывода отладочных сообщений
} else {
    if (DEBUG_MODE) {
        include_once('lib/console/config.php');
        include_once('lib/console/main.php');
        new Debug_HackerConsole_Main(true);
    }
}

if (defined('LOCAL')) {
    define ('DEBUG_TEMPLATE', true);
} else {
    define ('DEBUG_TEMPLATE', DEBUG_MODE);
}

// Константы доступа к БД
if (!defined('DBHOST')) define('DBHOST','localhost');
if (!defined('DBNAME')) define('DBNAME','sap2');
if (!defined('DBUSER')) define('DBUSER','sap2'); //
if (!defined('DBPASSWORD')) define('DBPASSWORD','sap2');

// констранты почтовых ящиков
define('SITEEMAIL','job.ed.koleda@gmail.com');
define('ADMINEMAIL','job.ed.koleda@gmail.com');
define('USEREMAIL','job.ed.koleda@gmail.com');

// Отключение вывода отладочных сообщений PHP
if (!DEBUG_MODE) Error_Reporting(E_ALL & ~E_NOTICE);

define('FIELD_DIVER','<!<!<!#DIVIDER#!>!>!>');
define('SUB_FIELD_DIVER','<!#REDIVID#!>');

$cur_data = array();

$block_data = array(
    'left' => '',
    'content' => '',
    'itempic' => '',
    'itempicspase' => '',
    'cfg' => array(
        'left' => true,
    )
);

include_once(INC.'utf.php');
include_once(INC.'cache.php');
include_once(INC.'structs.php');
include_once(INC.'email.php');
include_once(INC.'db.php');
include_once(INC.'url_checker.php');
include_once(INC.'linker.php');
include_once(INC.'freeditor.php');
include_once(INC.'form.php');
include_once(INC.'userfunc.php');
include_once(INC.'functions.php');
include_once(INC.'optimize.php');
include_once(INC.'img_lib.php');
include_once(INC.'twig.php');
include_once(INC.'search.php');
include_once(INC.'cart.php');
include_once(INC.'diler.php');
include_once(INC.'mem.php');
include_once(INC.'ajax.php');
$GLOBALS['MEM'] = new mem($mem_cfg);

if (!$GLOBALS['ADMIN_MODE']) readLnkArr();

//initstructures();

$GLOBALS['out'] = '';


$cur_id = vlRequest('id','first');

// функция вывода ошибок в режиме отладки
function vlDebug($data, $group = 'debug', $color = '#888888') {
    if (!DEBUG_MODE) return;
    call_user_func(array('Debug_HackerConsole_Main', 'out'), $data, $group, $color);
}

// функция вывода переменой в режиме отладки
function vlDebugVar($data, $group = 'debug', $color = '#FF0000') {
    if (!DEBUG_MODE) return;
    call_user_func(array('Debug_HackerConsole_Main', 'out'), $data, $group, $color);
}

// функция вывода массива в режиме отладки
function vlDebugArr($data, $group = 'debug', $color = '#0000FF') {
    if (!DEBUG_MODE) return;
    call_user_func(array('Debug_HackerConsole_Main', 'out'), $data, $group, $color);
}

// обработка выбора основных шаблонов страницы
$templates = Array(
   'default' => 'base.html',
   'first' => 'first.html',
   
   'c_type_21' => 'boss.html',
   
   'c_type_10' => 'news.html',
   'c_type_11' => 'new.html',
   
   'c_type_40' => 'services.html',
   'c_type_41' => 'service.html',
   
   'c_type_51' => 'schedule.html',
   'c_type_52' => 'schedule_time.html',
   
   'error404' => 'error.html',  
);

// обработка выбора основных шаблонов страницы
$admin_templates = Array(
    'default' => 'view.html',
-1 => 'root.html',
-2 => 'view.html',
    'editor' => 'op.html',
    'copy_move' => 'cm.html'
);

// функция выбора шаблона для текущей страницы админ по id
function SelectAdmTemplate() {
    global $admin_templates, $adm_id;
    $id = $adm_id;
    if ($id < 0) {
        if (isset($admin_templates[$id])) return $admin_templates[$id];
        else return $admin_templates['default'];
    } else return $admin_templates[-2];
}
// функция выбора шаблона для текущей страницы по id
function SelectTemplate() {
    global $templates, $cur_id, $cur_data,$print_version;

    unset($a);
    if (!empty($cur_data['self']['c_name']) && ($cur_data['self']['c_name'] == 'podbor')) {
        if (vlRequest('result', '') == 'result') {
            $a=@$templates['podbor_result'];
        } else {
            $a=@$templates[($cur_data['self']['c_name'])];
        }
        if (!empty($a)) {
            return ''.$a;
        }
    }

    unset($a);
    if (!empty($cur_data['self']['c_name'])) {
        $a=@$templates[($cur_data['self']['c_name'])];


        if (!empty($a)) {
            return ''.$a;
        }
    }

     if (!empty($cur_data['self']['record_type'])) {
         $a=@$templates['rec_type_'.($cur_data['self']['record_type'])];


         if (!empty($a)) {
             return ''.$a;
         }
     }

     if (!empty($cur_data['self']['c_type'])) {
         $a=@$templates['c_type_'.($cur_data['self']['c_type'])];


         if (!empty($a)) {
             return ''.$a;
         }
     }

     if (!empty($cur_data['parent']['c_type'])) {
         $a=@$templates['parent_c_type_'.($cur_data['parent']['c_type'])];


         if (!empty($a)) {
             return ''.$a;
         }
     }

    if($print_version)  return ''.$templates['print'];

    if (($cur_id < 0) || (trim($cur_id) == '')) $cur_id='first';
    if (($cur_id < 0) || (trim($cur_id) != '')) {
        if (isset($templates[$cur_id])) return ''.$templates[$cur_id];
        else return ''.$templates['default'];
    } else return ''.$templates['default'];
}




// функция доставания переменной
function vlRequest($name, $default) {
    global $_REQUEST;
    //    vlDebugArr('$_REQUEST',$_REQUEST);
    if (isset($_REQUEST[$name])) {
        //vlDebugVar($name,$_REQUEST[$name]);
        return $_REQUEST[$name];
    } else return $default;
}

// функция проверки наличия параметра в запросе
function vlCheckRequest($name) {
    global $_REQUEST;
    if (isset($_REQUEST[$name])) return true;
    return false;
}

// функция возвращает параметр из массива встретившийся в запросе (для выбора submit-ов)
function vlSelectRequest($arr) {
    global $_REQUEST;
    if (!isset($arr)) return '';
    foreach ($arr as $n => $name) {
        if (isset($_REQUEST[$name])) return $name;
    }
    return '';
}

// параметры запроса
$cur_id = vlRequest('id','first');

$print_version = false;

$pofig_na_start_page = 0;
if(isset($_SESSION['swfaddress'])) {


    $pofig_na_start_page = false;
    list($cuz,) = explode('?',$_SESSION['swfaddress']);
    $cuz2 = explode('/',$cuz);
    req_id_check($cuz2);


    $pofig_na_start_page = 1;
}elseif (!isset($_REQUEST['id']) && !isset($_REQUEST['swfaddress'])) {
    if (isset($_SERVER['REQUEST_URI']) &&
(strlen($_SERVER['REQUEST_URI'])>0)) {
        list($cuz,) = explode('?',$_SERVER['REQUEST_URI']);
        $cuz2 = explode('/',$cuz);
         if (count($cuz2) > 0) {
             $aq = $cuz2[count($cuz2)-1];
             $aw = preg_match('#(.php|.html|.htm)$#si', $aq);
             //echo $aw;
             if ($aw) {
                 array_pop($cuz2);
             }
         }

        switch ($cuz2[count($cuz2)-1]) {
            case 'print':
                $print_version = true;
                unset($cuz2[count($cuz2)-1]);
                break;
        }

        req_id_check($cuz2);
    }
} elseif(isset($_REQUEST['swfaddress'])) {
    list($cuz, $ras) = explode('?',$_REQUEST['swfaddress']);
    $cuz2 = explode('/',$cuz);
    req_id_check($cuz2);
    if(trim($ras)!= '') {
        $arr = explode('&',$ras);
        foreach ($arr as $n=> $row) {
            $_REQUEST[$n] = $row;

        }
    }
}


// функция чтения переменной из сессии



function vlGetSessionVar($name) {
    if (isset($_SESSION[$name])) return $_SESSION[$name];
    else {
        vlDebug("Не удается вынять переменную ".$name." из сессии",'vlGetSessionVar');
        return '';
    }
}

// функция чтения файла в переменную
function vlReadFile($name) {
    if (!file_exists($name)) {
        vlDebug("Ошибка чтения файла - файл отсутствует: ".$name,'vlReadFile','maroon');
        return;
    }
    return implode ('', file ($name));
}

$repace_mas = array (
    '&gt;&gt;' => '>>',
    '&lt;&lt;&amp;' => '<<&',
    '&lt;&lt;' => '<<',
    'charset=windows-1251"' => 'charset=utf-8"',
    'charset=windows-1251\'' => 'charset=utf-8\'',
//    '\'<!--.*?-->\'si' => '',
);

function pre_replace(&$text) {
    global $repace_mas;
    $reg = array();
    $rep = array();
    foreach ($repace_mas as $from => $to) {
        $reg[] = "/$from/";
        $rep[] = $to;
    }
    $res = preg_replace($reg,$rep,$text);
    return $res;
}


// функция вставки форм
function wizi_form_insert($matches) {
    global $tree, $cur_data;
    if (!isset($GLOBALS['all_site_data']['forms']) && isset($tree)) {
        $GLOBALS['all_site_data']['forms'] = $tree->expandChilds('forms', 'c_desc_main, c_desc, c_email, c_ankor');

    }
    if (!isset($GLOBALS['all_site_data']['forms'])) return '';
    foreach($GLOBALS['all_site_data']['forms'] as $n=>&$row) {
        if ($row['c_name'] == $matches[1]) {
            $cur_data['vars']['user_form'] = wddx_deserialize3($row['c_desc']);
            $row['c_desc'] = $cur_data['vars']['user_form'];
            $cur_data['vars']['user_email'] = $row['c_email'];
            form_handler($row['c_desc_main'], $row);
            return $row['c_desc_main'];
            break;
        }
    }
    return '';
}

function tag_replacer($text) {
    // замена форм
    return preg_replace_callback("#<pitrixform[^>]+form=\"([^\"]+)\"[^<]*</pitrixform>#", "wizi_form_insert", $text);


}


// функция подстановки в шаблон
function vlExtractTemplate($text, $data, $echo = true, $cache = false) {
    global $cur_id, $vvedenoverno, $cur_data, $types, $ntypes, $tree, $workflow;
    if (!$text) {
        vlDebug('Шаблон пустой','vlExtractTemplate','navy');
        return;
    }
    $res = pre_replace($text);
    $status = true;
    while ($status) {
        $res = vlDataExtractor($res, $data, $status);

    }
    if ($echo) {

        if ($cache) {

    header('Cache-Control: no-cache,no-store,max-age=0,must-revalidate');
    header("Last-Modified: ".date("D, d M Y H:i:s")." GMT");
    header("Expires: ".date("D, d M Y H:i:s")." GMT");

            if (cacheBuildHeader($res)) {
                vlDebugVar("Last-Modified: ".date("D, d M Y G:i:s")." GMT", 'HTTP/1.1 200 OK', '#ff0000');
                echo iu(($res));
            } else {
                vlDebugVar("Last-Modified: ".date("D, d M Y G:i:s")." GMT", 'HTTP/1.1 304 Not Modified', '#ff0000');
            }
        } else {

            echo $res;
        }
    } else {

        return $res;
    }
}

function vlDataExtractor($text, $data, &$status) {
    global $cur_id, $vvedenoverno, $cur_data, $types, $ntypes, $tree, $workflow, $tpl_site, $block_data;
    $f = $text;
    $res = '';
    $status = false;
    while (true) {
        $p1 = strpos($f, '<<');
        if ($p1===false) break;
        $text = substr($f,0,$p1);
        $res .= $text;
        $f = substr($f,$p1+2);
        $p2 = strpos($f, '>>');
        if (!$p2) break;
        $name = substr($f,0,$p2);
        $name = trim($name);
        switch ($name[0]) {
            case '`':    // <<`NAME>> вставляет файл шаблона из папки templates/, указывать путь и имя;
                        // <<`?условие|шаблон при да|шаблон при нет>>
                        // для подгрузки разных подшаблонов по условию пишем:
                        // <<`?$data['show_menu']|`template_with_menu.html|`simple_template.html>>
                $name = substr($name,1);

                $status = true;

                switch ($name[0]) {
                    case '?':
                        $name2 = substr($name,1);
                        $qd = explode('|',$name2, 3);
                        eval('$val = '.$qd[0].';');
                        if ($val) {
                            if (trim($qd[1]!='')) {
                                $res .= vlExtractTemplate('<<'.$qd[1].'>>', $data, false);
                            }
                        } else {
                            if (trim($qd[2]!='')) {
                                $res .= vlExtractTemplate('<<'.$qd[2].'>>', $data, false);
                            }
                        }
                        break;
                    default:
                        $ti = ROOTDIR.TEMPLATE.$name;
                        $fi = vlReadFile($ti);

                        $res .= vlExtractTemplate($fi, $cur_data, false);
                        break;
                }
                break;

            case '?':
                $status = true;

                $name = substr($name,1);
                $qd = explode('=',$name,2);
                $qd_val = explode(':',$qd[1], 2);
                $qd_tf = explode('|',$qd_val[1], 2);
                switch($qd[0]) {
                    // <<?c_name=_name_:if_true|if_false>>  проверяет текущий c_name
                    case 'c_name':
                    case 'cn':
                    case 'n':
                        if ($cur_data['self']['c_name'] == $qd_val[0]) $res .= $qd_tf[0];
                        else $res .= $qd_tf[1];
                        break;
                    // <<?c_type=_type_code_:if_true|if_false>>  проверяет текущий c_name
                    case 'c_type':
                    case 'ct':
                    case 't':
                        if ($cur_data['self']['c_type'] == $qd_val[0]) $res .= $qd_tf[0];
                        else $res .= $qd_tf[1];
                        break;
                    // <<?record_type=_type_name_or_type_code_:if_true|if_false>>  проверяет текущий c_name
                    case 'record_type':
                    case 'r':
                    case 'rt':
                        if (($cur_data['self']['record_type'] == $qd_val[0])
                            || ($cur_data['self']['record_type'] == $ntypes[$qd_val[0]])) $res .= $qd_tf[0];
                        else $res .= $qd_tf[1];
                        break;
                    // <<?inPath=_name_:if_true|if_false>>  проверяет текущий c_name
                    case 'inPath':
                    case 'inpath':
                    case 'in_Path':
                    case 'in_path':
                    case 'ip':
                    case 'p':
                        $res .= inPath($qd_val[0],$qd_tf[0], $qd_tf[1]);
                        break;
                }
                break;
            case '$':    // <<$NAME>>  отрабатывает как echo $NAME;
                $status = true;

                $name = substr($name,1);
                if (substr($name,0,4) != 'data') {
                    $sca = '$res .= $GLOBALS["'.$name.'"];';
//                    echo $sca;
                    eval($sca);
                } else eval('$res .= $'.$name.';');
                break;
            case '#':    // <<#NAME>>  отрабатывает как echo NAME;
                $status = true;

                $name = substr($name,1);
                eval('$res .= '.$name.';');
                break;
            case '&':    // <<&NAME()>>  отрабатывает как NAME();
                $status = true;

                $name = substr($name,1);
                //vlDebugVar('',$name);
                eval('$res .= '.$name.';');
                break;
            case '%':    // <<%NAME>>  отрабатывает как Шаблон INC.NAME;
                $status = true;

                $name = INC.substr($name,1);
                $name = vlReadFile($name);
                $res .= vlExtractTemplate($name, $data, false);
                break;
            case '*':    // значение переменной из БД по имени
                $status = true;

                $name = substr($name,1);
                $res .= vlGetDBVar($name);
                break;
            case ';':    // значение переменной из БД по имени как шаблон
                $status = true;

                $name = substr($name,1);
                $name = vlGetDBVar($name);
                $res .= vlExtractTemplate($name, $data,false);
                break;
            case '^':    // значение переменной из сессии
                $status = true;

                $name = substr($name,1);
                $res .= vlGetSessionVar($name);
                break;
            case '@':    // значение переменной из сессии как шаблон
                $status = true;

                $name = substr($name,1);
                $name = vlGetSessionVar($name);
                $res .= vlExtractTemplate($name, $data, false);
                break;
            case '+':    // формирование адреса страницы по ключу
                $status = true;

                $name = substr($name,1);
                $res .= lnk($name);
                break;
            case '_':    // значение поля из текущей записи
                $status = true;

                $name = substr($name,1);

                if (isset($cur_data['self'][$name])) {
                    //vlDebugArr($cur_data['self'][$name]);
                    $res .= $cur_data['self'][$name];
                }
                break;
            // вызов функций из WORKFLOW
            case 'w':
            case 'W':
                $status = true;

                $name = substr($name,2);
                eval('$res .= $workflow->'.$name.';');
                break;
            case '!':
                $status = true;

                $name = substr($name,1);
                $res .= $name;
                break;
            case '~':
                $status = true;

                $name = substr($name,1);
                // print print_r($data['childs'], true);
            //    $res .= '$res .= $OBJECTS['.$data['childs'][$name].']->display();'.$name;
                eval('$res .= obj_display($data[\'childs\']['.$name.']);');

                break;

                // запрос из БД в переменную + include или функция  ?????

            default :     // << NAME >>   отрабатывает как include (INC.'NAME.php');
                //                vlDebugArr('', $data);
                $filename = INC.trim($name).'.php';
                if (!file_exists($filename)) {vlDebug('Файл '.$filename.' не найден','ExtractTemplate'); exit;}
                else include($filename);
                break;
        }
        $f = substr($f,$p2+2);
    }
    $res .= $f;
    //vlDebugVar('$res',$res);
    return tag_replacer($res);
}

function getVL($var) {
    global $vl;
    if (isset($vl[$var])) {
        return $vl[$var];
    }
}

function vlGetDataByTemplate($id) {
    global $structures, $types;
    $data = vlGetData($id);
    if (!isset($data)) {
        return vlGetDBVar('id_error');
    }
    $type = $data['record_type'];
    if (!isset($structures[$types[$type]])) {
        return vlGetDBVar('id_error');
    }
    $str = $structures[$types[$type]];
    $tm = $str['self_template'];
    //    vlDebugVar('',$tm);
    return vlExtractTemplate($tm,$data, false);
}

function BuildPage($display = true) {
    global $cur_id, $cur_data, $_SERVER, $structures, $types;

    header('Cache-Control: no-cache,no-store,max-age=0,must-revalidate');
    header("Last-Modified: ".date("D, d M Y G:i:s")." GMT");
    header("Expires: ".date("D, d M Y G:i:s")." GMT");






    $cur_id  = vlCheckID(vlCheckID($cur_id));
    if (($cur_id >= 0) || (trim($cur_id) != '')) {
        $cur_id = goinfunk($cur_id);
        $cur_data = vlGetItem($cur_id,false,'c_user_sort ASC, c_price ASC, c_date DESC, c_caption ASC', false, false, 'c_caption2, show_in_map');

    } else {
        $cur_data = array();
    }



    if (!isset($cur_data['self']['id'])) {
        $cur_id = 'error404';
        $cur_data = vlGetItem($cur_id,false,' c_user_sort ASC, c_date DESC, c_caption ASC', true, false, 'c_caption2, show_in_map');
    }  else {
         if (!defined('LOCAL')) checkURL();  // эта строка проверяет правильность текущего адреса
     }


    if ($cur_id == 'error404') {
        header("HTTP/1.0 404 Not Found");
    }

    if (isset($cur_data['self']['record_type'])) {
        $tru = $types[$cur_data['self']['record_type']];
        if (isset($structures[$tru]['template_file'])) {
            $fit = $structures[$tru]['template_file'];
            if ((trim($fit)!='') && (file_exists(ROOTDIR.TEMPLATE.$fit))) {
                $t = TEMPLATE.$fit;
            }
        }
    }
        vlCart::init('c_price, c_price2'); // обработка корзины

        new vlDilers(); // обработка диллеров


    echo vlTwig(SelectTemplate());
    writeLnkArr();
    $GLOBALS['MEM']->storeKeys();

}

?>
