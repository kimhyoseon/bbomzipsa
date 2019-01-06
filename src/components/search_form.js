import $ from 'jquery';
import React from 'react';
import SearchResult from './search_result';

class SearchForm extends React.Component {
    constructor() {
      super();
      this.state = { 
        isSearching: false,               
        keywords: [],
        items: [],
        relkeyword: null
      };  
      
      this.handleSubmit = this.handleSubmit.bind(this);
    }
  
    handleSubmit(event) {     
      if (this.state.isSearching) return false;      
      
      let keyword = this.keywordInput.value;
      if (!keyword) {      
        this.keywordInput.focus();
        event.preventDefault();
        return false;
      }

      if (this.state.keywords.indexOf(keyword) !== -1) {
        this.keywordInput.value = '';
        this.keywordInput.focus();
        event.preventDefault();
        return false;
      }
      
      this.keywordInput.value = keyword.replace(/\s/g, '');
      
      $('#btn-search-submit').addClass('disabled');
  
      $.ajax({
        type: "POST",
        //url: "//localhost/api/keyword.php",
        url: "//ppomzipsa.com/api/keyword.php",
        data: {
          keyword: keyword
        },
        success: $.proxy(function (result) {
          if (result) {            
            this.state.items.push({
              'keywordstool': result.keywordstool,
              'shopping': result.shopping,
            });
            this.state.relkeyword = result.relkeyword;
            this.state.keywords.push(keyword);
            this.setState({
              items: this.state.items,
              relkeyword: this.state.relkeyword,
              keywords: this.state.keywords
            });
            
            this.keywordInput.value = '';
            console.log(this.state);
          }          
        }, this),
        complete: $.proxy(function() {
          $('#btn-search-submit').removeClass('disabled');
          this.state.isSearching = false;
        }, this)
      });    
      event.preventDefault();
    }  
  
    render() {
      return (
        <div>
          <div>
            <form onSubmit={this.handleSubmit}>
              <div className="form-group row">
                <label htmlFor="keyword" className="col-sm-2 col-form-label" >쇼핑키워드</label>
                <div className="col-sm-10">
                  <input type="text" className="form-control" id="keyword" placeholder="키워드를 입력해주세요." ref={(input) => { this.keywordInput = input; }} value={this.state.value} onChange={this.handleChange}/>          
                </div>        
              </div>      
              <div className="form-group row">
                <div className="text-center">
                  <button id="btn-search-submit" type="submit" className="btn btn-primary">
                      <span className="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span>
                    검색
                  </button>
                </div>        
              </div>
            </form>          
          </div>
          <div>
            <SearchResult result={this.state} submit={this.handleSubmit.bind(this)}/>
          </div>
        </div>
      );
    }
  }

  export default SearchForm;