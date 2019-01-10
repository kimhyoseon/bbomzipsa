import $ from 'jquery';
import React from 'react';
import tablesort from 'tablesort';
import icoNShopping from '../images/ico-navershopping.png';

class SearchResult extends React.Component {
	constructor(props) {
    super(props);

    this.handleSubmit = this.handleSubmit.bind(this);
	}

	keywordRow() {
		const listItems = this.props.result.items.map((item) => {
      let searchAmount = Math.ceil(item.monthlyMobileQcCnt + item.monthlyPcQcCnt) || 0;
      let raceIndex = (searchAmount == 0 || item.totalItems == 0) ? 0 : (item.totalItems / searchAmount).toFixed(3);
      let ttipReview = '최저:' + this.numberWithCommas(item.lowReview) + '건, 최대:'+ this.numberWithCommas(item.highReview) + '건';
      let ttipPrice = '최저:' + this.numberWithCommas(item.lowPrice) + '원, 최대:'+ this.numberWithCommas(item.highPrice) + '원';
      let ttipSell = '최저:' + this.numberWithCommas(item.lowSell) + '건, 최대:'+ this.numberWithCommas(item.highSell) + '건';
      let hotKeyword = [];
      let hotKeywordFull = Object.keys(item.hotKeywords).map(function(k, i){
        if (i < 3) hotKeyword.push(k + '('+item.hotKeywords[k]+')');
        return k + '('+item.hotKeywords[k]+')'
      }).join(", ");
      let linkNaverShopping = 'https://search.shopping.naver.com/search/all.nhn?cat_id=&frm=NVSHATC&query=' + item.relKeyword;
      let trendsGraph = item.trends.map((trend, i) => {
        trend = (trend == 0) ? 0 : Math.ceil(trend / 5);
        trend = 20 - trend;
        return i * 2 + ' ' + trend;
      }).join(", ");
      let trendsText = item.trends.map((trend, i) => {
        return (i + 1) + '월(' + trend + ')';
      }).join(", ");

      let raceBattery = 'float-left fas fa-battery-empty';
      if (raceIndex < 0.05) raceBattery = 'float-left fas fa-battery-full';
      else if (raceIndex < 0.5) raceBattery = 'float-left fas fa-battery-three-quarters';
      else if (raceIndex < 1) raceBattery = 'float-left fas fa-battery-half';
      else if (raceIndex < 5) raceBattery = 'float-left fas fa-battery-quarter';

      const season = function() {
        if (!item.season) return '';
        else if (item.season == 1) return (<span className="box-etc float-left"><span className="badge badge-secondary spring">봄</span></span>)
        else if (item.season == 2) return (<span className="box-etc float-left"><span className="badge badge-secondary summer">여름</span></span>)
        else if (item.season == 3) return (<span className="box-etc float-left"><span className="badge badge-secondary fall">가을</span></span>)
        else if (item.season == 4) return (<span className="box-etc float-left"><span className="badge badge-secondary winter">겨울</span></span>)
      }();

      this.tableSort.refresh();

      return (
        <tr key={item.relKeyword}>
        <td className="align-middle text-center" data-toggle="tooltip" data-placement="right" title={trendsText}><span className="fa"><svg className="chart-mini"><polyline  fill="none" stroke="#00c73c" strokeWidth="1" points={trendsGraph} /></svg></span></td>
        <td className="align-middle text-center">{item.relKeyword}</td>
        <td className="align-middle text-right">{this.numberWithCommas(item.totalItems)}개</td>
        <td className="align-middle text-right">{this.numberWithCommas(searchAmount)}건</td>
        <td className="align-middle text-right"><i className={raceBattery}></i>{raceIndex}</td>
				<td className="align-middle text-right d-none d-sm-block">{this.numberWithCommas(item.avgSellPrice)}원</td>
        <td className="align-middle text-right d-none d-sm-block" data-toggle="tooltip" data-placement="right" title={ttipSell}>{this.numberWithCommas(item.avgSell)}건</td>
        <td className="align-middle text-right d-none d-sm-block" data-toggle="tooltip" data-placement="right" title={ttipReview}>{this.numberWithCommas(item.avgReview)}건</td>
        <td className="align-middle text-right d-none d-sm-block" data-toggle="tooltip" data-placement="right" title={ttipPrice}>{this.numberWithCommas(item.avgPrice)}원</td>
        <td className="align-middle d-none d-sm-block" data-toggle="tooltip" data-placement="right" title={hotKeywordFull}><small>{hotKeyword.join(', ')}</small></td>
        <td className="align-middle d-none d-sm-block">
          <span className="box-etc float-left"><a href={linkNaverShopping} target="_blank" title="네이버쇼핑 바로가기"><img src={icoNShopping} width="20" height="20" className="d-inline-block align-middle"/></a></span>
          {season}
        </td>
			</tr>);
      }
    );

		return (
			<tbody>{listItems}</tbody>
		);
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

  numberWithCommas(num) {
      return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
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
        <div className="rel-keywords-list">
          {this.relKeywordList()}
        </div>
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
                    <i data-toggle="tooltip" data-placement="right" title="낮을수록 좋은 키워드 (등록상품수/월검색수)" className="fas fa-question-circle"></i>
                  </th>
                  <th scope="col" className="align-middle text-center d-none d-sm-block" data-sort-method='number'>
                    평균매출액
                    <i data-toggle="tooltip" data-placement="right" title="네이버쇼핑 1페이지 기준, 1개 소셜판매자당 매출추정액" className="fas fa-question-circle"></i>
                  </th>
                  <th scope="col" className="align-middle text-center d-none d-sm-block" data-sort-method='number'>
                    평균구매
                    <i data-toggle="tooltip" data-placement="right" title="네이버쇼핑 1페이지 기준, 1개 스토어당 등록된 평균 구매 수" className="fas fa-question-circle"></i>
                  </th>
                  <th scope="col" className="align-middle text-center d-none d-sm-block" data-sort-method='number'>
                    평균리뷰
                    <i data-toggle="tooltip" data-placement="right" title="네이버쇼핑 1페이지 기준, 1개 스토어당 등록된 평균 리뷰 수" className="fas fa-question-circle"></i>
                  </th>
                  <th scope="col" className="align-middle text-center d-none d-sm-block" data-sort-method='number'>
                    평균상품가격
                    <i data-toggle="tooltip" data-placement="right" title="네이버쇼핑 1페이지 기준, 평균 상품가격" className="fas fa-question-circle"></i>
                  </th>
                  <th scope="col" className="align-middle text-center d-none d-sm-block">
                    인기키워드
                    <i data-toggle="tooltip" data-placement="right" title="네이버쇼핑 1페이지 기준, 상품명에 많이 언급된 단어 (횟수)" className="fas fa-question-circle"></i>
                  </th>
                  <th scope="col" className="align-middle text-center d-none d-sm-block no-sort" data-sort-method='none'>정보</th>
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