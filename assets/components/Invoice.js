import React, { Component } from 'react';
import connect from 'react-redux/es/connect/connect';
import ContentWrapper from '../components/ContentWrapper';
import PageTitle from '../components/PageTitle';
import { Link } from 'react-router';
import store from '../redux/store';
import { fetchInvoice, fetchInvoiceEntries, editInvoice } from '../redux/actions';
import PropTypes from 'prop-types';
import Moment from 'react-moment';
import 'moment-timezone';
import rest from '../redux/utils/rest';
import { push } from 'react-router-redux';

const $ = require('jquery');

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
  handleEditSubmit = (event) => {
    event.preventDefault();
    const {dispatch} = this.props;
    const id = this.props.params.invoiceId;
    // @TODO: look into getting this value from the event instead
    const name = $("#edit-invoice-entry-name").val();
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
  handleRecordSubmit = (event) => {
    event.preventDefault();
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
  handleCreateSubmit = (event) => {
    event.preventDefault();
    const {dispatch} = this.props;
    const invoiceId = this.props.params.invoiceId;
    // @TODO: look into getting this value from the event instead
    const name = $("#invoice-entry-name").val();
    const invoiceEntryData = {
      invoiceId,
      name
    }
    dispatch(rest.actions.createInvoiceEntry({}, {
      body: JSON.stringify(invoiceEntryData)
    }));
  }
  handleDeleteSubmit = (event) => {
    event.preventDefault();
    const {dispatch} = this.props;
    dispatch(rest.actions.deleteInvoice({id: `${this.props.params.invoiceId}`}));
    // @TODO: Check that deletion is successful before navigating back to project page
    dispatch(push(`/project/${this.props.params.projectId}`));
  }
  handleAddFromJira = (event) => {
    event.preventDefault();
    const {dispatch} = this.props;
    dispatch(push(`/project/${this.props.params.projectId}/${this.props.params.invoiceId}/jiraIssues`));
  }
  // @TODO: Remove form to create invoiceEntry with only a name
  // @TODO: Handle updating the list of invoiceEntries when a new invoiceEntry is submitted
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
            <form id="submit-edit-form" onSubmit={this.handleEditSubmit}>
              <div id="edit-invoice-entry-form-group">
                <label htmlFor="edit-invoice-entry-name">Enter invoice name</label>
                <input
                  type="text"
                  name="editInvoiceEntryName"
                  className="form-control"
                  id="edit-invoice-entry-name"
                  aria-describedby="editInvoiceEntryName"
                  placeholder="changeme">
                </input>
              </div>
              <button type="submit" className="btn btn-primary" id="edit-invoice-entry">Submit new invoice name</button>
            </form>
          </div>
          <div>
            <form id="submit-recorded-form" onSubmit={this.handleRecordSubmit}>
              <button type="submit" className="btn btn-primary" id="record-invoice">Record invoice</button>
            </form>
          </div>
          <div>
            <form id="delete-form" onSubmit={this.handleDeleteSubmit}>
              <button type="submit" className="btn btn-danger" id="delete">Delete invoice</button>
            </form>
          </div>
          <div>Invoice entries:</div>
          {this.props.invoiceEntries.data.data && this.props.invoiceEntries.data.data.map((item) =>
            <div key={item.id}><Link to={`/project/${this.props.params.projectId}/${this.props.params.invoiceId}/${item.id}`}>Link til {item.name}</Link></div>
          )}
          <div>Create new invoice entry</div>
          <div>
            <form id="submit-invoice-entry-form" onSubmit={this.handleCreateSubmit}>
              <div id="submit-invoice-entry-form-group">
                <label htmlFor="input-new-invoice-entry-name">Enter invoiceEntry name for new invoiceEntry</label>
                <input
                  type="text"
                  name="invoiceEntryName"
                  className="form-control"
                  id="invoice-entry-name"
                  aria-describedby="invoiceEntryName"
                  placeholder="Enter invoiceEntry name">
                </input>
              </div>
              <button type="submit" className="btn btn-primary" id="add-from-jira">Submit new invoice entry</button>
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
