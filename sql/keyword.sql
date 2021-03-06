drop table keywords;

create table keywords (
    id int(11) NOT NULL AUTO_INCREMENT,
    avgPrice int(11) unsigned default 0,
    avgReview int(11) unsigned default 0,
    avgSell int(11) unsigned default 0,
    avgSellPrice int(15) unsigned default 0,
    category int(11) unsigned default 0,
    categoryTexts varchar(255),
    compIdx char(4),
    highPrice int(11) unsigned default 0,
    highReview int(11) unsigned default 0,
    highSell int(11) unsigned default 0,
    hotKeywords varchar(255),
    keyword varchar(100),
    lowPrice int(11) unsigned default 0,
    lowReview int(11) unsigned default 0,
    lowSell int(11) unsigned default 0,
    monthlyAveMobileClkCnt int(11) unsigned default 0,
    monthlyAveMobileCtr decimal(10,2) unsigned default 0,
    monthlyAvePcClkCnt int(11) unsigned default 0,
    monthlyAvePcCtr decimal(10,2) unsigned default 0,
    monthlyMobileQcCnt int(11) unsigned default 0,
    monthlyPcQcCnt int(11) unsigned default 0,
    monthlyQcCnt int(11) unsigned default 0,
    plAvgDepth tinyint(11) unsigned default 0,
    raceIndex decimal(10,4) unsigned default 0,
    relKeywords text,
    saleIndex int(11) unsigned default 0,
    season tinyint(11) unsigned default 0,
    seasonMonth tinyint(11) unsigned default 0,
    totalItems int(11) unsigned default 0,
    trends varchar(100),
    modDate datetime,
    regDate datetime default current_timestamp,
    PRIMARY KEY (id),
    UNIQUE INDEX keyword (keyword),
    INDEX raceIndex (raceIndex),
    INDEX saleIndex (saleIndex),
    INDEX category (category),
    INDEX seasonMonth (seasonMonth)
);

ALTER TABLE keywords AUTO_INCREMENT=1;
SET @COUNT = 0;
UPDATE keywords SET id = @COUNT:=@COUNT+1;

ALTER TABLE keywords MODIFY COLUMN avgSellPrice int(20) unsigned default 0;
ALTER TABLE keywords MODIFY COLUMN raceIndex decimal(10,4) unsigned default 0;
UPDATE keywords SET modDate='2020-01-01 00:00:00' WHERE totalItems=0;

drop table keywords_rel;

create table keywords_rel (
    keywords_id int(11) unsigned default 0,
    keywords_rel_id int(11) unsigned default 0,
    INDEX keywords_id (keywords_id)
);

ALTER TABLE keywords ADD COLUMN raceIndexChange decimal(10,4) signed default 0 AFTER raceIndex;
ALTER TABLE keywords ADD COLUMN ignored tinyint(1) unsigned default 0 AFTER totalItems;
ALTER TABLE keywords ADD COLUMN hasMainShoppingSearch tinyint(1) unsigned default 0 AFTER ignored;


-- 상품별 재고
drop table smartstore_stock;
create table smartstore_stock (
    id int(11) NOT NULL AUTO_INCREMENT comment 'ID',
    category varchar(50) NOT NULL comment '분류(상품,포장등)',
    title varchar(100) NOT NULL comment '상품명',
    opt varchar(100) NOT NULL default '' comment '옵션',
    amount int(11) default 0 comment '재고',
    period smallint NOT NULL default 0 comment '입고기간(일)',
    PRIMARY KEY (id),
    INDEX title (title)
);

create table smartstore_stock_history (
    id int(11) NOT NULL comment 'ID',
    amount int(11) default 0 comment '재고',
    regDate datetime default current_timestamp,
);

INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '땅콩볼', '검정', 100, 4);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '땅콩볼', '핑크', 100, 4);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '땅콩볼', '주황', 100, 4);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '땅콩볼', '파랑', 100, 4);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '땅콩볼', '녹색', 100, 4);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '요가링', '핑크색', 100, 15);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '요가링', '파란색', 100, 15);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '요가링', '노란색', 100, 15);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '요가링', '보라색', 100, 15);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '요가링', '녹색', 100, 15);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '요가링', '감귤색', 100, 15);
-- A타입 3켤레 (어두운회색), ABC타입 골고루 3켤레
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '요가양말', 'A타입(어두운회색)', 100, 15);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '요가양말', 'A타입(밝은회색)', 100, 15);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '요가양말', 'A타입(검정)', 100, 15);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '요가양말', 'A타입(보라)', 100, 15);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '요가양말', 'A타입(핑크)', 100, 15);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '요가양말', 'B타입(검정)', 100, 15);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '요가양말', 'B타입(흰색)', 100, 15);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '요가양말', 'C타입(검정)', 100, 15);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '요가양말', 'C타입(흰색)', 100, 15);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '샴푸브러쉬', '', 100, 15);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '바른자세밴드', 'M', 100, 30);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '바른자세밴드', 'L', 100, 30);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '리프팅밴드', 'A타입(검정)', 100, 30);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '리프팅밴드', 'B타입(연핑크)', 100, 30);
-- 미니+다용도 롤러세트
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '마사지롤러', '미니롤러(얼굴전용)', 100, 15);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '마사지롤러', '다용도롤러', 100, 15);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '롤빗', '1호', 100, 15);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '롤빗', '2호', 100, 15);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '롤빗', '3호', 100, 15);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '롤빗', '4호', 100, 15);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '롤빗', '5호', 100, 15);
-- 혼합 3켤레
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '뽀송양말', '검정', 100, 4);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '뽀송양말', '회색', 100, 4);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '뽀송양말', '흰색', 100, 4);
-- 검정 2개, 검정 + 회색
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '수면안대', '검정', 100, 4);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '수면안대', '회색', 100, 4);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '수면안대', '핑크', 100, 4);
-- 벚꽃색 + 실리콘마개
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '가정용대야', '벚꽃색', 100, 4);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '가정용대야', '실리콘마개', 100, 4);
-- XL(95)/골고루 4개, XL(95)/스킨 4개
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '심리스의류', 'L(90)/흰색', 100, 4);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '심리스의류', 'L(90)/검정', 100, 4);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '심리스의류', 'L(90)/스킨', 100, 4);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '심리스의류', 'L(90)/밀크티', 100, 4);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '심리스의류', 'XL(95)/흰색', 100, 4);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '심리스의류', 'XL(95)/검정', 100, 4);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '심리스의류', 'XL(95)/스킨', 100, 4);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '심리스의류', 'XL(95)/밀크티', 100, 4);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '심리스의류', 'XXL(100)/흰색', 100, 4);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '심리스의류', 'XXL(100)/검정', 100, 4);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '심리스의류', 'XXL(100)/스킨', 100, 4);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '심리스의류', 'XXL(100)/밀크티', 100, 4);
-- 20개입 (1박스), 10개입, 5개입
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '천연약쑥', '', 100, 4);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '아치보호대', '', 100, 4);
-- 스트레칭 + 발가락링 세트
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '발가락교정기', '발가락링 교정기', 100, 15);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '발가락교정기', '스트레칭 교정기', 100, 15);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '압박양말', 'S', 100, 15);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '압박양말', 'M', 100, 15);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '압박양말', 'L', 100, 15);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '귀지압패치', '1+1 (1200매)', 100, 4);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('부자재', '귀혈자리지도', '', 100, 4);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '압진기', '압진기', 100, 4);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '실리콘패치', '원형', 100, 4);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '머리끈세트', '블랙/one size', 100, 4);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '머리끈세트', '네이비/one size', 100, 4);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '머리끈세트', '스카이블루/one size', 100, 4);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '머리끈세트', '핑크/one size', 100, 4);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '아이스롤러', '아이스롤러', 100, 15);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '타투스티커', 'A', 3, 15);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '타투스티커', 'C', 5, 15);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '타투스티커', 'D', 5, 15);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '타투스티커', 'E', 5, 15);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '타투스티커', 'F', 5, 15);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '타투스티커', 'G', 4, 15);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '타투스티커', 'H', 4, 15);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '타투스티커', 'I', 4, 15);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '타투스티커', 'N', 5, 15);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '타투스티커', 'P', 5, 15);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '타투스티커', 'R', 5, 15);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '타투스티커', 'SN', 5, 15);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '타투스티커', 'U', 4, 15);
-- M 2개(양무릎세트할인)
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '무릎보호대', 'M', 100, 15);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '무릎보호대', 'L', 100, 15);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '무릎보호대', 'XL', 100, 15);
-- S(양발세트할인)
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '발목보호대', 'S(좌)', 100, 15);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '발목보호대', 'S(우)', 100, 15);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '발목보호대', 'M(좌)', 100, 15);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '발목보호대', 'M(우)', 100, 15);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '발목보호대', 'L(좌)', 100, 15);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '발목보호대', 'L(우)', 100, 15);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '발목보호대', 'XL(좌)', 100, 15);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '발목보호대', 'XL(우)', 100, 15);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '발케어세트', '', 100, 15);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('부자재', '박스(C-197)', '', 100, 4);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('부자재', '박스(C-31)', '', 100, 4);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('부자재', '더스트백(530)', '', 100, 30);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('부자재', '더스트백(320)', '', 100, 30);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('부자재', '더스트백(250)', '', 100, 30);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('부자재', '더스트백(150)', '', 100, 30);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('부자재', '태그(120)', '', 1000, 7);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('부자재', '태그(170)', '', 1000, 7);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('부자재', '안내장', '', 1000, 7);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('부자재', '안내장밴드', '', 1000, 7);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '가정용혈압계', '기본형', 5, 4);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '가정용혈압계', '고급형', 5, 4);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '가정용혈압계', '아답터', 5, 4);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '짐볼', '55cm (블루)', 0, 4);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '짐볼', '55cm (와일드핑크)', 120, 4);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '짐볼', '55cm (더스티블루)', 0, 4);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '짐볼', '65cm (레드)', 0, 4);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '짐볼', '65cm (블루)', 0, 4);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '짐볼', '65cm (라임크림)', 0, 4);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '짐볼', '65cm (라일락)', 0, 4);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '짐볼', '65cm (라벤더)', 120, 4);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '짐볼', '75cm (블루)', 0, 4);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '짐볼', '75cm (루비와인)', 0, 4);
INSERT INTO smartstore_stock (category, title, opt, amount, period) VALUES ('상품', '짐볼', '75cm (무드인디고)', 0, 4);



-- 상품별 판매수
drop table smartstore_order;
create table smartstore_order (
    id int(11) NOT NULL comment '상품ID',
    date char(8) NOT NULL comment '년월일',
    sale_cnt int(11) unsigned default 0 comment '일평균판매수',
    INDEX date (id, date)
);

INSERT INTO smartstore_order (id, date, sale_cnt) VALUES (1, '20200104', 30);
INSERT INTO smartstore_order (id, date, sale_cnt) VALUES (1, '20200105', 40);
INSERT INTO smartstore_order (id, date, sale_cnt) VALUES (1, '20200106', 50);

-- 정성한끼 정기주문 (DROP)
drop table smartstore_order_hanki;
create table smartstore_order_hanki (
    id BIGINT unsigned NOT NULL comment '상품주문번호',
    date char(8) NOT NULL comment '년월일',
    contents TEXT NOT NULL comment '주문데이터(serialize)',
    PRIMARY KEY (id),
    INDEX date (date)
);
delete from smartstore_order_hanki;

-- 정성한끼 발주확인 주문
drop table smartstore_order_hanki_wait;
create table smartstore_order_hanki_wait (
    opt varchar(50) NOT NULL default '' comment '옵션',
    amount smallint NOT NULL default 0 comment '수량'
);

-- 정성한끼 정기주문
drop table smartstore_order_jshk;
create table smartstore_order_jshk (
    id int(11) NOT NULL AUTO_INCREMENT,
    item_order_no BIGINT unsigned NOT NULL default 0 comment '상품주문번호',
    date char(8) NOT NULL comment '수령일',
    menu char(25) NOT NULL default '' comment '세트메뉴',
    name varchar(25) NOT NULL default '' comment '수취인명',
    quantity tinyint NOT NULL default 1 comment '수량',
    tel1 varchar(15) NOT NULL default '' comment '수취인연락처1',
    tel2 varchar(15) NOT NULL default '' comment '수취인연락처2',
    address varchar(255) NOT NULL default '' comment '배송지',
    message varchar(255) NOT NULL default '' comment '배송메세지',
    deposit varchar(100) NOT NULL default '' comment '입금정보',
    PRIMARY KEY (id),
    INDEX date (date),
    INDEX item_order_no (item_order_no)
);

DELETE FROM smartstore_order_jshk;
INSERT INTO smartstore_order_jshk (item_order_no, address, date) VALUES ('2020122292521761', '수정된 배송지', '20210128');
INSERT INTO smartstore_order_jshk (date, menu, name, quantity, tel1, address, message) VALUES ('20210128', '2인세트(6개)', '홍길동', '1', '010-9878-6713', '경기도 화성시 남양읍 남양로862번길 16 (남양읍) 화성남양뉴타운A5행복주택, 602동 602호', '노크하지마세요.');


drop table yoona_apt;
create table yoona_apt (
    id int(11) NOT NULL AUTO_INCREMENT,
    year_build char(4) NOT NULL default '' comment '건축년도',
    name_apt varchar(100) NOT NULL default '' comment '아파트',
    sigoongoo varchar(20) NOT NULL default '' comment '시군구',
    upmyeondong varchar(20) NOT NULL default '' comment '읍면동',
    code_beopjeongdong char(10) NOT NULL default '' comment '법정동코드(10자리)',
    code_sigoongoo char(5) NOT NULL default '' comment '시군구코드',
    code_eupmyeondong char(5) NOT NULL default '' comment '읍면동코드',
    number_apt varchar(20) NOT NULL default '' comment '아파트번호',
    road varchar(20) NOT NULL default '' comment '도로명',
    code_road varchar(10) NOT NULL default '' comment '도로명코드',
    rank_index decimal(3,2) NOT NULL default 1.00 comment '지역별 아파트를 평당가격별로 가산점 점수',
    PRIMARY KEY (id),
    INDEX code_sigoongoo (code_sigoongoo),
    INDEX name_apt (name_apt)
);

ALTER TABLE yoona_apt MODIFY rank_index decimal(3,2) NOT NULL default 1.00 comment '지역별 아파트를 평당가격별로 가산점 점수';

drop table yoona_apt_deal;
create table yoona_apt_deal (
    yoona_apt_id int(11) NOT NULL comment '아파트ID',
    date char(6) NOT NULL comment '년월',
    year smallint NOT NULL comment '년',
    month tinyint NOT NULL comment '월',
    size smallint NOT NULL comment '크기(제곱미터)',
    sale_count smallint NOT NULL default 0 comment '매매거래횟수',
    sale_price decimal(10) NOT NULL default 0 comment '평균매매가',
    sale_price_max decimal(10) NOT NULL default 0 comment '상위매매가',
    sale_price_min decimal(10) NOT NULL default 0 comment '하위매매가',
    jeonse_count smallint NOT NULL default 0 comment '전세거래횟수',
    jeonse_price decimal(10) NOT NULL default 0 comment '평균전세가',
    jeonse_price_max decimal(10) NOT NULL default 0 comment '상위전세가',
    jeonse_price_min decimal(10) NOT NULL default 0 comment '하위전세가',
    INDEX yoona_apt_id (yoona_apt_id),
    INDEX yoona_apt_date (yoona_apt_id, date, size)
);

ALTER TABLE yoona_apt_deal MODIFY jeonse_count smallint;
ALTER TABLE yoona_apt_deal MODIFY sale_count smallint;

-- 지역 아파트 특정 기간 내 투자수익률순
SELECT ya.id, ya.name_apt, yad.size, ya.year_build, ya.upmyeondong, ROUND(((bf.beforesale * ya.rank_index) - bf.beforesale) / (bf.beforesale - bf.beforejeonse) * 100) AS calc_sooicper, ROUND((af.aftersale - bf.beforesale) / (bf.beforesale - bf.beforejeonse) * 100) AS sooicper, ROUND((ya.rank_index - 1) * 100) AS calc_incper, ROUND((af.aftersale - bf.beforesale) / bf.beforesale * 100) AS incper, ROUND(af.aftersale - bf.beforesale) AS sooic, ROUND(bf.beforesale / yad.size * 3.3) AS priceper, ROUND(bf.beforesale - bf.beforejeonse) AS gap, ROUND(bf.salecount + bf.jeonsecount) AS countsum, ROUND(bf.beforesale) AS beforesale, ROUND(bf.beforejeonse) AS beforejeonse, ROUND(af.aftersale) AS aftersale, ROUND(af.afterjeonse) AS afterjeonse FROM yoona_apt_deal AS yad
LEFT JOIN (SELECT yoona_apt_id, size, AVG(sale_price) AS beforesale, AVG(jeonse_price) AS beforejeonse, AVG(sale_count) AS salecount, AVG(jeonse_count) AS jeonsecount FROM yoona_apt_deal WHERE date >= '201606' AND date <= '201612' AND sale_count > 0 AND jeonse_count > 0 GROUP BY yoona_apt_id, size) AS bf on bf.yoona_apt_id = yad.yoona_apt_id AND bf.size = yad.size
LEFT JOIN (SELECT yoona_apt_id, size, MAX(sale_price) AS aftersale, MAX(jeonse_price) AS afterjeonse FROM yoona_apt_deal WHERE date >= '201909' AND date <= '201912' GROUP BY yoona_apt_id, size) AS af on af.yoona_apt_id = yad.yoona_apt_id AND af.size = yad.size
LEFT JOIN yoona_apt AS ya on ya.id = yad.yoona_apt_id
WHERE yad.yoona_apt_id IN (SELECT id FROM yoona_apt WHERE code_sigoongoo = '30170') AND bf.beforesale > 0 AND bf.beforejeonse > 0 AND af.aftersale > 0
GROUP BY yad.yoona_apt_id, yad.size
ORDER BY calc_sooicper DESC
LIMIT 100;



-- 투자 유망한 아파트 포인트 순 정렬
SELECT ya.id, ya.name_apt, yad.size, ya.year_build, ya.upmyeondong, ROUND(((bf.beforesale * ya.rank_index) - bf.beforesale) / (bf.beforesale - bf.beforejeonse) * 100) AS calc_sooicper, ROUND((ya.rank_index - 1) * 100) AS calc_incper, ROUND(bf.beforesale / yad.size * 3.3) AS priceper, ROUND(bf.beforesale - bf.beforejeonse) AS gap, ROUND(bf.salecount + bf.jeonsecount) AS countsum, ROUND(bf.beforesale) AS beforesale, ROUND(bf.beforejeonse) AS beforejeonse FROM yoona_apt_deal AS yad
LEFT JOIN (SELECT yoona_apt_id, size, MAX(sale_price) AS beforesale, MAX(jeonse_price) AS beforejeonse, AVG(sale_count) AS salecount, AVG(jeonse_count) AS jeonsecount FROM yoona_apt_deal WHERE date >= '201905' AND date <= '201911' AND sale_count > 0 AND jeonse_count > 0 GROUP BY yoona_apt_id, size) AS bf on bf.yoona_apt_id = yad.yoona_apt_id AND bf.size = yad.size
LEFT JOIN yoona_apt AS ya on ya.id = yad.yoona_apt_id
WHERE yad.yoona_apt_id IN (SELECT id FROM yoona_apt WHERE code_sigoongoo = '31140') AND bf.beforesale > 0 AND bf.beforejeonse > 0 AND yad.size > 50 AND yad.size < 100
GROUP BY yad.yoona_apt_id, yad.size
ORDER BY calc_sooicper DESC
LIMIT 100;




-- 수지
SELECT ya.id, ya.name_apt, yad.size, ya.year_build, ya.upmyeondong, ROUND(((bf.beforesale * ya.rank_index) - bf.beforesale) / (bf.beforesale - bf.beforejeonse) * 100) AS calc_sooicper, ROUND((af.aftersale - bf.beforesale) / (bf.beforesale - bf.beforejeonse) * 100) AS sooicper, ROUND((ya.rank_index - 1) * 100) AS calc_incper, ROUND((af.aftersale - bf.beforesale) / bf.beforesale * 100) AS incper, ROUND(af.aftersale - bf.beforesale) AS sooic, ROUND(bf.beforesale / yad.size * 3.3) AS priceper, ROUND(bf.beforesale - bf.beforejeonse) AS gap, ROUND(bf.salecount + bf.jeonsecount) AS countsum, ROUND(bf.beforesale) AS beforesale, ROUND(bf.beforejeonse) AS beforejeonse, ROUND(af.aftersale) AS aftersale, ROUND(af.afterjeonse) AS afterjeonse FROM yoona_apt_deal AS yad
LEFT JOIN (SELECT yoona_apt_id, size, AVG(sale_price) AS beforesale, AVG(jeonse_price) AS beforejeonse, AVG(sale_count) AS salecount, AVG(jeonse_count) AS jeonsecount FROM yoona_apt_deal WHERE date >= '201307' AND date <= '201312' AND sale_count > 0 AND jeonse_count > 0 GROUP BY yoona_apt_id, size) AS bf on bf.yoona_apt_id = yad.yoona_apt_id AND bf.size = yad.size
LEFT JOIN (SELECT yoona_apt_id, size, MAX(sale_price) AS aftersale, MAX(jeonse_price) AS afterjeonse FROM yoona_apt_deal WHERE date >= '201906' AND date <= '201912' GROUP BY yoona_apt_id, size) AS af on af.yoona_apt_id = yad.yoona_apt_id AND af.size = yad.size
LEFT JOIN yoona_apt AS ya on ya.id = yad.yoona_apt_id
WHERE yad.yoona_apt_id IN (SELECT id FROM yoona_apt WHERE code_sigoongoo = '41465') AND bf.beforesale > 0 AND bf.beforejeonse > 0 AND af.aftersale > 0
GROUP BY yad.yoona_apt_id, yad.size
ORDER BY calc_sooicper DESC
LIMIT 100;


-- 연관검색어 삭제
select id from keywords where keyword='방충망청소';
delete from keywords_rel where keywords_id='83422' and keywords_rel_id in (select id from keywords where keyword='');
delete from keywords_rel where keywords_id='83422' and keywords_rel_id in (select id from keywords where keyword='');
