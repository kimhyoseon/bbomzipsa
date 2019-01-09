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
        <footer class="footer mt-auto py-3">
          <div class="container">
            <span class="text-muted">Copyright&copy;<a href="/"> ppomzipsa.com</a></span>
            <a href="mailto:gytjs4473@gmail.com" target="_blank" class="btn btn-sm btn-secondary float-right">문의 및 제안</a>
          </div>
        </footer>
      </div>
    );
  }
}

export default Main;