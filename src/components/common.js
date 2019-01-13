import React from 'react';

class Layer extends React.Component {
  constructor() {
    super();

    window.Layer = this;

    this.state = {
      message: null
    }

    this.toast = this.toast.bind(this);
  }

  toast(message) {
    if (!message) return false;
    this.state.message = message;

    this.setState({
      message: this.state.message
    });

    $(this.elemToast).toast('show');
  }

  render() {
    return (      
      <div aria-live="polite" aria-atomic="true" className=" d-flex justify-content-center align-items-center">
        <div ref={(elem) => { this.elemToast = elem; }} className="toast" role="alert" aria-live="assertive" aria-atomic="true" data-delay="1000" >
          <div className="toast-body">
            {this.state.message}
          </div>
        </div>
      </div>
    )
  }
}

export default Layer;


