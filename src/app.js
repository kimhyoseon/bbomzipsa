import './css/app.scss';
import React from 'react';
import ReactDOM from 'react-dom';

const html = (
  <div className="container">
    
    <div className="starter-template">
      <h1>Hello, world</h1>          

      <button type="button" className="btn btn-primary btn-lg" data-toggle="modal" data-target="#myModal">
        modal
      </button>    

      <div className="modal fade" id="myModal" tabIndex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div className="modal-dialog" role="document">
          <div className="modal-content">
            <div className="modal-header">
              <button type="button" className="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              <h4 className="modal-title" id="myModalLabel">Modal title</h4>
            </div>
            <div className="modal-body">
              Modal success~!
            </div>
            <div className="modal-footer">
              <button type="button" className="btn btn-default" data-dismiss="modal">Close</button>
              <button type="button" className="btn btn-primary">Save changes</button>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
);

ReactDOM.render(
  html,
  document.getElementById('root')
);