<?php

namespace App\Http\Controllers;

use App\Price;
use App\Product;
use App\Report;
use Illuminate\Http\Request;
use phpQuery;
use RollingCurlMini;


class ParserController extends Controller
{
    /**
     * @var integer - колличество потоков
     */
    const N_THREADS = 10;

    /**
     * @var string - файл логов парсера
     */
    const FILE = 'log.txt';

    /**
     * @var array - массив свойт для CURL
     */
    static protected $A_CURL_OPTS = array(
        CURLOPT_NOBODY => 0,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_AUTOREFERER => true,
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.181 Safari/537.36',
        CURLOPT_TIMEOUT => 20,
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLINFO_HEADER_OUT => true,
    );

    /**
     * @var object - объект класса RollingCurlMini
     */
    protected $oMc = 0;

    /**
     * @var array - массив для хранения спарсенных данных
     */
    protected $aData = array();

    /**
     * @var array - массив используемых прокси
     */
    protected $aProxies = array();

    /**
     * @var null - ID отчета
     */
    protected $reportId = null;


    public function start(Request $request)
    {
        if ($request->ajax()) {
            $start = $request->input('message');

            if ( $start == 'ok' ) {
                $this->run();
            }
        }
    }

    /**
     * Цыкл работы парсера
     *
     * @return bool|void
     */
    public function run()
    {
        ini_set('max_execution_time', '2000');

        $this->makeReport();

        $this->initData();

        if (!$this->aData) return;

        $this->initProxies();

        $nThreads = min(self::N_THREADS, count($this->aProxies) );

        $this->oMc = new RollingCurlMini($nThreads);
        $this->oMc->setOptions(self::$A_CURL_OPTS);

        for ($l = 0; 0 != count($this->aData); $l++) {
            $this->writeLog("Круг обхода ссылок: $l \n");

            if ($l > 0) {
                $this->rewriteProxy();
            }

            foreach ($this->aData as $id => $a_item) {
                $url = $a_item['url'];

                $this->oMc->add($url, 0, array($this, 'handleMsg'), $id,
                    $this->buildItemOptions($id, $url, true));
            }
            $this->oMc->execute();
        }
        $this->writeLog("Отчет завершен!!! ------------------------------------ \n\n");
        return true;
    }

    /**
     * Перзаписываем массив с прокси
     * @return bool
     */
    public function rewriteProxy()
    {
        $proxies = $this->aProxies;
        $this->aProxies = array();

        $i = 0;
        foreach ($proxies as $proxy) {
            $this->aProxies[$i] = $proxy;
            $i++;
        }
        return true;
    }

    /**
     * @param $data - строка записи в лог
     * @return bool
     */
    public function writeLog($data)
    {
        $result = file_put_contents(self::FILE, $data, FILE_APPEND | LOCK_EX);
        if ($result === true ) {
            return true;
        }
    }

    /**
     * Создаем новый отчет и возвращаем его ID
     * @return mixed
     */
    public function makeReport()
    {
        date_default_timezone_set('Europe/Kiev');
        $model = Report::create([
            'name' => 'Hotline ' . date('d.m.y H:i')
        ]);

        $this->writeLog("Создан отчет: $model->name ------------------------------------- \n" );

        return $this->reportId = $model->id;
    }

    /**
     * Получаем все ссылки на позиции и возвращаем их массив
     * @return mixed
     */
    protected function initData()
    {
        $products = Product::all();

        foreach ($products as $product){
            $uri_parts = explode('/', $product->Link);
            $links[$product->id] = [
                'url' => 'https://hotline.ua/' . $uri_parts[3] . '/' . $uri_parts[4] . '/'
            ];
        }
        $quantity = count($links);
        $this->writeLog("Получено ссылок: $quantity \n" );

        return $this->aData = $links;
    }

    /**
     * Получаем список Прокси
     * @return bool
     */
    protected function initProxies()
    {
        $ch = curl_init('https://hidemy.name/ru/proxy-list/?country=UA&maxtime=3000#list');

        curl_setopt($ch, CURLOPT_NOBODY, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36');
        curl_setopt($ch, CURLOPT_COOKIE, "__cfduid=dfed48de5752dd009b2c98bdbd90774941527924035");
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);

        $response = (string) curl_exec($ch);

        curl_close($ch);

        $dom = phpQuery::newDocument($response);

        $table = $dom->find('table.proxy__t tbody tr');
        $i=0;

        foreach ($table as $tr){
            $tr = pq($tr);

            $ip = $tr->find('td.tdl')->text();
            $port = $tr->find('td:eq(1)')->text();
            $protocol = $tr->find('td:eq(4)')->text();

            if($protocol == 'HTTP')
                $protocol = 'CURLPROXY_HTTP';

            if ($protocol == 'HTTPS')
                $protocol = 'CURLPROXY_HTTPS';

            if ($protocol == 'SOCKS4')
                $protocol = 'CURLPROXY_SOCKS4';

            if ($protocol == 'SOCKS5')
                $protocol = 'CURLPROXY_SOCKS5';

            $this->aProxies[$i] = [
                'ip' => $ip . ':' . $port,
                'protocol' => $protocol
            ];

            $i++;
        }
        $quantity = count($this->aProxies);
        $this->writeLog("Получено прокси: $quantity \n" );

        return true;
    }

    /**
     * Колбек для обработки ответа от запроса страницы токена
     * @param string $content - содержимое ответа
     * @param string $url - URL запроса
     * @param array $aInfo - информация о запросе
     * @param int $id - номер страницы сообщения
     */
    public function handleMsg($content, $url, $aInfo, $id)
    {

        if (!isset($this->aData[$id])) return;

        if (!$content) {
            $deleted = $this->deleteProxy($id);

            $this->writeLog("Битый сервер. Ссылка: $url => Удален прокси {$deleted['id']} / Осталось прокси: {$deleted['quantity']}\n");

            return;
        }

        $token = $this->parseToken($content);


        if (!$token) {
            $deleted = $this->deleteProxy($id);

            $this->writeLog("Нет токена(капча). Ссылка: $url => Удален прокси {$deleted['id']} / Осталось прокси: {$deleted['quantity']}\n");

            return;
        }

        $this->writeLog("Токен получен. Ссылка: $url => $token \n");
        $this->aData[$id]['token'] = $token;

        $url_xtra = $this->aData[$id]['url'] . 'load-prices/';

        if (!$url_xtra) return;

        $this->oMc->add(
            $url_xtra, 0, array($this, 'handleXtra'), $id,
            $this->buildItemOptions($id, $url)
        );
    }

    /**
     * Получаем csrf токен из мета тега
     * @param string $content - содержимое страницы сообщения
     * @return string
     */
    public function parseToken($content)
    {
        $dom = phpQuery::newDocument($content);

        $token = $dom->find('head > meta[name=csrf-token]')->attr('content');

        phpQuery::unloadDocuments($content);

        return $token;
    }

    /**
     * Колбек для обработки ответа от запроса на прайс лист
     * @param string $content
     * @param string $url
     * @param array $aInfo
     * @param int $id - номер соотв-щей страницы сообщения
     */
    public function handleXtra($content, $url, $aInfo, $id)
    {
        if (!isset($this->aData[$id])) return;

        if (!$content) {
            $deleted = $this->deleteProxy($id);

            $this->writeLog("Нет файла цен. Ссылка: $url => Удален прокси {$deleted['id']} / Осталось прокси: {$deleted['quantity']} \n");

            return;
        }

        if (!$this->addDataRow($content, $url, $id)) {
            $deleted = $this->deleteProxy($id);

            $this->writeLog("Нет файла цен. Нет свойства в обьекте. Ссылка: $url => Удален прокси {$deleted['id']} / Осталось прокси: {$deleted['quantity']} \n");

            return;
        }

        unset($this->aData[$id]);

        $quantity_link = count($this->aData);
        $this->writeLog("Ссылка успешно удалена. Ссылка: $url => Осталось ссылок: $quantity_link \n");
    }

    /**
     * Запись данных в базу
     * @param string $data
     * @return bool
     */
    public function addDataRow($data, $url, $id)
    {
        $json = json_decode($data);

        if (!property_exists($json,'prices')) return false;

        $prices = $json->prices;

        foreach ($prices as $price) {
            if ( $price->condition == 'new' and $price->condition_title != 'Восстановленный' ) {
                $data = [
                    'report_id' => $this->reportId,
                    'product_id' => $id,
                    'store' => $price->firm_website,
                    'price' => $price->price_uah_real_raw,
                    'date' => $price->dt,
                    'link' => 'https://hotline.ua' . $price->url
                ];
                Price::create($data);
            }
        }
        $this->writeLog("Цены успешно получены!!! - $url \n");
        return true;
    }

    /**
     * @param $id - id ссылки для которой нужно удалить прокси
     * @return array - колличество оставшихся прокси в работе
     */
    public function deleteProxy($id)
    {
        $result['id'] = $this->aData[$id]['i_proxy']['id'];

        unset($this->aProxies[$result['id']]);

        $result['quantity'] = count($this->aProxies);

        return $result;
    }

    /**
     * Составление cURL-опций для запроса из заданной пары запросов
     * @param int $id - номер страницы сообщения, соотв-щей заданной паре
     * @param string $url - URL запроса
     * @param bool $b1st - является ли данный запрос первым в своей паре
     * @return array - массив cURL-опций
     */
    protected function buildItemOptions($id, $url, $b1st = false)
    {
        if (!isset($this->aData[$id])) return false;

        $r_item = &$this->aData[$id];

        $a_opts = array();

        if ( 0 != count($this->aProxies)) {

            if ($b1st) {
                $r_item['i_proxy'] = $this->randProxy();
            }

            $a_opts[CURLOPT_PROXY] = $r_item['i_proxy']['ip'];
            $a_opts[CURLOPT_PROXYTYPE] = $r_item['i_proxy']['protocol'];
        }

        if ( isset($r_item['token']) ) {
            $token = $r_item['token'];
            $a_opts[CURLOPT_HTTPHEADER] = array("x-csrf-token: $token");
        }

        $r_item['url_prev'] = $url;

        return $a_opts;
    }

    public function randProxy()
    {
        $n = count($this->aProxies);
        $rand = mt_rand(0, $n - 1);

        if (!isset($this->aProxies[$rand])) {
            $this->randProxy();
        }
        $proxy = $this->aProxies[$rand];
        $proxy['id'] = $rand;

        return $proxy;
    }

    /**
     * Исключение прокси, использованного в заданной паре запросов,
     * из списка прокси
     * @param int $id - номер страницы сообщения, соотв-щей заданной паре
     */
//    protected function excludeItemProxy($id) {
//
//        if (!isset($this->aData[$id]) || !$this->aData[$id]['i_proxy'])
//            return;
//
//        $i = $this->aData[$id]['i_proxy'];
//
//        if (!isset($this->aProxies[$i]))
//            return;
//
//        unset($this->aProxies[$i]);
//
//        $this->aProxies = array_values($this->aProxies);
//    }
}
