import PropTypes from 'prop-types';
import React, { Component } from 'react';

const $ = require('jquery');
// this "modifies" the jquery module: adding behavior to it
// the bootstrap module doesn't export/return anything
require('bootstrap');

$(document).ready(function() {
    $('[data-toggle="popover"]').popover();
});

export default class App extends Component {
  state = {
    isModalOpen: false,
  };

  static contextTypes = {
    navOpenState: PropTypes.object,
    router: PropTypes.object,
  };

  static propTypes = {
    navOpenState: PropTypes.object,
    onNavResize: PropTypes.func,
  };

  render() {
    return (
      <div>
        {this.props.children}
      </div>
    );
  }
}
