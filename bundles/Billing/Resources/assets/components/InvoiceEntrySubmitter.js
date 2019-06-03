import React, { Component } from 'react';
import connect from 'react-redux/es/connect/connect';
import ContentWrapper from './ContentWrapper';
import PageTitle from './PageTitle';
import store from '../redux/store';
import reducers from '../redux/reducers';
import PropTypes from 'prop-types';
import rest from '../redux/utils/rest';
import { push } from 'react-router-redux';

const $ = require('jquery');

export class InvoiceEntrySubmitter extends Component {
  constructor(props) {
    super(props);
    this.handleSelectJiraIssues = this.handleSelectJiraIssues.bind(this);
  }
  componentDidMount() {
    const { dispatch } = this.props;
  }
  handleSubmitInvoiceEntry = (e) => {
    const { dispatch } = this.props;
    // @TODO: an InvoiceEntry should have one or more JiraIssues
    const invoiceEntryData = {
      id,
      name
    }
    dispatch(rest.actions.createInvoiceEntry(), {
      body: JSON.stringify(invoiceEntryData)
    });
  }
  handleSelectJiraIssues = (event) => {
    event.preventDefault();
    const { dispatch } = this.props;
    this.props.history.push(`/project/${this.props.match.params.projectId}/${this.props.match.params.invoiceId}/invoice_entry/jira_issues`);
  }
  getTimeSpent() {
    if (this.props.selectedIssues == undefined) {
      return 0;
    }
    let timeSum = 0;
    this.props.selectedIssues.selectedIssues.forEach(selectedIssue => {
      timeSum += selectedIssue.timeSpent;
    });
    return timeSum;
  }
  handleCreateSubmit = (event) => {
    event.preventDefault();
    const { dispatch } = this.props;
    const invoiceId = this.props.match.params.invoiceId;
    // @TODO: should an invoice entry even have a name?
    const name = "dummy";
    // @TODO: get these values from the event instead
    const description = $('#invoice-entry-description').val();
    const account = $('#invoice-entry-account').val();
    const product = $('#invoice-entry-product').val();
    let jiraIssueIds = [];
    this.props.selectedIssues.selectedIssues.forEach(selectedIssue => {
      jiraIssueIds.push(selectedIssue.id);
    });
    const invoiceEntryData = {
      invoiceId,
      name,
      description,
      account,
      product,
      jiraIssueIds
    };
    dispatch(rest.actions.createInvoiceEntry({}, {
      body: JSON.stringify(invoiceEntryData)
    }));
    // @TODO: check that a new invoiceEntry was successfully created before navigating to invoice page
    // @TODO: consider showing a modal dialog to confirm invoiceEntry creation
    this.props.history.push(`/project/${this.props.match.params.projectId}/${this.props.match.params.invoiceId}`);
  };
  handleCancelSubmit = (event) => {
    event.preventDefault();
    this.props.history.push(`/project/${this.props.match.params.projectId}/${this.props.match.params.invoiceId}`);
  };
  render() {
    if (this.props.selectedIssues) {
      return (
        <ContentWrapper>
          <PageTitle>Tilføj oplysninger til fakturalinje fra Jira</PageTitle>
          <div>{Object.values(this.props.selectedIssues.selectedIssues).length + " issue(s) valgt"}</div>
          <div>{"Total timer valgt: " + this.getTimeSpent()}</div>
          <div>
            <form id="submitForm" onSubmit={this.handleSelectJiraIssues}>
              <button type="submit" className="btn btn-primary" id="submit">Rediger valg</button>
            </form>
          </div>
          <div>
            <form id="create-invoice-entry-form">
              <label htmlFor="kontonr">
                Kontonr.
              </label>
              <div>
                <select className="browser-default custom-select" defaultValue="Vælg PSP" id="invoice-entry-account">
                  <option value="Vælg PSP" hidden>Vælg PSP</option>
                  <option value="foo">foo</option>
                  <option value="bar">bar</option>
                  <option value="baz">baz</option>
                </select>
              </div>
              <div>
                <label htmlFor="vare">
                  Vare
                </label>
                <input
                  type="text"
                  name="enterVarenr"
                  className="form-control"
                  id="invoice-entry-product"
                  aria-describedby="enterVarenr"
                  placeholder="Varenavn">
                </input>
                <label htmlFor="beskrivelse">
                  Beskrivelse
                </label>
                <input
                  type="text"
                  name="beskrivelse"
                  className="form-control"
                  id="invoice-entry-description"
                  aria-describedby="enterBeskrivelse"
                  placeholder="Varebeskrivelse">
                </input>
                <label htmlFor="antal">
                  Timer
                </label>
                <input
                  type="text"
                  name="hoursSpent"
                  className="form-control"
                  id="invoice-entry-hours-spent"
                  aria-describedby="enterHoursSpent"
                  placeholder={this.getTimeSpent()}
                  readOnly>
                </input>
                <label htmlFor="beskrivelse">
                  Stk. pris
                </label>
                <input
                  type="text"
                  name="unitPrice"
                  className="form-control"
                  id="invoice-entry-unit-price"
                  aria-describedby="enterUnitPrice"
                  placeholder="42"
                  readOnly>
                </input>
              </div>
            </form>
            <form onSubmit={this.handleCreateSubmit}>
              <button
                type="submit"
                className="btn btn-primary"
                id="create-invoice-entry">Overfør til faktura
              </button>
            </form>
            <form onSubmit={this.handleCancelSubmit}>
              <button
                type="submit"
                className="btn btn-danger"
                id="cancel">Annuller
              </button>
            </form>
          </div>
        </ContentWrapper>
      );
    }
    else {
      return (
        <ContentWrapper>
          <div class="spinner-border" style={{ width: '3rem', height: '3rem', role: 'status' }}>
            <span class="sr-only">Loading...</span>
          </div>
        </ContentWrapper>
      );
    }
  }
}

InvoiceEntrySubmitter.propTypes = {
  invoiceEntrySubmitter: PropTypes.object,
  dispatch: PropTypes.func.isRequired
};

const mapStateToProps = state => {
  return {
    invoiceEntrySubmitter: state.invoiceEntrySubmitter,
    selectedIssues: state.selectedIssues
  };
};

export default connect(
  mapStateToProps
)(InvoiceEntrySubmitter);