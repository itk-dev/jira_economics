import React, { Component } from 'react';
import connect from 'react-redux/es/connect/connect';
import ContentWrapper from '../components/ContentWrapper';
import PageTitle from '../components/PageTitle';
import { Link } from 'react-router';
import store from '../redux/store';
import { fetchInvoice, fetchInvoiceEntries } from '../redux/actions';
import PropTypes from 'prop-types';
import Spinner from '@atlaskit/spinner';
import Moment from 'react-moment';
import 'moment-timezone';

class Invoice extends Component {
  componentDidMount() {
    store.dispatch(fetchInvoice(this.props.params.invoiceId));
    store.dispatch(fetchInvoiceEntries(this.props.params.invoiceId));
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
          {console.log(this.props.invoiceEntries)}
          {this.props.invoiceEntries && this.props.invoiceEntries.map((item, key) =>
            <div><Link to={`/project/${this.props.params.projectId}/${this.props.params.invoiceId}/${item.id}`}>Link til {item.name}</Link></div>
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
