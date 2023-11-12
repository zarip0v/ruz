<?php

require_once realpath(__DIR__ . "/vendor/autoload.php");

$env = Dotenv\Dotenv::createImmutable(__DIR__);
$env->load();

function checkToken($token): bool
{
    try {
        // Запрашиваем ключи у серверва авторизации и проверяем токен
        $jwks = json_decode(file_get_contents($_ENV['AUTH_KEYS_URL']), true);
        $decoded = Firebase\JWT\JWT::decode($token, Firebase\JWT\JWK::parseKeySet($jwks));
        // Дополнительная проверка: в домене ли ВШЭ аккаунт
        if (strpos($decoded->email, "@edu.hse.ru") !== false || strpos($decoded->email, "@hse.ru") !== false) {
            return true;
        }
    } catch (Exception $e) {
        return false;
    }
    return false;
}

// Расшифровываем URL
$url = explode("/", $_SERVER["REQUEST_URI"]);

// Если нет токена в Cookie или он истек, редиректим на авторизацию
if (!isset($_COOKIE["token"]) || !checkToken($_COOKIE["token"])) {
    header("Location: " . $_ENV["AUTH_LOGIN_URL"]);
    die();
}

// Если мы вернулись из авторизации, проверить токен и сохранить
if (isset($url[1]) && $url[1] === "redirect") {
    if (checkToken($_POST["access_token"])) {
        setcookie("token", $_POST["access_token"], 0, '/');
      	header("Location: /");
    }
    else {
        http_response_code(403);
        echo "Доступ запрещён.";
    }
    die();
}

// Обрабатываем запросы API
if (isset($url[1]) && $url[1] === "api") {

    // Доступ к /api/dictionary излишен в контексте расписания
    if (isset($url[2]) && $url[2] === "dictionary") {
        http_response_code(403);
        echo "Ошибка доступа";
        die();
    }

    // Отправляем запрос в РУЗ
    $url = $_ENV["RUZ_URL"].$_SERVER["REQUEST_URI"];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    // Используется ли прокси
    if ($_ENV["PROXY_USED"]) {
        curl_setopt($ch, CURLOPT_PROXY, $_ENV["PROXY_SERVER"]);
        curl_setopt($ch, CURLOPT_PROXYUSERPWD, $_ENV["PROXY_CREDENTIALS"]);
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    $response = curl_exec($ch);
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header = substr($response, 0, $header_size);
    $body = substr($response, $header_size);
    $array = explode("\n", $header);
    curl_close($ch);

    // Передаем заголовки типов
  	foreach ($array as $h) {
      if (strpos($h, "Content-Type") !== false || strpos($h, "Content-Disposition") !== false) {
      	header($h);
      }
    }

  	// Отдаем ответ
    echo $body;
    die();
}

// Во всех остальных случаях отображаем главную
?>

<!DOCTYPE html>
<html lang="ru"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

  <title>Расписание занятий - РУЗ</title>
  <base href=".">
  <meta content="width=device-width, initial-scale=1, shrink-to-fit=no" name="viewport">
  <link href="./ruz-files/logo1.png" rel="icon" type="image/x-icon">
<link rel="stylesheet" href="./ruz_files/styles.59440638391218d8da2f.css"><style>.btn-outline-secondary[_ngcontent-iya-c24]{color:inherit}[_nghost-iya-c24]{display:flex;flex-direction:column;overflow:hidden;height:100%}[_nghost-iya-c24]   .ruz[_ngcontent-iya-c24]{flex:1 1 100%;overflow:hidden}</style><style>.btn-outline-secondary[_ngcontent-iya-c20]{color:inherit}  body .ui-widget.ui-toast .ui-toast-message.ui-toast-message-error{background-color:#fff;border:3px solid #dc3545;color:#212121;padding:1rem}  body .ui-widget.ui-toast .ui-toast-message.ui-toast-message-info{background-color:#fff;border:3px solid #17a2b8;color:#212121;padding:1rem}  body .ui-widget.ui-toast .ui-toast-message.ui-toast-message-info h3{font-size:1.5rem}</style><style>.btn-outline-secondary[_ngcontent-iya-c21]{color:inherit}[_nghost-iya-c21]{flex:0 0 auto}.navbar[_ngcontent-iya-c21]{background-color:#e3f2fd;padding:0 1rem;box-shadow:0 .25rem .75rem rgba(0,0,0,.05)}.navbar[_ngcontent-iya-c21]   .navbar-brand[_ngcontent-iya-c21]{outline:none}.navbar[_ngcontent-iya-c21]   .navbar-brand[_ngcontent-iya-c21]:hover{cursor:pointer}.navbar[_ngcontent-iya-c21]   .navbar-toggler[_ngcontent-iya-c21]{margin:.5rem}.navbar[_ngcontent-iya-c21]   .main-menu[_ngcontent-iya-c21]   .nav-item.desktop[_ngcontent-iya-c21]{font-size:1.1rem;padding:.5rem 1rem;border-bottom:3px solid transparent;outline:none}.navbar[_ngcontent-iya-c21]   .main-menu[_ngcontent-iya-c21]   .nav-item.desktop[_ngcontent-iya-c21]:hover{background-color:rgba(0,0,0,.05);border-bottom:3px solid rgba(0,0,0,.2);cursor:pointer}.navbar[_ngcontent-iya-c21]   .main-menu[_ngcontent-iya-c21]   .nav-item.desktop[_ngcontent-iya-c21]:hover   a[_ngcontent-iya-c21]{color:rgba(0,0,0,.7)}.navbar[_ngcontent-iya-c21]   .main-menu[_ngcontent-iya-c21]   .nav-item.desktop.active[_ngcontent-iya-c21]{font-weight:700;border-bottom:3px solid rgba(0,0,0,.1);background-color:rgba(0,0,0,.02)}</style><style>.btn-outline-secondary[_ngcontent-iya-c23]{color:inherit}.footer[_ngcontent-iya-c23], footer[_ngcontent-iya-c23]{width:100%;height:38px;padding:.5rem 0;border-top:3px solid #f5f5f5;font-size:13px}.footer[_ngcontent-iya-c23]   .version[_ngcontent-iya-c23], footer[_ngcontent-iya-c23]   .version[_ngcontent-iya-c23]{text-align:end;color:#ddd}</style><script charset="utf-8" src="./ruz_files/2-es2015.9645a84d35dce87efd82.js"></script><script charset="utf-8" src="./ruz_files/3-es2015.9a102d41783381d05d8b.js"></script><script charset="utf-8" src="./ruz_files/common-es2015.81f1a71dfbabf4cd2f6a.js"></script><script charset="utf-8" src="./ruz_files/8-es2015.23200eb9f1c26df636f4.js"></script><style>[_nghost-iya-c76]{display:block;overflow:auto;height:100%}</style><style>.btn-outline-secondary[_ngcontent-iya-c75]{color:inherit}.day-items[_ngcontent-iya-c75]{margin-bottom:16px;padding-bottom:8px}.autocomplete-wrapper[_ngcontent-iya-c75]{width:222px}.autocomplete-wrapper[_ngcontent-iya-c75]   .autocomplete-component[_ngcontent-iya-c75]{width:100%}.autocomplete-wrapper[_ngcontent-iya-c75]   .custom-control[_ngcontent-iya-c75]{padding-bottom:0;margin-bottom:0}@media (min-width:768px){.autocomplete-wrapper[_ngcontent-iya-c75]{width:400px}}.calendar-item[_ngcontent-iya-c75]   .kind[_ngcontent-iya-c75]{font-size:.8rem;font-style:italic}.item-tooltip[_ngcontent-iya-c75]   .title[_ngcontent-iya-c75]{font-size:1.1rem;font-weight:700}.item-tooltip[_ngcontent-iya-c75]   .lecturer[_ngcontent-iya-c75]{text-transform:capitalize}.checkbox-colored[_ngcontent-iya-c75]{display:block}.btn-toolbar.text-right[_ngcontent-iya-c75]   .btn-group[_ngcontent-iya-c75], .btn.btn-outline-danger.clear-filter[_ngcontent-iya-c75], .week-changer[_ngcontent-iya-c75]{height:38px}.grid.color-types[_ngcontent-iya-c75]     .ui-table .ui-table-tbody>tr>td{padding:0}.grid[_ngcontent-iya-c75]   .time[_ngcontent-iya-c75]{width:120px}.grid[_ngcontent-iya-c75]   .kind[_ngcontent-iya-c75]{font-size:.8rem;font-style:italic}.grid[_ngcontent-iya-c75]   .auditorium[_ngcontent-iya-c75]{font-size:.9rem}.grid[_ngcontent-iya-c75]   .lecturer[_ngcontent-iya-c75]{font-size:.9rem;font-weight:300}.grid[_ngcontent-iya-c75]   .item[_ngcontent-iya-c75]{word-break:break-word}.grid[_ngcontent-iya-c75]   .item[_ngcontent-iya-c75]:not(:last-child){border-bottom:1px solid #d9d9d9;padding-bottom:.5rem}</style></head>
<body>

<ruz-root _nghost-iya-c24="" ng-version="9.0.7"><ruz-ui-page-header _ngcontent-iya-c24="" _nghost-iya-c21="" class="ng-star-inserted"><header _ngcontent-iya-c21=""><nav _ngcontent-iya-c21="" class="navbar navbar-expand-lg navbar-light"><a _ngcontent-iya-c21="" class="navbar-brand mb-0 h1 mr-3 d-flex align-items-center" href="https://ruz.hse.ru/"><img _ngcontent-iya-c21="" height="50" class="d-inline-block align-top rounded mr-2" src="./ruz_files/logo.jpg" alt="РУЗ"><span _ngcontent-iya-c21="">РУЗ</span></a><button _ngcontent-iya-c21="" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" class="navbar-toggler" aria-expanded="false"><span _ngcontent-iya-c21="" class="navbar-toggler-icon"></span></button><div _ngcontent-iya-c21="" id="navbarSupportedContent" class="collapse navbar-collapse"><ul _ngcontent-iya-c21="" class="navbar-nav mr-auto main-menu"><li _ngcontent-iya-c21="" routerlinkactive="active" class="nav-item desktop ng-star-inserted active" tabindex="0"><a _ngcontent-iya-c21="" class="nav-link ng-star-inserted"> Расписание </a><!----></li><li _ngcontent-iya-c21="" routerlinkactive="active" class="nav-item desktop ng-star-inserted" tabindex="0"><a _ngcontent-iya-c21="" class="nav-link ng-star-inserted"> Занятия </a><!----></li><li _ngcontent-iya-c21="" routerlinkactive="active" class="nav-item desktop ng-star-inserted" tabindex="0"><a _ngcontent-iya-c21="" class="nav-link ng-star-inserted"> Загруженность аудиторий </a><!----></li><!----></ul><span _ngcontent-iya-c21="" class="navbar-text d-lg-none mt-3">Язык:</span><ul _ngcontent-iya-c21="" class="navbar-nav"><li _ngcontent-iya-c21="" class="nav-item ng-star-inserted"><button _ngcontent-iya-c21="" type="button" class="btn btn-link nav-link active"> Русский </button></li><li _ngcontent-iya-c21="" class="nav-item ng-star-inserted"><button _ngcontent-iya-c21="" type="button" class="btn btn-link nav-link"> English </button></li><!----></ul></div></nav></header></ruz-ui-page-header><!----><ruz-ui-page-toaster _ngcontent-iya-c24="" _nghost-iya-c20=""><p-toast _ngcontent-iya-c20="" appendto="body" class="ng-tns-c19-0"><div class="ng-tns-c19-0 ui-toast ui-widget ui-toast-top-right"><!----></div></p-toast><p-toast _ngcontent-iya-c20="" appendto="body" key="error" position="center" class="ng-tns-c19-1"><div class="ng-tns-c19-1 ui-toast ui-widget ui-toast-center"><!----></div></p-toast><p-toast _ngcontent-iya-c20="" appendto="body" key="info" position="center" class="ng-tns-c19-2"><div class="ng-tns-c19-2 ui-toast ui-widget ui-toast-center"><!----></div></p-toast></ruz-ui-page-toaster><!----><div _ngcontent-iya-c24="" class="ruz container-fluid mt-3" dir="ltr"><router-outlet _ngcontent-iya-c24=""></router-outlet><ruz-main-index _nghost-iya-c76="" class="ng-star-inserted"><ruz-list _ngcontent-iya-c76="" _nghost-iya-c75=""><div _ngcontent-iya-c75="" class="btn-toolbar justify-content-between mb-3"><div _ngcontent-iya-c75="" class="btn-toolbar"><div _ngcontent-iya-c75="" class="btn-group mt-3 mr-3 d-none d-sm-block"><button _ngcontent-iya-c75="" type="button" class="btn btn-outline-secondary active ng-star-inserted"> Группа </button><button _ngcontent-iya-c75="" type="button" class="btn btn-outline-secondary ng-star-inserted"> Студент </button><button _ngcontent-iya-c75="" type="button" class="btn btn-outline-secondary ng-star-inserted"> Преподаватель </button><button _ngcontent-iya-c75="" type="button" class="btn btn-outline-secondary ng-star-inserted"> Аудитория </button><!----></div><div _ngcontent-iya-c75="" class="mt-3 d-block d-sm-none"><label _ngcontent-iya-c75="">Тип</label><button _ngcontent-iya-c75="" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-outline-secondary dropdown-toggle btn-block"> Группа </button><div _ngcontent-iya-c75="" class="dropdown-menu"><button _ngcontent-iya-c75="" type="button" class="dropdown-item active ng-star-inserted">Группа </button><button _ngcontent-iya-c75="" type="button" class="dropdown-item ng-star-inserted">Студент </button><button _ngcontent-iya-c75="" type="button" class="dropdown-item ng-star-inserted">Преподаватель </button><button _ngcontent-iya-c75="" type="button" class="dropdown-item ng-star-inserted">Аудитория </button><!----></div></div><div _ngcontent-iya-c75="" class="input-group mt-3 autocomplete-wrapper mr-3 ng-star-inserted"><label _ngcontent-iya-c75="" class="d-block d-sm-none" for="autocomplete-group"> Группа </label><p-autocomplete _ngcontent-iya-c75="" emptymessage="Не найдено" datakey="id" field="label" inputstyleclass="form-control" class="autocomplete-component ng-tns-c41-3 ng-untouched ng-pristine ng-valid"><span class="ng-tns-c41-3 ui-autocomplete ui-widget" style="width: 100%;"><input aria-autocomplete="list" role="searchbox" aria-haspopup="true" class="ng-tns-c41-3 form-control ui-inputtext ui-widget ui-state-default ui-corner-all ui-autocomplete-input ng-star-inserted" autocomplete="off" type="text" id="autocomplete-group" name="group" aria-controls="pr_id_1_list" aria-expanded="false" aria-activedescendant="p-highlighted-option" placeholder="Группа"><!----><!----><!----><!----><!----></span></p-autocomplete><!----></div><!----><div _ngcontent-iya-c75="" class="input-group"><div _ngcontent-iya-c75="" class="d-inline-block mr-4 mt-3"><p-calendar _ngcontent-iya-c75="" dateformat="dd.mm.yy" inputid="start" name="start" required="" placeholder="Неделя" class="ng-tns-c42-4 ng-untouched ng-pristine ng-star-inserted ui-inputwrapper-filled ng-valid"><span class="ng-tns-c42-4 ui-calendar ui-calendar-w-btn" style="margin-right: 32px;"><input type="text" autocomplete="off" class="ng-tns-c42-4 ui-inputtext ui-widget ui-state-default ui-corner-all ng-star-inserted" placeholder="Неделя" id="start" name="start" required="" aria-required=""><button type="button" pbutton="" tabindex="0" class="ui-datepicker-trigger ui-calendar-button ng-tns-c42-4 ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only ng-star-inserted"><span aria-hidden="true" class="ui-button-icon-left ui-clickable pi pi-calendar"></span><span aria-hidden="true" class="ui-button-text ui-clickable">ui-btn</span></button><!----><!----><!----></span></p-calendar><!----></div></div><div _ngcontent-iya-c75="" class="input-group mt-3 mr-4"><div _ngcontent-iya-c75="" class="btn-group week-changer"><button _ngcontent-iya-c75="" type="button" class="btn btn-outline-secondary" title="Предыдущая неделя"><i _ngcontent-iya-c75="" class="fa fa-arrow-left"></i></button><button _ngcontent-iya-c75="" type="button" class="btn btn-outline-secondary" title="Следующая неделя"><i _ngcontent-iya-c75="" class="fa fa-arrow-right"></i></button></div></div><button _ngcontent-iya-c75="" type="button" class="btn btn-outline-danger mt-3 clear-filter"><i _ngcontent-iya-c75="" class="fa fa-times fa-fw"></i></button></div><div _ngcontent-iya-c75="" class="btn-toolbar text-right"><!----><div _ngcontent-iya-c75=""><div _ngcontent-iya-c75="" class="btn-group mt-3 ng-star-inserted"><button _ngcontent-iya-c75="" type="button" class="btn btn-outline-secondary active ng-star-inserted"> Список </button><button _ngcontent-iya-c75="" type="button" class="btn btn-outline-secondary ng-star-inserted"> Сетка </button><!----></div><!----><!----></div></div></div><!----><!----><div _ngcontent-iya-c75="" class="mb-3 ng-star-inserted"> Установите фильтры
</div><!----><div _ngcontent-iya-c75="" class="list ng-star-inserted"><!----></div><!----><!----><!----></ruz-list></ruz-main-index><!----></div><ruz-ui-page-footer _ngcontent-iya-c24="" _nghost-iya-c23="" class="ng-star-inserted"><footer _ngcontent-iya-c23="" class="footer mt-4"><div _ngcontent-iya-c23="" class="container-fluid"><div _ngcontent-iya-c23="" class="d-flex justify-content-between flex-wrap"><div _ngcontent-iya-c23="" class="d-flex justify-content-start flex-wrap"><div _ngcontent-iya-c23="" class="text-muted d-inline-block mr-5 copyright"><span _ngcontent-iya-c23="" class="ng-star-inserted"> 2017 - </span><!----> 2023 <span _ngcontent-iya-c23="" class="mx-1">©</span> Расписание учебных занятий </div><div _ngcontent-iya-c23="" class="text-muted d-inline-block ng-star-inserted"><div _ngcontent-iya-c23="" class="ng-star-inserted"><a _ngcontent-iya-c23="" target="_blank" href="https://pmo.hse.ru/servicedesk/customer/portal/81/group/319"> Поддержка </a></div><!----><!----></div><!----><!----><!----><!----></div><div _ngcontent-iya-c23=""><div _ngcontent-iya-c23="" class="version"> v1.16.16 </div></div></div></div></footer></ruz-ui-page-footer><!----></ruz-root>
<link href="./ruz_files/styles.css" type="text/css" rel="stylesheet">
<script src="./ruz_files/runtime-es2015.0c13229453d95b6dd583.js" type="module"></script><script src="./ruz_files/runtime-es5.0c13229453d95b6dd583.js" nomodule="" defer=""></script><script src="./ruz_files/polyfills-es5.cf432a12777c848d9fca.js" nomodule="" defer=""></script><script src="./ruz_files/polyfills-es2015.0b2adbeba9e0c28c1eb9.js" type="module"></script><script src="./ruz_files/scripts.fcbcbb6cadcc3d8026ab.js" defer=""></script><script src="./ruz_files/main-es2015.596da396bf11ef41a069.js" type="module"></script><script src="./ruz_files/main-es5.596da396bf11ef41a069.js" nomodule="" defer=""></script>

</body></html>
