import PropTypes from 'prop-types';
import React, { Component } from 'react';
import Page from '@atlaskit/page';
import '@atlaskit/css-reset';
import Navigation from '../components/Navigation';

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
        <Page
          navigationWidth={this.context.navOpenState.width}
          navigation={<Navigation />}
        >
          {this.props.children}
        </Page>
      </div>
    );
  }
}
