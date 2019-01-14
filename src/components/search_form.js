import React from 'react';
import SearchResult from './search_result';
import categoryData from '../../data/category.json';

class SearchForm extends React.Component {
    constructor() {
      super();

      //this.urlApi = "//localhost/api";
      this.urlApi = "//ppomzipsa.com/api";

      this.state = {
        page: 1,
        isSearching: false,
        keywords: [],
        items: [],
        relkeyword: null,
        modeSearch: null,
        category: [categoryData.categoryDepthText[0]],
        categoryId: 0
      };

      this.handleSubmit = this.handleSubmit.bind(this);
      this.searchCategory = this.searchCategory.bind(this);
      this.changeCategory = this.changeCategory.bind(this);
      this.trackScrolling = this.trackScrolling.bind(this);
    }

    componentDidMount() {
      document.addEventListener('scroll', this.trackScrolling);
      this.search();
    }

    componentWillUnmount() {
      document.removeEventListener('scroll', this.trackScrolling);
    }

    trackScrolling() {
      if (this.state.page == 1) return false;

      if($(window).scrollTop() + $(window).height() == $(document).height()) {
        Layer.toast('목록을 가져오는 중입니다. 잠시만 기다려 주세요.');
        this.search();
      }
    };

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

      if (this.state.modeSearch == 'c') {
        this.state.items = [];
        this.state.page = 1;
        this.state.modeSearch = null;
        this.state.category = [categoryData.categoryDepthText[0]];
        this.state.categoryId = 0;
        $('select[data-depth=0]').prop('selectedIndex', 0);
        this.setState(this.state);
      }

      this.state.isSearching = true;
      this.keywordInput.value = keyword.replace(/\s/g, '');

      $('#btn-search-submit, .btn-search-categoty').addClass('disabled');

      $.ajax({
        type: "POST",
        url: this.urlApi + '/keyword.php',
        data: {
          keyword: this.keywordInput.value
        },
        success: $.proxy(function (result, textStatus) {
          if (!result || textStatus != 'success') {
            Layer.toast(textStatus);
            return false;
          }

          console.log(result);

          this.state.items.push(result);
          this.state.keywords.push(keyword);
          if (result.relKeywords) this.state.relkeyword = result.relKeywords.split(',');

          this.setState(this.state);

          this.keywordInput.value = '';

          if (mobileCheck) this.keywordInput.blur();
        }, this),
        error: $.proxy(function(result, textStatus, jqXHR) {
          Layer.toast('통신 오류입니다. 잠시 후 다시 시도해주세요.');
        }, this),
        complete: $.proxy(function() {
          $('#btn-search-submit, .btn-search-categoty').removeClass('disabled');
          this.state.isSearching = false;
        }, this)
      });

      event.preventDefault();
    }

    getAllcategoriIds(categoryId, categories){
      if (categoryId == 0) return 0;
      if (!categories) categories = [];

      if (!categoryData['categoryDepthId'][categoryId]) {
        categories.push(categoryId);
      } else {
        for (let i = 0; i < categoryData['categoryDepthId'][categoryId].length; i++) {
          this.getAllcategoriIds(categoryData['categoryDepthId'][categoryId][i], categories);
        }
      }

      return categories;
    }

    searchCategory(event) {
      if (event) event.preventDefault();

      this.state.items = [];
      this.state.page = 1;
      this.state.relkeyword = null;
      this.state.modeSearch = 'c';
      this.setState(this.state);

      this.search();
    }

    search() {
      if (this.state.isSearching) return false;

      let categories = this.getAllcategoriIds(this.state.categoryId);
      if (categories == 0) categories = null;

      // console.log(this.state.categoryId)
      // console.log(categories);
      // return false;

      this.state.isSearching = true;
      $('#btn-search-submit, .btn-search-categoty').addClass('disabled');

      $.ajax({
        type: "POST",
        url: this.urlApi + '/list.php',
        data: {
          page: this.state.page,
          category: categories
        },
        success: $.proxy(function (result, textStatus) {
          if (!result || textStatus != 'success') {
            Layer.toast(textStatus);
            return false;
          }

          console.log(result);

          for (let i = 0; i < result.length; i++) {
            this.state.items.push(result[i]);
          }

          this.state.page++;

          this.setState(this.state);
        }, this),
        error: $.proxy(function(result, textStatus, jqXHR) {
          Layer.toast('통신 오류입니다. 잠시 후 다시 시도해주세요.');
        }, this),
        complete: $.proxy(function() {
          $('#btn-search-submit, .btn-search-categoty').removeClass('disabled');
          this.state.isSearching = false;
        }, this)
      });
    }

    category() {
      if (!this.state.category) return false;
      return this.state.category.map((categoryDepth, i) => {
        const option = Object.keys(categoryDepth).map((categoryId) => {
          return (<option key={"option-" + categoryId} value={categoryId}>{categoryDepth[categoryId]}</option>);
        });

        return (
          <div key={"category-" + i} className="col-sm-2 mb-1">
            <select className="form-control" data-depth={i} onChange={this.changeCategory}>
              {option}
            </select>
          </div>
        )
      });
    }

    changeCategory(event) {
      event.preventDefault();

      const categoryIdSelected = event.target.value;
      const depth = $(event.target).data('depth') + 1;

      this.state.category = this.state.category.slice(0, depth);
      this.state.categoryId = categoryIdSelected;

      if (categoryIdSelected == 0 && depth > 1) {
        this.state.categoryId = $(event.target).parent().prev().children().val();
      }

      if (categoryIdSelected != 0 && categoryData.categoryDepthText[categoryIdSelected]) {
        this.state.category[depth] = categoryData.categoryDepthText[categoryIdSelected];
      }

      this.setState(this.state);
    }

    render() {
      return (
        <div>
          <div className="box-form mb-4">
            <form onSubmit={this.handleSubmit} className="mb-0">
              <div className="form-group row">
                <label className="col-md-1 col-form-label d-none d-sm-block">카테고리</label>
                {this.category()}
                <div className="col mb-1">
                  <button type="button" className="btn btn-secondary btn-search-categoty" onClick={this.searchCategory}>
                    <span className="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    카테고리 검색
                  </button>
                </div>
              </div>
              <div className="form-group row mb-0">
                <label htmlFor="keyword" className="col-md-1 col-form-label d-none d-sm-block">키워드검색</label>
                <div className="col-md-4">
                  <div id="custom-search-input">
                    <div className="input-group">
                        <input type="text" id="keyword" autoComplete="off" ref={(input) => { this.keywordInput = input; }} value={this.state.value} className="form-control input-lg" placeholder="키워드를 입력해주세요." />
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