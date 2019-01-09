import React from 'react';
import logo from '../images/logo-w-200.png';

class Header extends React.Component {
  render() {
    return (
      <div>
        <nav className="navbar navbar-dark bg-dark mb-4">
          <a className="navbar-brand" href="#">
            <img src={logo} width="40" height="40" className="d-inline-block align-top header-logo"/>
            뽐집사
          </a>
        </nav>
      </div>
    )
  }
}

export default Header;