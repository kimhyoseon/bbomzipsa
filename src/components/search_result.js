import $ from 'jquery';
import React from 'react';
import tablesort from 'tablesort';
import iconNaverShopping from '../images/ico-navershopping.png';
import iconGoogle from '../images/ico-google.png';
import icon1688 from '../images/ico-1688.png';

class SearchResult extends React.Component {
	constructor(props) {
    super(props);

    this.state = {
      openResult: false,
      isSearching: false,
    };

    this.handleSubmit = this.handleSubmit.bind(this);
    this.openResult = this.openResult.bind(this);
    this.link1688 = this.link1688.bind(this);
    this.selectKeyword = this.selectKeyword.bind(this);
    this.handleSubmitIgnore = this.handleSubmitIgnore.bind(this);
    this.searchKeywordDetail = this.searchKeywordDetail.bind(this);
    this.refreshItem = this.refreshItem.bind(this);
	}

	keywordRow() {
		const listItems = this.props.result.items.map((item) => {
      // console.log(item);

      let ttipPrice = '최저:' + this.numberWithCommas(item.lowPrice) + '원, 최대:'+ this.numberWithCommas(item.highPrice) + '원';
      let ttipRaceIndex = '증감:' + item.raceIndexChange;
      let ttipSaleIndex = '평균구매수:' + this.numberWithCommas(item.avgSell) + '건, 평균리뷰수:'+ this.numberWithCommas(item.avgReview) + '건, 평균매출액:'+ this.numberWithCommas(item.avgSellPrice) + '원';
      let hotKeyword = ''
      if (item.hotKeywords) {
        hotKeyword = item.hotKeywords.slice(0, item.hotKeywords.split(',', 3).join(',').length);
      }
      let trendsGraph = '';
      let trendsText = '';
      if (item.trends) {
        if (typeof item.trends == 'string') item.trends = item.trends.split(',');
        trendsGraph = item.trends.map((trend, i) => {
          trend = (trend == 0) ? 0 : Math.ceil(trend / 5);
          trend = 20 - trend;
          return i * 2 + ' ' + trend;
        }).join(", ");
        trendsText = item.trends.map((trend, i) => {
          return (i + 1) + '월(' + trend + ')';
        }).join(", ");
      }

      let raceBattery = 'fas fa-battery-empty';
      if (item.raceIndex > 0 && item.raceIndex < 0.05) raceBattery = 'fas fa-battery-full';
      else if (item.raceIndex < 0.5) raceBattery = 'fas fa-battery-three-quarters';
      else if (item.raceIndex < 1) raceBattery = 'fas fa-battery-half';
      else if (item.raceIndex < 5) raceBattery = 'fas fa-battery-quarter';

      let saleBattery = 'fas fa-battery-empty';
      if (item.highReview > 3000) saleBattery = 'fas fa-battery-full';
      else if (item.highReview > 1000) saleBattery = 'fas fa-battery-three-quarters';
      else if (item.highReview > 500) saleBattery = 'fas fa-battery-half';
      else if (item.highReview > 300) saleBattery = 'fas fa-battery-quarter';

      const device  = function() {
        if (item.monthlyQcCnt == 0) return '';
        let mobQcRatio = Math.ceil(item.monthlyMobileQcCnt / (item.monthlyMobileQcCnt + item.monthlyPcQcCnt) * 100);
        let deviceTooltip = '';
        if (mobQcRatio > 70) return (<span className="box-etc float-left" data-toggle="tooltip" data-placement="right" title={"모바일 검색비율: " + mobQcRatio + "%"}><i className="fas fa-mobile-alt"></i></span>)
        if (mobQcRatio < 30) return (<span className="box-etc float-left" data-toggle="tooltip" data-placement="right" title={"PC 검색비율: " + (100 -mobQcRatio) + "%"}><i className="fas fa-desktop"></i></span>)
      }();

      const season = function() {
        if (!item.season) return '';
        else if (item.season == 1) return (<span className="box-etc float-left"><span className="badge badge-secondary spring">봄</span></span>)
        else if (item.season == 2) return (<span className="box-etc float-left"><span className="badge badge-secondary summer">여름</span></span>)
        else if (item.season == 3) return (<span className="box-etc float-left"><span className="badge badge-secondary fall">가을</span></span>)
        else if (item.season == 4) return (<span className="box-etc float-left"><span className="badge badge-secondary winter">겨울</span></span>)
      }();

      const detail = (item.hasDetail != 1) ? '' : (<span className="box-etc float-left"><button onClick={() => this.searchKeywordDetail(item.id)} className="btn badge badge-secondary winter">연관키워드</button></span>);
      const mainShoppingSearch = (item.hasMainShoppingSearch != 1) ? '' : (<span className="box-etc float-left" data-toggle="tooltip" data-placement="right" title="네이버메인 쇼핑검색 키워드"><i className="fas fa-home"></i></span>);
      const refresh = (<span className="box-etc float-left"><a href="#" onClick={(e) => this.refreshItem(e, item.keyword)}><i className="fas fa-sync"></i></a></span>);
      const ranking = (item.ranking && item.ranking > -1) ? (<span className="box-etc float-left"><span className="badge badge-secondary">{item.ranking}</span></span>) : '';

      let category = ''
      if (item.categoryTexts) {
        category = item.categoryTexts.split(',').map((category, i) => {
          let nextArrow = (i == 0) ? '' : (<i className="fas fa-caret-right text-muted"></i>);
          return (<span key={category} className="d-inline">{nextArrow}{category}</span>);
        });
      }

      let linkNaverShopping = (!mobileCheck) ?'https://search.shopping.naver.com/search/all.nhn?cat_id=&frm=NVSHATC&query=' + item.keyword : 'https://msearch.shopping.naver.com/search/all?frm=NVSHSRC&cat_id=&pb=true&mall=&query=' + item.keyword;
      let linkGoogleSearch = 'http://www.google.com/search?q=' + item.keyword + '+위탁+사입+도매';

      // let isDefualtIgnoreChecked = (item.ignored == 2) ? false : true;

      this.tableSort.refresh();

      return (
        <tr key={item.keyword}>
        <td className="align-middle text-center" data-toggle="tooltip" data-placement="right" title={trendsText}><span className="fa"><svg className="chart-mini"><polyline  fill="none" stroke="#00c73c" strokeWidth="1" points={trendsGraph} /></svg></span></td>
        <td className="align-middle text-center">
          <label>
            {item.keyword}
            <input type="checkbox" name="id[]" value={item.id} onChange={() => this.selectKeyword(item.id)} /*defaultChecked={isDefualtIgnoreChecked}*/ />
          </label>
        </td>
        <td className="align-middle text-right">{this.numberWithCommas(item.totalItems)}개</td>
        <td className="align-middle text-right" data-toggle="tooltip" data-placement="right" title={item.modDate}>{this.numberWithCommas(item.monthlyQcCnt)}건</td>
        <td className="align-middle text-right" data-toggle="tooltip" data-placement="right" title={ttipRaceIndex}>{this.numberWithCommas(item.raceIndex)}<i className={raceBattery}></i></td>
        <td className="align-middle text-right" data-toggle="tooltip" data-placement="right" title={ttipSaleIndex}>{this.numberWithCommas(item.highReview)}<i className={saleBattery}></i></td>
        <td className={"align-middle text-right" + this.getOpenResultClass()} data-toggle="tooltip" data-placement="right" title={ttipPrice}>{this.numberWithCommas(item.avgPrice)}원</td>
        <td className={"align-middle" + this.getOpenResultClass()} ><small>{category}</small></td>
        <td className={"align-middle" + this.getOpenResultClass()} data-toggle="tooltip" data-placement="right" title={item.hotKeywords}><small>{hotKeyword}</small></td>
        <td className={"align-middle" + this.getOpenResultClass()}>
          {ranking}
          {mainShoppingSearch}
          {detail}
          {device}
          {season}
          {refresh}
          <span className="box-etc float-left"><a href={linkNaverShopping} target="_blank" title="네이버쇼핑 바로가기"><img src={iconNaverShopping} width="20" height="20" className="d-inline-block align-middle"/></a></span>
          <span className="box-etc float-left"><a href={linkGoogleSearch} target="_blank" title="구글검색 바로가기"><img src={iconGoogle} width="20" height="20" className="d-inline-block align-middle"/></a></span>
          <span className="box-etc float-left"><a href="#" title="1688 바로가기" data-keyword={item.keyword} onClick={this.link1688}><img src={icon1688} width="20" height="20" className="d-inline-block align-middle"/></a></span>
        </td>
			</tr>);
      }
    );

		return (
			<tbody>{listItems}</tbody>
		);
  }

  link1688(event) {
    event.preventDefault();

    let keyword = $(event.target).parent().data('keyword');
    if (!keyword) return false;

    if (this.state.isSearching) {
      return false;
    }

    this.state.isSearching = true;

    $.ajax({
      type: "POST",
      url: this.props.result.urlApi + '/translate.php',
      data: {
        keyword: keyword
      },
      success: $.proxy(function (result, textStatus) {
        // console.log(result);

        if (!result || textStatus != 'success') {
          Layer.toast(textStatus);
          return false;
        }

        if (!result.translatedText) {
          Layer.toast('번역에 실패했습니다.');
          return false;
        }

        window.open("https://s.1688.com/selloffer/offer_search.htm?keywords=" + result.translatedText);
      }, this),
      error: $.proxy(function(result, textStatus, jqXHR) {
        Layer.toast('통신 오류입니다. 잠시 후 다시 시도해주세요.');
      }, this),
      complete: $.proxy(function() {
        this.state.isSearching = false;
      }, this)
    });
  }

  getOpenResultClass() {
     return (!this.state.openResult) ? " d-none d-sm-block" : ""
  }

  selectKeyword(id) {
    var index = this.props.result.itemsIds.indexOf(id);
    if (index !== -1) {
      this.props.result.itemsIds.splice(index, 1);
    } else {
      this.props.result.itemsIds.push(id);
    }
    this.setState(this.state);
  }

  extraFunc() {
    if (this.props.result.itemsIds.length == 0) return false;

		return (
      <div>
        <nav aria-label="breadcrumb">
          <ol className="breadcrumb">
            <li key="ignore" className="breadcrumb-item">
              <button onClick={this.handleSubmitIgnore} className="btn btn-warning">{this.props.result.itemsIds.length}개 키워드 제외하기</button>
            </li>
          </ol>
        </nav>
      </div>
		);
  }

  handleSubmitIgnore() {
    if (this.props.result.itemsIds.length == 0) {
      Layer.toast('키워드를 선택해주세요.');
      return false;
    }

    var careIds = [];
    $("input[name='id[]']:not(:checked)").each(function(){
      careIds.push(parseInt($(this).val()));
    });

    $('#btn-search-submit, .btn-search-categoty').addClass('disabled');

    // console.log(this.props.result.itemsIds);
    // console.log(careIds);

    $.ajax({
      type: "POST",
      url: this.props.result.urlApi + '/ignore.php',
      data: {
        ids: this.props.result.itemsIds,
        // idsCared: careIds
      },
      success: $.proxy(function (result, textStatus) {
        if (!result || textStatus != 'success') {
          Layer.toast(textStatus);
          return false;
        }

        // console.log(result);

        for (let index = 0; index < this.props.result.itemsIds.length; index++) {
          $('input[name="id[]"][value=' + this.props.result.itemsIds[index] + ']').closest('tr').hide();
        }

        this.props.result.itemsIds = [];
        this.setState(this.state);
      }, this),
      error: $.proxy(function(result, textStatus, jqXHR) {
        Layer.toast('통신 오류입니다. 잠시 후 다시 시도해주세요.');
      }, this),
      complete: $.proxy(function() {
        $('#btn-search-submit, .btn-search-categoty').removeClass('disabled');
      }, this)
    });
  }

  searchKeywordDetail(id) {
    if (!id) return false;

    this.props.result.page = 1;
    this.props.result.relkeyword = null;
    this.props.result.modeSearch = 'd';
    this.props.result.detailId = id;

    this.props.search();
  }

  refreshItem(e, keyword) {
    if (e) e.preventDefault();
    if (!keyword) return false;

    $.ajax({
      type: "POST",
      url: this.props.result.urlApi + '/keyword.php',
      data: {
        keyword: keyword
      },
      success: $.proxy(function (result, textStatus) {
        if (!result || textStatus != 'success') {
          Layer.toast(textStatus);
          return false;
        }

        for (let index = 0; index < this.props.result.items.length; index++) {
          if (this.props.result.items[index].keyword == result.keyword) {
            this.props.result.items[index] = result;
            this.setState(this.state);
            return true;
          }
        }
      }, this),
      error: $.proxy(function(result, textStatus, jqXHR) {
        Layer.toast('통신 오류입니다. 잠시 후 다시 시도해주세요.');
      }, this),
      complete: $.proxy(function() {
        $('#btn-search-submit, .btn-search-categoty').removeClass('disabled');
      }, this)
    });
  }

  relKeywordList() {
    if (!this.props.result.relkeyword) return false;
		const listRelKeyword = this.props.result.relkeyword.map((item) => {
      return (<li key={item}  className="breadcrumb-item">
        <a href="#" onClick={this.handleSubmit}>{item}</a>
			</li>);
      }
		);
		return (
      <div>
        <h5>쇼핑 연관</h5>
        <nav aria-label="breadcrumb">
          <ol className="breadcrumb">
            {listRelKeyword}
          </ol>
        </nav>
      </div>
		);
  }

  handleSubmit(event) {
    document.getElementById('keyword').value = event.target.textContent;
    this.props.submit(event);
    event.preventDefault();
  }

  openResult(event) {
    event.preventDefault();

    this.setState({openResult: !this.state.openResult});
  }

  numberWithCommas(num) {
    let parts = num.toString().split(".");
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    return parts.join(".");
  }

  componentDidUpdate() {
    $('[data-toggle="tooltip"]').tooltip();
  }

  componentDidMount() {
    $('[data-toggle="tooltip"]').tooltip();

    tablesort.extend('number', function(item) {
      return item.match(/^[-+]?[£\x24Û¢´€]?\d+\s*([,\.]\d{0,2})/) || // Prefixed currency
        item.match(/^[-+]?\d+\s*([,\.]\d{0,2})?[£\x24Û¢´€]/) || // Suffixed currency
        item.match(/^[-+]?(\d)*-?([,\.]){0,1}-?(\d)+([E,e][\-+][\d]+)?%?$/); // Number
    }, $.proxy(function(a, b) {
      a = a.replace(/[^\-?0-9.]/g, '');
      b = b.replace(/[^\-?0-9.]/g, '');

      return this.compareNumber(b, a);
    }, this));

    this.tableSort = tablesort(this.tableSearch);
  }

  compareNumber(a, b) {
    a = parseFloat(a);
    b = parseFloat(b);

    a = isNaN(a) ? 0 : a;
    b = isNaN(b) ? 0 : b;

    return a - b;
  }

	render() {
		return (
      <div>
        <div className="extra-func">
          {this.extraFunc()}
        </div>
        <div className="rel-keywords-list">
          {this.relKeywordList()}
        </div>
        <a href="#" className="badge badge-light float-right mb-2 d-block d-sm-none" onClick={this.openResult.bind(this)}>{!this.state.openResult ? '모두보기' : '닫기'}<i className={!this.state.openResult ? 'fas fa-caret-right' : 'fas fa-caret-left'}></i></a>
        <div className="table-responsive">
          <table id="table-search-keyword" className="table" ref={(elem) => { this.tableSearch = elem; }}>
            <thead className="thead-light">
                <tr>
                  <th scope="col" className="align-middle text-center no-sort" data-sort-method='none'>트렌드</th>
                  <th scope="col" className="align-middle text-center">키워드</th>
                  <th scope="col" className="align-middle text-center" data-sort-method='number'>등록상품수</th>
                  <th scope="col" className="align-middle text-center" data-sort-method='number'>
                    월검색수
                    <i data-toggle="tooltip" data-placement="right" title="최근 1달간 네이버에서 키워드를 검색한 숫자" className="fas fa-question-circle"></i>
                  </th>
                  <th scope="col" className="align-middle text-center" data-sort-method='number'>
                    경쟁지수
                    <i data-toggle="tooltip" data-placement="right" title="낮을수록 경쟁이 없는 좋은 키워드입니다. (등록상품수/월검색수)" className="fas fa-question-circle"></i>
                  </th>
                  <th scope="col" className="align-middle text-center" data-sort-method='number'>
                    판매지수
                    <i data-toggle="tooltip" data-placement="right" title="높을수록 판매가 활발한 좋은 키워드 입니다 (리뷰수 + 판매수)" className="fas fa-question-circle"></i>
                  </th>
                  <th scope="col" className={"align-middle text-center" + this.getOpenResultClass()} data-sort-method='number'>
                    평균상품가격
                    <i data-toggle="tooltip" data-placement="right" title="네이버쇼핑 1페이지 기준, 평균 상품가격" className="fas fa-question-circle"></i>
                  </th>
                  <th scope="col" className={"align-middle text-center" + this.getOpenResultClass()}>
                    카테고리
                  </th>
                  <th scope="col" className={"align-middle text-center" + this.getOpenResultClass()}>
                    인기키워드
                    <i data-toggle="tooltip" data-placement="right" title="네이버쇼핑 1페이지 기준, 상품명에 많이 언급된 단어 (횟수)" className="fas fa-question-circle"></i>
                  </th>
                  <th scope="col" className={"align-middle text-center no-sort" + this.getOpenResultClass()} data-sort-method='none'>정보</th>
                </tr>
            </thead>
            {this.keywordRow()}
          </table>
        </div>
      </div>
		);
	}
}

export default SearchResult;