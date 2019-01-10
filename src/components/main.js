import $ from 'jquery';
import React from 'react';
import Header from './header';
import SearchForm from './search_form';
import Common from './common';

class Main extends React.Component {
  constructor() {
    super();

    window.$ = $;
  }

  render() {
    return (
      <div className="d-flex flex-column h-100">
        <Header />
        <div className="container-fluid">
          <SearchForm />
        </div>
        <Common />
        <footer className="footer mt-auto py-3">
          <div className="container">
            <span className="text-muted">Copyright&copy;<a href="/"> ppomzipsa.com</a></span>
            <a href="mailto:gytjs4473@gmail.com" target="_blank" className="btn btn-sm btn-secondary float-right">문의 및 제안</a>
            <nav aria-label="breadcrumb" className="footer-naver-guide float-right d-none d-sm-block">
              <ol className="breadcrumb">
                <li className="breadcrumb-item"><a href="https://manage.searchad.naver.com/front" target="_blank">키워드광고</a></li>
                <li className="breadcrumb-item"><a href="https://datalab.naver.com/shoppingInsight/sCategory.naver">쇼핑인사이트</a></li>
              </ol>
            </nav>
          </div>
        </footer>
      </div>
    );
  }
}

export default Main;