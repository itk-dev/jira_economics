import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { Router, Route, browserHistory, IndexRoute } from 'react-router';
import App from './App';
import HomePage from '../pages/HomePage';
import Statistics from '../pages/Statistics';
import SprintPlanning from '../pages/SprintPlanning';
import Project from '../components/Project';
import Invoice from '../components/Invoice';
import InvoiceEntry from '../components/InvoiceEntry';
import JiraIssues from '../components/JiraIssues';
import InvoiceEntrySubmitter from '../components/InvoiceEntrySubmitter';

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
        <Route path="/billing" component={this.appWithPersistentNav()}>
          <IndexRoute component={HomePage} />
          <Route path="project/:projectId" component={Project} />
          <Route path="project/:projectId/entry/:invoiceEntryId" component={Project}/>
          <Route path="project/:projectId/:invoiceId" component={Invoice}/>
          <Route path="project/:projectId/:invoiceId/jiraIssues" component={JiraIssues}/>
          <Route path="project/:projectId/:invoiceId/invoice_entry" component={InvoiceEntrySubmitter}/>
          <Route path="project/:projectId/:invoiceId/:invoiceEntryId" component={InvoiceEntry}/>
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
