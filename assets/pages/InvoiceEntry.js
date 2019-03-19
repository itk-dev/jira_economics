import React, { Component } from 'react';
import connect from 'react-redux/es/connect/connect';
import ContentWrapper from '../components/ContentWrapper';
import PageTitle from '../components/PageTitle';

export class InvoiceEntry extends Component {
  render () {
    return (
      <ContentWrapper>
        <PageTitle>Invoice entry</PageTitle>
        <div>Invoice: {this.props.params.invoiceId}</div>
      </ContentWrapper>
    );
  }
}

const mapStateToProps = state => {
  return {
  };
};

export default connect(
  mapStateToProps
)(InvoiceEntry);
