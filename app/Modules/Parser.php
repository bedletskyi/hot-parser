<?php

namespace App\Modules;

use Illuminate\Support\Facades\Log;
use phpQuery;
use RollingCurlMini;


class Parser
{
    /**
     * @var integer - колличество потоков
     */
    const N_THREADS = 10;

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
     * @var array - массив для хранения ссылок которые в обработке
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

    /**
     * @var array - массив спарсеных цен
     */
    protected $prices = array();

    /**
     * @var bool - Индикатор использования прокси
     */
    protected $useProxy = false;

    public function __construct($urls, $report, $proxies = null)
    {
        ini_set('max_execution_time', '2000');

        $this->aData = $urls;

        $this->reportId = $report;

        if ($proxies != null)
            $this->useProxy = true;
            $this->aProxies = $proxies;
    }

    /**
     * Цыкл работы парсера
     *
     * @return array
     */
    public function getPrice()
    {
        $nThreads = ($this->useProxy == false ? self::N_THREADS : min(self::N_THREADS, count($this->aProxies)));

        $this->oMc = new RollingCurlMini($nThreads);
        $this->oMc->setOptions(self::$A_CURL_OPTS);

        for ($l = 0; 0 != count($this->aData); $l++) {
            Log::info("Круг прохода ссылок: {$l}");
            if ($l > 0 and $this->useProxy == true) {
                $this->rewriteProxy();
            }

            foreach ($this->aData as $id => $a_item) {
                $url = $a_item['url'];

                $this->oMc->add($url, 0, array($this, 'handleFirst'), $id,
                    $this->buildItemOptions($id, $url, true));
            }
            $this->oMc->execute();
        }

        return $this->prices;
    }

    /**
     * Перзаписываем массив с прокси
     * @return bool
     */
    public function rewriteProxy()
    {
        $proxies = $this->aProxies;
        $this->aProxies = sort($proxies);
        return true;
    }

    /**
     * Колбек для обработки ответа от запроса страницы токена
     * @param string $content - содержимое ответа
     * @param string $url - URL запроса
     * @param array $aInfo - информация о запросе
     * @param int $id - страницы
     */
    public function handleFirst($content, $url, $aInfo, $id)
    {

        if (!isset($this->aData[$id])) return;

        if (!$content) {
            $this->log($id, 'Нет содержания страницы.');
            return;
        }

        if (!$this->parseToken($content, $id)) return;

        $url_second = $this->aData[$id]['url'] . 'load-prices/';
        if (!$url_second) return;

        $this->oMc->add(
            $url_second, 0, array($this, 'handleSecond'), $id,
            $this->buildItemOptions($id, $url)
        );
    }

    /**
     * Получаем csrf токен из мета тега
     * @param string $content - содержимое страницы сообщения
     * @param int $id - id товара
     * @return true
     */
    public function parseToken($content, $id)
    {
        $dom = phpQuery::newDocument($content);

        $token = $dom->find('head > meta[name=csrf-token]')->attr('content');

        phpQuery::unloadDocuments($content);

        if (!$token) {
            $this->log($id, 'Не удалось получить токен. Не правильная ссылка, капча.', true);
            return false;
        }
        $this->aData[$id]['token'] = $token;
        return true;
    }

    /**
     * Колбек для обработки ответа от запроса на прайс лист
     * @param string $content
     * @param string $url
     * @param array $aInfo
     * @param int $id - номер соотв-щей страницы сообщения
     */
    public function handleSecond($content, $url, $aInfo, $id)
    {
        if (!isset($this->aData[$id])) return;

        if (!$content) {
            $this->log($id, 'Не удалось получить данные с ценами.');
            return;
        }

        if (!$this->addDataRow($content, $url, $id)) {
            $this->log($id, 'Нет свойства prices в данных');
            return;
        }

        unset($this->aData[$id]);
    }

    /**
     * Запись данных в базу
     * @param string $data
     * @return bool
     */
    public function addDataRow($data, $url, $id)
    {
        $json = json_decode($data);

        if (property_exists($json,'reload') or property_exists($json,'redirect')) {
            $this->log($id, 'Нет цен ссылка пропущена');
            return true;
        }

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
                $this->prices[] = $data;
            }
        }
        return true;
    }

    /**
     * @param $id - id ссылки для которой нужно удалить прокси
     * @return int - id удаленно прокси
     */
    public function deleteProxy($id)
    {
        $result['id'] = $this->aData[$id]['i_proxy']['id'];

        unset($this->aProxies[$result['id']]);

        return $result['id'];
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

        if ($this->useProxy == true) {

            if ($b1st) {
                $r_item['i_proxy'] = $this->randProxy();
            }

            $a_opts[CURLOPT_PROXY] = $r_item['i_proxy']['ip'];
            $a_opts[CURLOPT_PROXYTYPE] = $r_item['i_proxy']['protocol'];
        }

        if (isset($r_item['token']) ) {
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

    public function log($id, $message, $option = false)
    {
        $link = $this->aData[$id];
        if (array_key_exists('i_proxy', $link)) {
            $deleted = $this->deleteProxy($id);
            Log::info("Прокси. $message Ссылка: {$link['url']} => Удален прокси {$deleted}");
        } else {
            if ($option) {
                $message .= 'Ссылка удалена';
                unset($this->aData[$id]);
            }
            Log::warning("$message {$link['url']}");
        }
    }
}
