import React, { Component } from 'react';
import connect from 'react-redux/es/connect/connect';
import ContentWrapper from '../components/ContentWrapper';
import PageTitle from '../components/PageTitle';
import PropTypes from 'prop-types';
import rest from '../redux/utils/rest';
import { push } from 'react-router-redux';

export class InvoiceEntry extends Component {
    componentDidMount () {
        const { dispatch } = this.props;
        dispatch(rest.actions.getInvoiceEntry({ id: `${this.props.match.params.invoiceEntryId}` }));
        dispatch(rest.actions.getInvoice({ id: `${this.props.match.params.invoiceId}` }));
    }

  handleEditSubmit = (event) => {
      event.preventDefault();
      const { dispatch } = this.props;
      const id = this.props.match.params.invoiceEntryId;
      const name = $('#invoice-entry-name').val();
      const invoiceEntryData = {
          id,
          name
      };
      dispatch(rest.actions.updateInvoiceEntry({ id: `${this.props.match.params.invoiceEntryId}` }, {
          body: JSON.stringify(invoiceEntryData)
      }));
  }

  handleDeleteSubmit = (event) => {
      event.preventDefault();
      const { dispatch } = this.props;
      dispatch(rest.actions.deleteInvoiceEntry({ id: `${this.props.match.params.invoiceEntryId}` }));
      // @TODO: Check that deletion is successful before navigating back to invoice page
      this.props.history.push(`/project/${this.props.match.params.projectId}/${this.props.match.params.invoiceId}`);
  }

  render () {
      if (this.props.invoiceEntry.data.invoiceId && this.props.invoiceEntry.data.invoiceId != this.props.match.params.invoiceId) {
          return (
              <ContentWrapper>
                  <PageTitle>Invoice Entry</PageTitle>
                  <div>Error: the requested invoiceEntry does not match the invoice specified in the URL</div>
                  <div>(URL contains invoiceId '{this.props.match.params.invoiceId}'
             but invoiceEntry with id '{this.props.match.params.invoiceEntryId}'
              belongs to invoice with id '{this.props.invoiceEntry.data.invoiceId}')
                  </div>
              </ContentWrapper>
          );
      } else if (this.props.invoice.data.jiraId && this.props.invoice.data.jiraId != this.props.match.params.projectId) {
          return (
              <ContentWrapper>
                  <PageTitle>Invoice Entry</PageTitle>
                  <div>Error: the requested invoiceEntry does not match the project specified in the URL</div>
                  <div>(URL contains projectId '{this.props.match.params.projectId}'
             but invoiceEntry with id '{this.props.match.params.invoiceEntryId}'
              belongs to an invoice that belongs to project with id '{this.props.invoice.data.jiraId}')
                  </div>
              </ContentWrapper>
          );
      } else if (this.props.invoiceEntry.data.name) {
          return (
              <ContentWrapper>
                  <PageTitle>Invoice Entry</PageTitle>
                  <div>ProjectID: {this.props.match.params.projectId}</div>
                  <div>InvoiceID: {this.props.match.params.invoiceId}</div>
                  <div>InvoiceEntryID: {this.props.match.params.invoiceEntryId}</div>
                  <div>InvoiceEntryName: {this.props.invoiceEntry.data.name}</div>
                  <div>
                      <form id="submitForm" onSubmit={this.handleEditSubmit}>
                          <div id="formGroup" className="form-group">
                              <label htmlFor="input-invoiceEntry-name">Enter invoice entry name</label>
                              <input
                                  type="text"
                                  name="invoiceEntryName"
                                  className="form-control"
                                  id="invoice-entry-name"
                                  aria-describedby="invoiceEntryName"
                                  placeholder="Enter new invoice entry name">
                              </input>
                          </div>
                          <button type="submit" className="btn btn-primary" id="submit">Submit new invoice entry name</button>
                      </form>
                  </div>
                  <div>
                      <form id="deleteForm" onSubmit={this.handleDeleteSubmit}>
                          <button type="submit" className="btn btn-danger" id="delete">Delete invoice entry</button>
                      </form>
                  </div>
              </ContentWrapper>
          );
      } else {
          return (
              <ContentWrapper>
                  <div className="spinner-border" style={{ width: '3rem', height: '3rem', role: 'status' }}>
                      <span className="sr-only">Loading...</span>
                  </div>
              </ContentWrapper>
          );
      }
  }
}

InvoiceEntry.propTypes = {
    invoiceEntry: PropTypes.object,
    invoice: PropTypes.object
};

const mapStateToProps = state => {
    return {
        invoiceEntry: state.invoiceEntry,
        invoice: state.invoice
    };
};

export default connect(
    mapStateToProps
)(InvoiceEntry);
