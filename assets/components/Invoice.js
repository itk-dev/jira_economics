import React, { Component } from 'react';
import connect from 'react-redux/es/connect/connect';
import ContentWrapper from '../components/ContentWrapper';
import PageTitle from '../components/PageTitle';
import { Link } from 'react-router';
import store from '../redux/store';
import { fetchInvoice, fetchInvoiceEntries, editInvoice } from '../redux/actions';
import PropTypes from 'prop-types';
import Button, {ButtonAppearances} from '@atlaskit/button';
import Form, {Field, FormFooter} from '@atlaskit/form';
import Spinner from '@atlaskit/spinner';
import TextField from '@atlaskit/field-text';
import Moment from 'react-moment';
import 'moment-timezone';

class Invoice extends Component {
  componentDidMount() {
    store.dispatch(fetchInvoice(this.props.params.invoiceId));
    store.dispatch(fetchInvoiceEntries(this.props.params.invoiceId));
  }
  handleEditSubmit = (e) => {
    const id = this.props.params.invoiceId;
    const name = e.invoiceName;
    const invoiceData = {
      id,
      name
    }
    store.dispatch(editInvoice(invoiceData));
  }
  handleRecordSubmit = (e) => {
    const id = this.props.params.invoiceId;
    const recorded = "true";
    const invoiceData = {
      id,
      recorded
    }
    store.dispatch(editInvoice(invoiceData));
  }
  render () {
    if (this.props.selectedInvoice.name) {
      return (
        <ContentWrapper>
          <PageTitle>Invoice</PageTitle>
          <div>ProjectID: {this.props.params.projectId}</div>
          <div>InvoiceID: {this.props.params.invoiceId}</div>
          <div>InvoiceName: {this.props.selectedInvoice.name}</div>
          <div>InvoiceRecorded: {this.props.selectedInvoice.recorded}</div>
          <div>InvoiceCreated: <Moment format="YYYY-MM-DD HH:mm">{this.props.createdAt}</Moment></div>
          <div>
            <Form onSubmit={this.handleEditSubmit}>
              {({ formProps }) => (
                <form {...formProps} name="submit-edit-form">
                  <Field name="invoiceName" defaultValue={this.props.selectedInvoice.name} label="Enter invoice name" isRequired>
                    {({ fieldProps}) => <TextField {...fieldProps} />}
                  </Field>
                  <Button type="submit" appearance="primary">Submit</Button>
                </form>
              )}
            </Form>
          </div>
          <div>
            <Form onSubmit={this.handleRecordSubmit}>
              {({ formProps }) => (
                <form {...formProps} name="submit-recorded-form">
                  <Button type="submit" appearance="primary">Record invoice</Button>
                </form>
              )}
            </Form>
          </div>
          <div>Invoice entries:</div>
          {this.props.invoiceEntries && this.props.invoiceEntries.map((item) =>
            <div key={item.id}><Link to={`/project/${this.props.params.projectId}/${this.props.params.invoiceId}/${item.id}`}>Link til {item.name}</Link></div>
          )}
        </ContentWrapper>
      );
    }
    else {
      return (<ContentWrapper><Spinner size="large"/></ContentWrapper>);
    }
  }
}

Invoice.propTypes = {
  selectedInvoice: PropTypes.object,
  createdAt: PropTypes.string,
  invoiceEntries: PropTypes.array
};

const mapStateToProps = state => {
  let createdAt = state.selectedInvoice.selectedInvoice.created ? state.selectedInvoice.selectedInvoice.created.date : "";

  return {
    selectedInvoice: state.selectedInvoice.selectedInvoice,
    createdAt: createdAt,
    invoiceEntries: state.invoiceEntries.invoiceEntries
  };
};

export default connect(
  mapStateToProps
)(Invoice);
