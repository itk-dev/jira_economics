import React, { Component } from 'react';
import connect from 'react-redux/es/connect/connect';
import ContentWrapper from '../components/ContentWrapper';
import PageTitle from '../components/PageTitle';
import { Link } from 'react-router';
import store from '../redux/store';
import { fetchInvoice, fetchInvoiceEntries, editInvoice } from '../redux/actions';
import PropTypes from 'prop-types';
import Button from '@atlaskit/button';
import Form, {Field} from '@atlaskit/form';
import Spinner from '@atlaskit/spinner';
import TextField from '@atlaskit/field-text';
import Moment from 'react-moment';
import 'moment-timezone';
import rest from '../redux/utils/rest';

class Invoice extends Component {
  constructor(props) {
    super(props);

    this.handleRecordSubmit = this.handleRecordSubmit.bind(this);
    this.handleEditSubmit = this.handleEditSubmit.bind(this);
    this.state = { invoiceEntryName: '' };
  }
  componentDidMount() {
    const {dispatch} = this.props;
    dispatch(rest.actions.getInvoice({id: `${this.props.params.invoiceId}`}));
    dispatch(rest.actions.getInvoiceEntries({id: `${this.props.params.invoiceId}`}));
  }
  // @TODO: consider cleaning up redundancy
  handleEditSubmit = (e) => {
    const {dispatch} = this.props;
    const id = this.props.params.invoiceId;
    const name = e.invoiceName;
    const recorded = this.props.invoice.data.recorded;
    const created = this.props.createdAt;
    const invoiceData = {
      id,
      name,
      recorded,
      created
    }
    dispatch(rest.actions.updateInvoice({id: `${this.props.params.invoiceId}`}, {
      body: JSON.stringify(invoiceData)
    }));
  }
  handleRecordSubmit = (e) => {
    const {dispatch} = this.props;
    const id = this.props.params.invoiceId;
    const name = this.props.invoice.data.name;
    const recorded = true;
    const created = this.props.createdAt;
    const invoiceData = {
      id,
      name,
      recorded,
      created
    }
    dispatch(rest.actions.updateInvoice({id: `${this.props.params.invoiceId}`}, {
      body: JSON.stringify(invoiceData)
    }));
  }
  handleCreateSubmit = (e) => {
    const {dispatch} = this.props;
    const invoiceId = this.props.params.invoiceId;
    const name = e.invoiceEntryName;
    const invoiceEntryData = {
      invoiceId,
      name
    }
    dispatch(rest.actions.createInvoiceEntry({}, {
      body: JSON.stringify(invoiceEntryData)
    }));
  }
  handleDeleteSubmit = (e) => {
    const {dispatch} = this.props;
    dispatch(rest.actions.deleteInvoice({id: `${this.props.params.invoiceId}`}));
  }
  render () {
    if (this.props.invoice.data.name) {
      return (
        <ContentWrapper>
          <PageTitle>Invoice</PageTitle>
          <div>ProjectID: {this.props.params.projectId}</div>
          <div>InvoiceID: {this.props.params.invoiceId}</div>
          <div>InvoiceName: {this.props.invoice.data.name}</div>
          <div>InvoiceRecorded: {String(this.props.invoice.data.recorded)}</div>
          <div>InvoiceCreated: <Moment format="YYYY-MM-DD HH:mm">{this.props.createdAt}</Moment></div>
          <div>
            <Form onSubmit={this.handleEditSubmit}>
              {({ formProps }) => (
                <form {...formProps} name="submit-edit-form">
                  <Field name="invoiceName" defaultValue={this.props.invoice.data.name} label="Enter invoice name" isRequired>
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
          {this.props.invoiceEntries.data.data && this.props.invoiceEntries.data.data.map((item) =>
            <div key={item.id}><Link to={`/project/${this.props.params.projectId}/${this.props.params.invoiceId}/${item.id}`}>Link til {item.name}</Link></div>
          )}
          <div>Create new invoice entry</div>
          <div>
            <Form onSubmit={this.handleCreateSubmit}>
                {({ formProps }) => (
                  <form {...formProps} name="submit-create-form">
                    <Field name="invoiceEntryName" defaultValue={this.state.invoiceEntryName} label="Enter invoice entry name for new invoice" isRequired>
                      {({ fieldProps}) => <TextField {...fieldProps} />}
                    </Field>
                    <Button type="submit" appearance="primary">Submit new invoice entry</Button>
                  </form>
                )}
            </Form>
          </div>
          <div>
            <Form onSubmit={this.handleDeleteSubmit}>
                {({ formProps }) => (
                  <form {...formProps} name="submit-delete-form">
                    <Button type="submit" appearance="danger">Delete invoice</Button>
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

Invoice.propTypes = {
  invoice: PropTypes.object,
  createdAt: PropTypes.string,
  invoiceEntries: PropTypes.object,
  dispatch: PropTypes.func.isRequired
};

const mapStateToProps = state => {
  let createdAt = state.invoice.data.created ? state.invoice.data.created.date : "";

  return {
    invoice: state.invoice,
    createdAt: createdAt,
    invoiceEntries: state.invoiceEntries
  };
};

export default connect(
  mapStateToProps
)(Invoice);
