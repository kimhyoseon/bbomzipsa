import React from 'react';
import logo from '../images/logo-w-200.png';

class Header extends React.Component {
  render() {
    return (
      <div id="header">
        <nav className="navbar navbar-dark bg-dark mb-4">
          <a className="navbar-brand" href="/">
            <img src={logo} width="40" height="40" className="d-inline-block align-middle header-logo"/>
            뽐집사
            <h6 className="d-inline ml-2 text-color-naver">스마트스토어 키워드분석</h6>
          </a>
        </nav>
      </div>
    )
  }
}

export default Header;