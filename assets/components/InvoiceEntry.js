import React, { Component } from 'react';
import connect from 'react-redux/es/connect/connect';
import ContentWrapper from '../components/ContentWrapper';
import PageTitle from '../components/PageTitle';

export class InvoiceEntry extends Component {
  componentDidMount() {
    // @TODO: Implement this.
    // store.dispatch(fetchInvoiceEntry(this.props.params.invoiceEntryId));
  }

  render () {
    return (
      <ContentWrapper>
        <PageTitle>Invoice Entry</PageTitle>
        <div>ProjectID: {this.props.params.projectId}</div>
        <div>InvoiceID: {this.props.params.invoiceId}</div>
        <div>InvoiceEntryID: {this.props.params.invoiceEntryId}</div>
      </ContentWrapper>
    );
  }
}

const mapStateToProps = state => {
  // @TODO: Hook up with state.

  return {
  };
};

export default connect(
  mapStateToProps
)(InvoiceEntry);
