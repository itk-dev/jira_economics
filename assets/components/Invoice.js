import React, { Component } from 'react';
import connect from 'react-redux/es/connect/connect';
import ContentWrapper from '../components/ContentWrapper';
import PageTitle from '../components/PageTitle';
import { Link } from 'react-router';

class Invoice extends Component {
  componentDidMount() {
    // @TODO: Implement this.
    // store.dispatch(fetchInvoice(this.props.params.invoiceId));
  }

  render () {
    return (
      <ContentWrapper>
        <PageTitle>Invoice</PageTitle>
        <div>ProjectID: {this.props.params.projectId}</div>
        <div>InvoiceID: {this.props.params.invoiceId}</div>

        <Link to={`/project/${this.props.params.projectId}/${this.props.params.invoiceId}/1`}>InvoiceEntry</Link>
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
)(Invoice);
