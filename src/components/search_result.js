import $ from 'jquery';
import React from 'react';
import tablesort from 'tablesort';

class SearchResult extends React.Component {
	constructor(props) {
    super(props);		
    
    this.handleSubmit = this.handleSubmit.bind(this);    
	}

	keywordRow() {							
		const listItems = this.props.result.items.map((item) => {            
      let keywordstool = item.keywordstool;
      let shopping = item.shopping;
      let qc = Math.ceil(keywordstool.monthlyMobileQcCnt + keywordstool.monthlyPcQcCnt) || 0;
      let clk = Math.ceil(keywordstool.monthlyAveMobileClkCnt + keywordstool.monthlyAvePcClkCnt) || 0;
      let sale = (qc == 0 || clk == 0) ? 0 : (qc / shopping.total).toFixed(5);      
      let clkr = (qc == 0 || clk == 0) ? 0 : (qc / clk / 100).toFixed(2);

      this.tableSort.refresh();

      return (<tr key={keywordstool.relKeyword}>
				<td className="text-center">{keywordstool.relKeyword}</td>
        <td className="text-right">{this.numberWithCommas(shopping.total)}</td>
				<td className="text-right">{this.numberWithCommas(qc)}</td>
        <td className="text-right">{sale}</td>
				<td className="text-right">{this.numberWithCommas(clk)}</td>
				<td className="text-right">{clkr}%</td>
        <td className="text-right">{this.numberWithCommas(shopping.lprice)}원</td>
        <td className="text-right">{this.numberWithCommas(shopping.hprice)}원</td>
				<td className="text-center">{keywordstool.compIdx}</td>
				<td className="text-right">{keywordstool.plAvgDepth}</td>
        <td>{Object.keys(shopping.hotkeyword).map(function(k){return k + '('+shopping.hotkeyword[k]+')'}).join(", ")}</td>
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

  componentDidMount() {        
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
          <table id="table-search-keyword" className="table table-sm" ref={(elem) => { this.tableSearch = elem; }}>
            <thead>
                <tr>
                  <th scope="col" className="text-center">키워드</th>
                  <th scope="col" className="text-center" data-sort-method='number'>총 상품수</th>              
                  <th scope="col" className="text-center" data-sort-method='number'>월간 검색</th>
                  <th scope="col" className="text-center" data-sort-method='number'>판매지수</th>
                  <th scope="col" className="text-center" data-sort-method='number'>월간 클릭</th>
                  <th scope="col" className="text-center" data-sort-method='number'>월간 클릭율</th>
                  <th scope="col" className="text-center" data-sort-method='number'>최저가격</th>
                  <th scope="col" className="text-center" data-sort-method='number'>최고가격</th>              
                  <th scope="col" className="text-center">경쟁정도</th>
                  <th scope="col" className="text-center">월평균광고</th>
                  <th scope="col" className="text-center">인기키워드</th>
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