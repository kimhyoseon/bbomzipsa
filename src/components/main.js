import React from 'react';
import SearchForm from './search_form';

class Main extends React.Component {
  render() {
    return (
      <div className="container-fluid">    
        <div className="starter-template">        
          <SearchForm />          
        </div>
      </div>  
    );
  }
}

export default Main;