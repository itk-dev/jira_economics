import React, { Component } from 'react';
import connect from 'react-redux/es/connect/connect';
import ContentWrapper from '../components/ContentWrapper';
import PageTitle from '../components/PageTitle';
import { Link } from 'react-router';
import store from '../redux/store';
import { fetchInvoice } from '../redux/actions';
import PropTypes from 'prop-types';
import Moment from 'react-moment';
import 'moment-timezone';

class Invoice extends Component {
  componentDidMount() {
    store.dispatch(fetchInvoice(this.props.params.invoiceId));
  }
  // @TODO: Retrieve all invoiceEntries for an entry and display them
  render () {
    return (
      <ContentWrapper>
        <PageTitle>Invoice</PageTitle>
        <div>ProjectID: {this.props.params.projectId}</div>
        <div>InvoiceID: {this.props.params.invoiceId}</div>
        <div>InvoiceName: {this.props.selectedInvoice.name}</div>
        <div>InvoiceRecorded: {this.props.selectedInvoice.recorded}</div>
        <div>InvoiceCreated: <Moment format="YYYY-MM-DD HH:mm">{this.props.createdAt}</Moment></div>
        <Link to={`/project/${this.props.params.projectId}/${this.props.params.invoiceId}/2`}>InvoiceEntry</Link>
      </ContentWrapper>
    );
  }
}

Invoice.propTypes = {
  selectedInvoice: PropTypes.object,
  createdAt: PropTypes.string
};

const mapStateToProps = state => {
  let createdAt = state.selectedInvoice.selectedInvoice.created ? state.selectedInvoice.selectedInvoice.created.date : "";

  return {
    selectedInvoice: state.selectedInvoice.selectedInvoice,
    createdAt: createdAt
  };
};

export default connect(
  mapStateToProps
)(Invoice);
