import React, { Component } from 'react';
import connect from 'react-redux/es/connect/connect';
import ContentWrapper from '../components/ContentWrapper';
import PageTitle from '../components/PageTitle';
import store from '../redux/store';
import { fetchInvoiceEntry } from '../redux/actions';
import PropTypes from 'prop-types';
import Button from '@atlaskit/button';
import Form, {Field} from '@atlaskit/form';
import Spinner from '@atlaskit/spinner';
import TextField from '@atlaskit/field-text';
import rest from '../redux/utils/rest';

export class InvoiceEntry extends Component {
  componentDidMount() {
    const {dispatch} = this.props;
    dispatch(rest.actions.getInvoiceEntry({id: `${this.props.params.invoiceEntryId}`}));
  }
  handleEditSubmit = (e) => {
    const {dispatch} = this.props;
    const id = this.props.params.invoiceEntryId;
    const name = e.invoiceEntryName;
    const invoiceEntryData = {
      id,
      name,
    }
    dispatch(rest.actions.updateInvoiceEntry({id: `${this.props.params.invoiceEntryId}`}, {
      body: JSON.stringify(invoiceEntryData)
    }));
  }
  handleDeleteSubmit = (e) => {
    const {dispatch} = this.props;
    dispatch(rest.actions.deleteInvoiceEntry({id: `${this.props.params.invoiceEntryId}`}));
  }
  render () {
    if (this.props.invoiceEntry.data.name) {
      return (
        <ContentWrapper>
          <PageTitle>Invoice Entry</PageTitle>
          <div>ProjectID: {this.props.params.projectId}</div>
          <div>InvoiceID: {this.props.params.invoiceId}</div>
          <div>InvoiceEntryID: {this.props.params.invoiceEntryId}</div>
          <div>InvoiceEntryName: {this.props.invoiceEntry.data.name}</div>
          <div>
            <Form onSubmit={this.handleEditSubmit}>
              {({ formProps }) => (
                <form {...formProps} name="submit-edit-form">
                  <Field name="invoiceEntryName" defaultValue={this.props.invoiceEntry.data.name} label="Enter invoice entry name" isRequired>
                    {({ fieldProps}) => <TextField {...fieldProps} />}
                  </Field>
                  <Button type="submit" appearance="primary">Submit invoice entry name</Button>
                </form>
              )}
            </Form>
          </div>
          <div>
            <Form onSubmit={this.handleDeleteSubmit}>
                {({ formProps }) => (
                  <form {...formProps} name="submit-delete-form">
                    <Button type="submit" appearance="danger">Delete invoice entry</Button>
                  </form>
                )}
            </Form>
          </div>
        </ContentWrapper>
      );
    }
    else {
      return (<ContentWrapper><Spinner size="large"/></ContentWrapper>);
    }
  }
}

InvoiceEntry.propTypes = {
  invoiceEntry: PropTypes.object
};

const mapStateToProps = state => {
  return {
    invoiceEntry: state.invoiceEntry
  };
};

export default connect(
  mapStateToProps
)(InvoiceEntry);
