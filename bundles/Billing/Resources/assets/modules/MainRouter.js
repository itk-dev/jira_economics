import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { BrowserRouter as Router, Route, withRouter } from "react-router-dom";
import App from './App';
import HomePage from '../pages/HomePage';
import Customer from '../components/Customer';
import Project from '../components/Project';
import Invoice from '../components/Invoice';
import InvoiceEntry from '../components/InvoiceEntry';
import JiraIssues from '../components/JiraIssues';
import InvoiceEntrySubmitter from '../components/InvoiceEntrySubmitter';
import NewInvoice from '../components/NewInvoice';
import Statistics from '../components/Statistics';

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
      <Router basename={"/billing"}>
        <Route exact path="/" component={HomePage}/>
        <Route exact path="/new" component={NewInvoice}/>
        <Route exact path="/statistics" component={Statistics}/>
        <Route exact path="/project/:projectId" component={Project} />
        <Route exact path="/project/:projectId/:invoiceId" component={Invoice}/>
        <Route exact path="/project/:projectId/:invoiceId/invoice_entry/jira_issues" component={JiraIssues}/>
        <Route exact path="/project/:projectId/:invoiceId/:invoiceEntryId" component={InvoiceEntry}/>
        <Route exact path="/project/:projectId/:invoiceId/submit/invoice_entry" component={InvoiceEntrySubmitter}/>
        <Route exact path="/customer" component={Customer}/>
      </Router>
    );
  }
}

MainRouter.childContextTypes = {
  navOpenState: PropTypes.object
};
