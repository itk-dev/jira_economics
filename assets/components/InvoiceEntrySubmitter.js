import React, { Component } from 'react';
import connect from 'react-redux/es/connect/connect';
import ContentWrapper from './ContentWrapper';
import PageTitle from './PageTitle';
import store from '../redux/store';
import PropTypes from 'prop-types';
import rest from '../redux/utils/rest';
import { push } from 'react-router-redux';
import Form, { Field } from '@atlaskit/form';
import Spinner from '@atlaskit/spinner';
import Button from '@atlaskit/button';

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
  handleSelectJiraIssues = (e) => {
    const {dispatch} = this.props;
    dispatch(push(`/billing/project/${this.props.params.projectId}/${this.props.params.invoiceId}/jiraIssues`));
  }
  render() {
    // @TODO: adjust boolean expression
    if (true) {
      return (
        <ContentWrapper>
          <PageTitle>Tilføj oplysninger til fakturalinje fra Jira</PageTitle>
          <div>Issues valgt og total timer go here...</div>
          <div>
            <Form onSubmit={this.handleSelectJiraIssues}>
              {({ formProps }) => (
                <form {...formProps} name="submit-select-jira-issues">
                  <Button type="submit" appearance="primary">Rediger valg</Button>
                </form>
              )}
            </Form>
          </div>
        </ContentWrapper>
      );
    }
    else {
      return (<ContentWrapper><Spinner size="large" /></ContentWrapper>);
    }
  }
}

InvoiceEntrySubmitter.propTypes = {
  invoiceEntrySubmitter: PropTypes.object,
  dispatch: PropTypes.func.isRequired
};

const mapStateToProps = state => {
  return {
    invoiceEntrySubmitter: state.invoiceEntrySubmitter
  };
};

export default connect(
  mapStateToProps
)(InvoiceEntrySubmitter);