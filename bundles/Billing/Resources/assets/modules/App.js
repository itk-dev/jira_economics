import PropTypes from 'prop-types';
import React, { Component } from 'react';

require('./../billing.css');

export default class App extends Component {
    state = {
        isModalOpen: false
    };

    static contextTypes = {
        navOpenState: PropTypes.object,
        router: PropTypes.object
    };

    static propTypes = {
        navOpenState: PropTypes.object,
        onNavResize: PropTypes.func
    };

    render () {
        return (
            <div>
                {this.props.children}
            </div>
        );
    }
}
