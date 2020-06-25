<?php

/**
 * 네이버 쇼핑 1페이지 정보 추출.
 */
class NaverShopping
{
    const URL_NAVER_SHOPPING = 'https://search.shopping.naver.com/search/all?cat_id=&frm=NVSHATC&query=';
    const URL_NAVER_MAIN = 'https://search.naver.com/search.naver?sm=tab_hty.top&where=nexearch&query=';

    private $code = 200;

    private $debug = false;
    private $refresh = false;

    private $data = array(
        'keyword' => null, // 검색 키워드
        'relKeywords' => '', // 연관쇼핑
        'hotKeywords' => '', // 많이 사용되는 키워드
        'categoryTexts' => '',
        'category' => 0,
        'totalItems' => 0, // 총 등록 상품 수
        'avgSellPrice' => 0, // 평균 판매액
        'lowPrice' => 0,
        'avgPrice' => 0,
        'highPrice' => 0,
        'lowSell' => 0,
        'avgSell' => 0,
        'highSell' => 0,
        'lowReview' => 0,
        'avgReview' => 0,
        'highReview' => 0,
        'monthlyAveMobileClkCnt' => 0,
        'monthlyAvePcClkCnt' => 0,
        'monthlyMobileQcCnt' => 0,
        'monthlyPcQcCnt' => 0,
        'monthlyQcCnt' => 0,
        'trends' => '',
        'season' => 0,
        'seasonMonth' => 0,
        'modDate' => null,
        'ignored' => 0,
        'hasMainShoppingSearch' => 0,
    );

    function __construct() {
    }

    public function setDebug($bool)
    {
        $this->debug = $bool;
    }

    public function setRefresh($bool)
    {
        $this->refresh = $bool;
    }

    public function setKeyword($keyword)
    {
        if (empty($keyword)) {
            return false;
        }

        $this->data['keyword'] = $keyword;
    }

    public function setData($data)
    {
        if (!empty($data)) {
            $this->data = array_merge($this->data, $data);
        }
    }

    public function getData()
    {
        return $this->data;
    }

    private function setCode($code)
    {
        print_r('setCode');
        print_r($code);
        if (!empty($code)) {
            $this->code = $code;
        }
    }

    public function getCode()
    {
        return $this->code;
    }

    public function needUpdate()
    {
        if ($this->debug) return true;
        if ($this->refresh) return true;
        if (empty($this->data['modDate'])) return true;
        if ($this->data['modDate'] < date('Y-m-d H:i:s', strtotime('-1 day'))) return true;

        return false;
    }

    /**
     * 키워드 검색광고 데이터 수집
     * 무제한
     * 키워드의 최근 한달간의 검색, 광고 관련 정보
     */
    public function requestKeywordSearchAd(RestApi $api)
    {
        if (empty($this->data['keyword'])) {
            $this->setCode(400);
            return false;
        }

        if (empty($api)) {
            $this->setCode(203);
            return false;
        }

        // 키워드광고 api
        $keywordstool = $api->GET("https://api.naver.com/keywordstool", array(
            'hintKeywords' => $this->data['keyword'],
            'showDetail' => 1
        ));

        if (empty($keywordstool) || empty($keywordstool['keywordList']) || empty($keywordstool['keywordList'][0])) {

            if (!empty($keywordstool['code'])) {
                $this->setCode($keywordstool['code']);
                return false;
            }

            $this->setCode(204);
            return false;
        }

        $resultTool = $keywordstool['keywordList'][0];

        if ($this->debug) {
            print_r('[requestKeywordSearchAd]');
            print_r($resultTool);
        }

        $this->data['monthlyAveMobileClkCnt'] = @ceil($resultTool['monthlyAveMobileClkCnt']);
        $this->data['monthlyAvePcClkCnt'] = @ceil($resultTool['monthlyAvePcClkCnt']);
        $this->data['monthlyMobileQcCnt'] = @filter_var($resultTool['monthlyMobileQcCnt'], FILTER_SANITIZE_NUMBER_INT);
        $this->data['monthlyPcQcCnt'] = @filter_var($resultTool['monthlyPcQcCnt'], FILTER_SANITIZE_NUMBER_INT);
        $this->data['monthlyQcCnt'] = @ceil($this->data['monthlyMobileQcCnt'] + $this->data['monthlyPcQcCnt']);

        return true;
    }

    /**
     * 키워드 검색광고 데이터 수집
     * 무제한
     * 연관 키워드 수집
     */
    public function requestKeywordSearchAdAll(RestApi $api)
    {
        if (empty($this->data['keyword'])) {
            $this->setCode(400);
            return false;
        }

        if (empty($api)) {
            $this->setCode(203);
            return false;
        }

        // 키워드광고 api
        $keywordstool = $api->GET("https://api.naver.com/keywordstool", array(
            'hintKeywords' => $this->data['keyword'],
            'showDetail' => 1
        ));

        if (empty($keywordstool) || empty($keywordstool['keywordList']) || empty($keywordstool['keywordList'][0])) {

            if (!empty($keywordstool['code'])) {
                $this->setCode($keywordstool['code']);
                return false;
            }

            $this->setCode(204);
            return false;
        }

        $keywordstoolFiltered = array();

        foreach ($keywordstool['keywordList'] as $key => $value) {
            if ($value['monthlyMobileQcCnt'] < 101 || $value['monthlyPcQcCnt'] < 101) continue;
            $keywordstoolFiltered[] = $value['relKeyword'];
        }

        return $keywordstoolFiltered;
    }

    /**
     * 키워드 검색 트렌드 데이터 수집
     * 일 1000건 제한
     * 지난 2년간 트렌드 정보로 시즌상품 여부 및 트렌트 계산
     * https://developers.naver.com/docs/datalab/search/
     */
    public function requestKeywordTrend(RestApi $api)
    {
        if (empty($this->data['keyword'])) {
            $this->setCode(400);
            return false;
        }

        if (empty($api)) {
            $this->setCode(203);
            return false;
        }

        // debug 모드가 아닌 경우 트렌드데이터가 없는 경우에만 수집한다. (횟수 제한 때문에)
        if (!$this->debug && !empty($this->data['trends'])) return true;

        // 횟수 제한 때문에 검색량이 적은 키워드는 수집 X
        if ($this->data['monthlyQcCnt'] < 500) return true;

        $startTrend = date('Y', strtotime('-2 year')).'-01-01';
        $endTrend = date('Y', strtotime('-1 year')).'-12-31';
        $period = new DatePeriod((new DateTime($startTrend))->modify('first day of this month'), DateInterval::createFromDateString('1 month'), (new DateTime($endTrend))->modify('first day of next month'));
        $trendsFull = array();

        foreach ($period as $date) {
            $trendsFull[$date->format("Y-m").'-01'] = 0;
        }

        $keywordtrend = $api->POST("https://openapi.naver.com/v1/datalab/search", array(
            'startDate' => $startTrend,
            'endDate' => $endTrend,
            'timeUnit'=> 'month',
            "keywordGroups" => array(
                array(
                    'groupName' => $this->data['keyword'],
                    'keywords' => array($this->data['keyword'])
                )
            )
        ));

        if ($this->debug) {
            print_r('[requestKeywordTrend]');
            print_r($keywordtrend);
        }

        if (!empty($keywordtrend) && !empty($keywordtrend['results']) && !empty($keywordtrend['results'][0]['data'])) {
            $trends = array();

            foreach ($keywordtrend['results'][0]['data'] as $trend) {
                $trendsFull[$trend['period']] = $trend['ratio'];
            }

            $trends['twoYearBefore'] = array_slice($trendsFull, 0, 12);
            $trends['oneYearBefore'] = array_slice($trendsFull, 12);

            foreach ($trends as $key => $trend) {
                if (max($trend) != 100 && max($trend) > 0) {
                    $trendRatio = (100 / max($trend));
                    foreach ($trend as $k => $v) {
                        $trends[$key][$k] = floor($v * $trendRatio);
                    }
                } else {
                    foreach ($trend as $k => $v) {
                        $trends[$key][$k] = ceil($v);
                    }
                }
            }

            foreach ($trends as $key => $trend) {
                if ((max($trend) - min($trend)) > 60) {
                    $month = date('m', strtotime(array_keys($trend, max($trend))[0]));

                    if ($month < 3 || $month > 10) $season = 4;
                    else if ($month < 6) $season = 1;
                    else if ($month < 9) $season = 2;
                    else if ($month < 11) $season = 3;

                    $trends[$key.'Season'] = array(
                        'month' => $month,
                        'season' => $season,
                    );
                }
            }

            if (!empty($trends['twoYearBeforeSeason']) && !empty($trends['oneYearBeforeSeason']) && $trends['twoYearBeforeSeason']['season'] == $trends['oneYearBeforeSeason']['season']) {
                $this->data['season'] = $trends['oneYearBeforeSeason']['season'];
                $this->data['seasonMonth'] = $trends['oneYearBeforeSeason']['month'];
            }

            $this->data['trends'] = implode(',', $trends['oneYearBefore']);

            if ($this->debug) {
                print_r($trends);
            }
        }

        return true;
    }

    public function crawlingNaverShopping()
    {
        if (empty($this->data['keyword'])) {
            $this->setCode(400);
            return false;
        }

        $xPath = $this->getXpath(self::URL_NAVER_SHOPPING.urlencode($this->data['keyword']));

        if (!$xPath) {
            $this->setCode(400);
            return false;
        }

        $relKeywords = array();
        $titles = array();
        $prices = array();
        $sells = array();
        $sellsPrice = array();
        $reviews = array();
        $categoryTexts = array();
        $hotKeywords = array();

        $nodeTotalItems = $xPath->query("//div[@class='seller_filter_area']/ul/li");
        if ($nodeTotalItems->length == 0) {
            $this->setCode(400);
            return false;
        }

        $this->data['totalItems'] = filter_var(trim($nodeTotalItems[0]->nodeValue), FILTER_SANITIZE_NUMBER_INT);
        $this->data['raceIndex'] = @round($this->data['totalItems'] / $this->data['monthlyQcCnt'], 4);


        if ($this->data['raceIndex'] > 10) $this->data['ignored'] = 1;

        $nodeRelKeywords = $xPath->query("//div[@class='relatedTags_relation_srh__1CleC']/ul/li/a");

        if ($nodeRelKeywords->length > 0) {
            foreach ($nodeRelKeywords as $nodeRelKeyword) {
                $relKeywords[] = trim($nodeRelKeyword->nodeValue);
            }
        }

        if (!empty($relKeywords)) {
            $this->data['relKeywords'] = implode(',', $relKeywords);
        }

        // $nodeItems = $xPath->query("//ul[@class='list_basis']/div/div/div/div");
        $nodeItems = $xPath->query("//li[contains(@class, 'basicList_item__2XT81')]");

        if ($nodeItems->length == 0) {
            $this->setCode(400);
            return false;
        }

        if ($nodeItems->length > 0) {
            $totalSalesPrice = 0;
            foreach ($nodeItems as $nodeItem) {
                $nodeTitles = $xPath->query("descendant::div[@class='basicList_title__3P9Q7']", $nodeItem);
                if ($nodeTitles->length > 0) {
                    $titles[] = trim($nodeTitles[0]->nodeValue);
                }

                $nodePrices = $xPath->query("descendant::span[@class='price_num__2WUXn']", $nodeItem);
                if ($nodePrices->length > 0) {
                    $prices[] = filter_var(trim($nodePrices[0]->nodeValue), FILTER_SANITIZE_NUMBER_INT);
                }

                $nodeInfos = $xPath->query("descendant::div[@class='basicList_etc_box__1Jzg6']/a", $nodeItem);

                if ($nodeInfos->length > 0) {
                    foreach ($nodeInfos as $nodeInfo) {
                        $textInfo = trim($nodeInfo->nodeValue);

                        if (strpos($textInfo, '리뷰') !== false) {
                            $nodeInfo2 = $xPath->query("descendant::em[@class='basicList_num__1yXM9']", $nodeInfo);
                            if ($nodeInfo2->length > 0) {
                                $textInfo = trim($nodeInfo2[0]->nodeValue);
                                $reviews[] = filter_var($textInfo, FILTER_SANITIZE_NUMBER_INT);
                            }
                        } else if (strpos($textInfo, '구매') !== false) {
                            $sells[] = filter_var($textInfo, FILTER_SANITIZE_NUMBER_INT);
                            if ($nodePrices->length > 0) {
                                $sellsPrice[] = filter_var($textInfo, FILTER_SANITIZE_NUMBER_INT) * filter_var(trim($nodePrices[0]->nodeValue), FILTER_SANITIZE_NUMBER_INT);
                            }
                        }
                    }
                }

                // $nodeSells = $xPath->query("descendant::span[@class='etc']/span[contains(., '구매건수')]", $nodeItem);
                // if ($nodeSells->length > 0) {
                //     $sells[] = filter_var(trim($nodeSells[0]->nodeValue), FILTER_SANITIZE_NUMBER_INT);

                //     if ($nodePrices->length > 0) {
                //         $sellsPrice[] = filter_var(trim($nodePrice->nodeValue), FILTER_SANITIZE_NUMBER_INT) * filter_var(trim($nodeSells[0]->nodeValue), FILTER_SANITIZE_NUMBER_INT);
                //     }
                // }

                // $nodeReviews = $xPath->query("descendant::span[@class='etc']/a[contains(., '리뷰')]|descendant::span[@class='etc']/span[contains(., '리뷰')]", $nodeItem);
                // if ($nodeReviews->length > 0) {
                //     $reviews[] = filter_var(trim($nodeReviews[0]->nodeValue), FILTER_SANITIZE_NUMBER_INT);
                // }

                if (empty($this->data['category']) || $this->data['category'] == '9999') {
                    $nodeAd = $xPath->query("descendant::button[@class='ad_ad_stk__12U34']", $nodeItem);
                    if ($nodeAd->length > 0) continue;

                    $nodeCategories = $xPath->query("descendant::div[@class='basicList_depth__2QIie']/a", $nodeItem);
                    if ($nodeCategories->length > 0) {
                        foreach ($nodeCategories as $category) {
                            $categoryTexts[] = $category->nodeValue;
                            $this->data['category'] = filter_var(trim($category->getAttribute('href')), FILTER_SANITIZE_NUMBER_INT);
                        }

                        $this->data['categoryTexts'] = implode(',', $categoryTexts);
                    }
                }
            }
        }

        if (!empty($sellsPrice)) {
            $this->data['avgSellPrice'] = ceil(array_sum($sellsPrice) / count($sellsPrice));
        }

        if (!empty($prices)) {
            $this->data['lowPrice'] = min($prices);
            $this->data['avgPrice'] = ceil(array_sum($prices) / count($prices));
            $this->data['highPrice'] = max($prices);
        }

        if (!empty($sells)) {
            $this->data['lowSell'] = min($sells);
            $this->data['avgSell'] = ceil(array_sum($sells) / count($sells));
            $this->data['highSell'] = max($sells);
        }

        if (!empty($reviews)) {
            $this->data['lowReview'] = min($reviews);
            $this->data['avgReview'] = ceil(array_sum($reviews) / count($reviews));
            $this->data['highReview'] = max($reviews);
        }

        $this->data['saleIndex'] = $this->data['avgReview'] + $this->data['avgSell'];

        if (!empty($titles)) {
            foreach ($titles as $title) {
                $title = strip_tags($title);
                $title = str_replace(array('-', '_', ',', '/', '(', ')', '[', ']'), ' ', $title);
                $title = trim(preg_replace('/\s+/', ' ', $title));
                $titles = explode(' ', $title);
                $titles = array_filter($titles);

                if (!empty($titles)) {
                    $hotKeywords = array_merge($hotKeywords, $titles);
                }
            }

            if (!empty($hotKeywords)) {
                $hotKeywords = array_count_values($hotKeywords);
                unset($hotKeywords['']);
                arsort($hotKeywords);
                $hotKeywords = array_slice($hotKeywords, 0, 10, true);

                $hotKeywordTexts = array();

                foreach ($hotKeywords as $key => $value) {
                    $hotKeywordTexts[] = $key.'('.$value.')';
                }

                $this->data['hotKeywords'] = implode(',', $hotKeywordTexts);
            }
        }

        // 메인쇼핑검색 여부
        $xPathMain = $this->getXpath(self::URL_NAVER_MAIN.$this->data['keyword']);

        if ($xPathMain) {
            $nodeMainShopping = $xPathMain->query("//div[@class='dsc_ncaution2 _shopping_info_area']");

            if ($nodeMainShopping->length > 0) {
                $this->data['hasMainShoppingSearch'] = 1;
            }
        }

        if ($this->debug) {
            print_r('[crawlingNaverShopping]');
            print_r($relKeywords);
            print_r($titles);
            print_r($prices);
            print_r($sells);
            print_r($sellsPrice);
            print_r($reviews);
            print_r($categoryTexts);
            print_r($hotKeywords);
        }

        return true;
    }

    private function getXpath($url)
    {
        if (empty($url)) {
            $this->setCode(400);
            return false;
        }

        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $headers = [
            'Cache-Control: no-cache',
            'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
            'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:28.0) Gecko/20100101 Firefox/28.0',
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $html = curl_exec($ch);
        curl_close($ch);

        $dom = new DOMDocument();

        @$dom->loadHTML($html);

        // print_r($url);
        // print_r($html);
        // print_r($dom);

        return new DOMXPath($dom);
    }
}
