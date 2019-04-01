import React, { Component } from 'react';
import connect from 'react-redux/es/connect/connect';
import ContentWrapper from '../components/ContentWrapper';
import PageTitle from '../components/PageTitle';
import store from '../redux/store';
import { fetchInvoiceEntry } from '../redux/actions';
import PropTypes from 'prop-types';
import Spinner from '@atlaskit/spinner';

export class InvoiceEntry extends Component {
  componentDidMount() {
    store.dispatch(fetchInvoiceEntry(this.props.params.invoiceEntryId));
  }

  render () {
    if (this.props.selectedInvoiceEntry.name) {
      return (
        <ContentWrapper>
          <PageTitle>Invoice Entry</PageTitle>
          <div>ProjectID: {this.props.params.projectId}</div>
          <div>InvoiceID: {this.props.params.invoiceId}</div>
          <div>InvoiceEntryID: {this.props.params.invoiceEntryId}</div>
          <div>InvoiceEntryName: {this.props.selectedInvoiceEntry.name}</div>
        </ContentWrapper>
      );
    }
    else {
      return (<ContentWrapper><Spinner size="large"/></ContentWrapper>);
    }
  }
}

InvoiceEntry.propTypes = {
  selectedInvoiceEntry: PropTypes.object
};

const mapStateToProps = state => {
  return {
    selectedInvoiceEntry: state.selectedInvoiceEntry.selectedInvoiceEntry
  };
};

export default connect(
  mapStateToProps
)(InvoiceEntry);
