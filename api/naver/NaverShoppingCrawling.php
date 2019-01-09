<?php

/**
 * 네이버 쇼핑 1페이지 정보 추출.
 */
class NaverShoppingCrawling
{
    private const URL_NAVER_SHOPPING = 'https://search.shopping.naver.com/search/all.nhn?cat_id=&frm=NVSHATC&query=';

    private $data = array(
        'keyword' => null, // 검색 키워드
        'relKeywords' => array(), // 연관쇼핑
        'hotKeywords' => array(), // 많이 사용되는 키워드
        'titles' => array(), // 상품명
        'prices' => array(), // 가격
        'sells' => array(), // 판매수
        'sellsPrice' => array(), // 판매수 * 가격
        'reviews' => array(), // 리뷰수
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
    );

    private function getXpath()
    {
        if (empty($this->data['keyword'])) {
            return false;
        }

        $url = self::URL_NAVER_SHOPPING.$this->data['keyword'];

        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $html = curl_exec($ch);
        curl_close($ch);

        $dom = new DOMDocument();

        @$dom->loadHTML($html);

        return new DOMXPath($dom);
    }

    public function collectByKeyword($keyword)
    {
        if (empty($keyword)) {
            return false;
        }

        $this->data['keyword'] = $keyword;

        $xPath = $this->getXpath();

        if (!$xPath) {
            return false;
        }

        $nodeRelKeywords = $xPath->query("//div[@class='co_relation_srh']/ul/li/a");

        if ($nodeRelKeywords->length > 0) {
            foreach ($nodeRelKeywords as $nodeRelKeyword) {
                $this->data['relKeywords'][] = trim($nodeRelKeyword->nodeValue);
            }
        }

        $nodeTotalItems = $xPath->query("//a[@class='_productSet_total']");

        if ($nodeTotalItems->length > 0) {
            $this->data['totalItems'] = filter_var(trim($nodeTotalItems[0]->nodeValue), FILTER_SANITIZE_NUMBER_INT);
        }

        $nodeItems = $xPath->query("//ul[@class='goods_list']/li");

        if ($nodeItems->length > 0) {
            $totalSalesPrice = 0;
            foreach ($nodeItems as $nodeItem) {
                $nodeTitles = $xPath->query("descendant::a[@class='tit']", $nodeItem);
                if ($nodeTitles->length > 0) {
                    $this->data['titles'][] = trim($nodeTitles[0]->nodeValue);
                }

                $nodePrices = $xPath->query("descendant::span[@class='price']/em/span", $nodeItem);
                if ($nodePrices->length > 0) {
                    $nodePrice = $nodePrices[($nodePrices->length - 1)];
                    $this->data['prices'][] = filter_var(trim($nodePrice->nodeValue), FILTER_SANITIZE_NUMBER_INT);
                }

                $nodeSells = $xPath->query("descendant::span[@class='etc']/span[contains(., '구매건수')]", $nodeItem);
                if ($nodeSells->length > 0) {
                    $this->data['sells'][] = filter_var(trim($nodeSells[0]->nodeValue), FILTER_SANITIZE_NUMBER_INT);

                    if ($nodePrices->length > 0) {
                        $this->data['sellsPrice'][] = filter_var(trim($nodePrice->nodeValue), FILTER_SANITIZE_NUMBER_INT) * filter_var(trim($nodeSells[0]->nodeValue), FILTER_SANITIZE_NUMBER_INT);
                    }
                }

                $nodeReviews = $xPath->query("descendant::span[@class='etc']/a[contains(., '리뷰')]|descendant::span[@class='etc']/span[contains(., '리뷰')]", $nodeItem);
                if ($nodeReviews->length > 0) {
                    $this->data['reviews'][] = filter_var(trim($nodeReviews[0]->nodeValue), FILTER_SANITIZE_NUMBER_INT);
                }
            }
        }

        if (!empty($this->data['sellsPrice'])) {
            $this->data['avgSellPrice'] = ceil(array_sum($this->data['sellsPrice']) / count($this->data['sellsPrice']));
        }

        if (!empty($this->data['prices'])) {
            $this->data['lowPrice'] = min($this->data['prices']);
            $this->data['avgPrice'] = ceil(array_sum($this->data['prices']) / count($this->data['prices']));
            $this->data['highPrice'] = max($this->data['prices']);
        }

        if (!empty($this->data['sells'])) {
            $this->data['lowSell'] = min($this->data['sells']);
            $this->data['avgSell'] = ceil(array_sum($this->data['sells']) / count($this->data['sells']));
            $this->data['highSell'] = max($this->data['sells']);
        }

        if (!empty($this->data['reviews'])) {
            $this->data['lowReview'] = min($this->data['reviews']);
            $this->data['avgReview'] = ceil(array_sum($this->data['reviews']) / count($this->data['reviews']));
            $this->data['highReview'] = max($this->data['reviews']);
        }

        if (!empty($this->data['titles'])) {
            foreach ($this->data['titles'] as $title) {
                $title = strip_tags($title);
                $title = str_replace(array('_', ',', '/', '(', ')', '[', ']'), ' ', $title);
                $titles = explode(' ', $title);

                if (!empty($titles)) {
                    $this->data['hotKeywords'] = array_merge($this->data['hotKeywords'], $titles);
                }
            }

            if (!empty($this->data['hotKeywords'])) {
                $this->data['hotKeywords'] = array_count_values($this->data['hotKeywords']);
                unset($this->data['hotKeywords']['']);
                arsort($this->data['hotKeywords']);
                $this->data['hotKeywords'] = array_slice($this->data['hotKeywords'], 0, 10);
            }
        }

        unset($this->data['titles']);
        unset($this->data['prices']);
        unset($this->data['sells']);
        unset($this->data['sells_price']);
        unset($this->data['reviews']);

        return $this->data;
    }
}