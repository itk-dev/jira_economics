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
    const {dispatch} = this.props;
    this.props.history.push(`/project/${this.props.match.params.projectId}/${this.props.match.params.invoiceId}/invoice_entry/jira_issues`);
  }
  render() {
    if (this.props.selectedIssues) {
      return (
        <ContentWrapper>
          <PageTitle>Tilføj oplysninger til fakturalinje fra Jira</PageTitle>
          <div>{Object.values(this.props.selectedIssues.selectedIssues).length + " issue(s) valgt"}</div>
          <div>
            <form id="submitForm" onSubmit={this.handleSelectJiraIssues}>
              <button type="submit" className="btn btn-primary" id="submit">Rediger valg</button>
            </form>
          </div>
        </ContentWrapper>
      );
    }
    else {
      return (
      <ContentWrapper>
        <div class="spinner-border" style={{width: '3rem', height: '3rem', role: 'status'}}>
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