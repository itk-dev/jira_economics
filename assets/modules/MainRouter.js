import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { Router, Route, browserHistory, hashHistory, IndexRoute } from 'react-router';
import App from './App';
import HomePage from '../pages/HomePage';
import Billing from '../pages/Billing';
import Statistics from '../pages/Statistics';
import SprintPlanning from '../pages/SprintPlanning';
import ProjectBilling from '../pages/ProjectBilling';

export default class MainRouter extends Component {
  constructor() {
    super();
    this.state = {
      navOpenState: {
        isOpen: true,
        width: 304,
      }
    }
  }

  getChildContext () {
    return {
      navOpenState: this.state.navOpenState,
    };
  }

  appWithPersistentNav = () => (props) => (
    <App
      onNavResize={this.onNavResize}
      {...props}
    />
  );

  onNavResize = (navOpenState) => {
    this.setState({
      navOpenState,
    });
  };

  render() {
    return (
      <Router history={browserHistory}>
        <Route path="/" component={this.appWithPersistentNav()}>
          <IndexRoute component={HomePage} />
          <Route path="project/:projectId" component={ProjectBilling} />
          <Route path="billing" component={Billing} />
          <Route path="statistics" component={Statistics} />
          <Route path="planning" component={SprintPlanning} />
        </Route>
      </Router>
    );
  }
}

MainRouter.childContextTypes = {
  navOpenState: PropTypes.object
};
