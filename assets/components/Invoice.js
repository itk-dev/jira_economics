import React, { Component } from 'react';
import connect from 'react-redux/es/connect/connect';
import ContentWrapper from '../components/ContentWrapper';
import PageTitle from '../components/PageTitle';
import { Link } from 'react-router';
import store from '../redux/store';
import { fetchInvoice } from '../redux/actions';
import PropTypes from 'prop-types';

class Invoice extends Component {
  componentDidMount() {
    store.dispatch(fetchInvoice(this.props.params.invoiceId));
  }

  render () {
    return (
      <ContentWrapper>
        <PageTitle>Invoice</PageTitle>
        <div>ProjectID: {this.props.params.projectId}</div>
        <div>InvoiceID: {this.props.params.invoiceId}</div>
        <div>InvoiceName: {this.props.selectedInvoice.name}</div>

        <Link to={`/project/${this.props.params.projectId}/${this.props.params.invoiceId}/1`}>InvoiceEntry</Link>
      </ContentWrapper>
    );
  }
}

Invoice.propTypes = {
  selectedInvoice: PropTypes.object
};

const mapStateToProps = state => {
  return {
    selectedInvoice: state.selectedInvoice.selectedInvoice
  };
};

export default connect(
  mapStateToProps
)(Invoice);
