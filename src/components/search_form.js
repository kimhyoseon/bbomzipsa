import React from 'react';
import SearchResult from './search_result';
import Common from './common';

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
      if (this.state.isSearching) {
        event.preventDefault();
        return false;
      }

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
        success: $.proxy(function (result, textStatus) {
          console.log(result);
          if (!result || textStatus != 'success') {
            Layer.toast(textStatus);
          }

          this.state.items.push(result);
          this.state.keywords.push(keyword);
          if (result.relKeywords && result.relKeywords.length > 0) this.state.relkeyword = result.relKeywords;
          this.setState({
            items: this.state.items,
            relkeyword: this.state.relkeyword,
            keywords: this.state.keywords
          });

          this.keywordInput.value = '';
        }, this),
        error: $.proxy(function(result, textStatus, jqXHR) {
          Layer.toast('통신 오류입니다. 잠시 후 다시 시도해주세요.');
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
              <div className="form-group row justify-content-md-center">
                <div className="col-md-6">
                  <div id="custom-search-input">
                    <div className="input-group">
                        <input type="text" id="keyword" ref={(input) => { this.keywordInput = input; }} value={this.state.value} className="form-control input-lg" placeholder="키워드를 입력해주세요." />
                        <span className="input-group-btn">
                          <button id="btn-search-submit" type="submit" className="btn btn-info btn-lg">
                            <span className="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            <i className="fas fa-search"></i>
                          </button>
                        </span>
                    </div>
                  </div>
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